<?php

namespace Ais\Request;
use Ais\Message\Helper;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


error_reporting(E_ALL);

/**
 * Class DataFetcher
 *
 * Die Klasse DataFetcher ist verantwortlich für den Schreibprozess, bei dem Daten gesammelt und in Redis geschrieben werden.
 * Sie stellt Methoden zum Herstellen der Verbindung, Lesen von Daten vom Socket, Dekodieren der AIS-Nachrichten und Schreiben
 * der kombinierten Daten in Redis zur Verfügung.
 *
 * @package Ais
 */
class DataFetcher {
    private $ip;
    private $port;

    private $logger;

    private $helper;

    private $redisData;

    private $config;

    /**
     * Konstruktor der Klasse.
     *
     * @param Logger $logger Das Logger-Objekt zur Protokollierung von Ereignissen.
     * @param Helper $helper Das Helper-Objekt für Hilfsfunktionen.
     * @param RedisData $redisData Das RedisData-Objekt zur Kommunikation mit Redis.
     */
    public function __construct(Logger $logger, Helper $helper, RedisData $redisData, Config $config) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->redisData = $redisData;
        $this->config = $config;
    }

    /**
     * Stellt eine Verbindung zum Socket her und prüft ob IP gültig ist.
     *
     * @return resource Die Socket-Verbindung.
     * @throws Exception Wenn ein Fehler bei der Verbindung oder Validierung auftritt.
     */
    public function connect(){

        if (!filter_var($this->ip, FILTER_VALIDATE_IP)) {
            $errorMessage = "Ungültige IP-Adresse: " . $this->ip;
            $this->logger->error($errorMessage);
            throw new Exception($errorMessage);
        } elseif (!filter_var($this->port, FILTER_VALIDATE_INT)) {
            $errorMessage = "Ungültiger Port: " . $this->port;
            $this->logger->error($errorMessage);
            throw new Exception($errorMessage);
        } else{
            $this->logger->info("Baue den Tunnel auf : IP: $this->ip, Port: $this->port");
        }


        $sock = fsockopen($this->ip,$this->port, $errno, $errstr, 5);

        if (!$sock){
            $errorMessage = "fsockopen() failed: error_code: $errno, error_message: $errstr";
            $this->logger->error($errorMessage);
            throw new Exception($errorMessage);
        }else{
            $this->logger->info("Verbindung erfolgreich hergestellt. Lese die Daten ein...");
        }
        return $sock;
    }

    /**
     * Liest Daten vom Socket, dekodiert AIS-Nachrichten und schreibt die kombinierten Daten in Redis.
     *
     * @throws Exception Wenn ein Fehler beim Lesen, Dekodieren oder Schreiben in Redis auftritt.
     */
    public function fetchAndSendToRedis()
    {
        try {

            $sock = $this->connect();
            $data = [];
            $startTime = time();
            $endTime = $startTime + $this->config->get('request_duration_seconds');
            $readTimeout = 180;
            $incompleteMessage = ''; // Unvollständige Nachrichten, die im vorherigen Durchlauf empfangen wurden
            $redisIP = $this->redisData->getIP();
            $redisPort = $this->redisData->getPort();

            stream_set_timeout($sock, $readTimeout);

            $info = stream_get_meta_data($sock);
            if ($info['timed_out']) {
                $loggingText = 'Timeout beim Lesem vom Socket.';
                $this->logger->critical($loggingText);
                throw new Exception($loggingText);
            }

            $combinedData = [];
            while (time() < $endTime) {

                $buffer = fread($sock, 1024);

                if (!$buffer) {
                    if (feof($sock)) {
                        $this->logger->critical('Verbindung geschlossen: Das Ende des Streams wurde erreicht oder IP/Port ist falsch.');
                        throw new \Exception('Verbindung geschlossen: Das Ende des Streams wurde erreicht oder IP/Port ist falsch.');
                    } else {
                        $this->logger->critical('Fehler beim Lesen vom Socket: Der Buffer ist leer.');
                        throw new \Exception('Fehler beim Lesen vom Socket: Der Buffer ist leer.');

                        //exception ist falsch weil falsch aus einer schleuse keine daten ankommen wird ein exception geworfen, obwohl
                        //gibt es bloß momenten keine schiffe, z.b in der gisela gibt es nie schieffe, und ein exception soll nicht
                        //geworfen werden
                    }
                }

                //Falls die Nachricht unvollständig ankommt, weil der Buffer voll ist
                $buffer = $incompleteMessage . $buffer;

                $data = explode("\n", $buffer);

                $incompleteMessage = '';

                // Falls die letzte Zeile nicht leer ist, handelt es sich um eine unvollständige Nachricht
                if (end($data) !== '') {
                    $incompleteMessage = array_pop($data);
                }

                $data = array_filter($data, 'strlen'); // Leere Zeilen aus den Nachrichten entfernen
                $this->logger->debug('Array von empfangenen Daten: ' . json_encode($data));

                //echo "Array von empfangenen Daten: ". PHP_EOL;
                //var_dump($data). PHP_EOL;

                $decodedData = $this->sendDataToDecoder($data);


                // Nachrichten mit nur dem Schiffsnamen und solchen mit nur geografischen Daten kombinieren.
                foreach ($decodedData as $datum) {
                    $mmsi = trim($datum->mmsi);

                    if (isset($combinedData[$mmsi])) {
                        // Falls das Schiff bereits im kombinierten Array vorhanden ist, aktualisiere die Daten
                        if (!empty($datum->name)) {
                            $cleanedName = $this->clearShipsName($datum->name);
                            $combinedData[$mmsi]->name =  trim($cleanedName);
                        }
                        if (!is_null($datum->longitude)) {
                            $combinedData[$mmsi]->longitude = $datum->longitude;
                        }
                        if (!is_null($datum->latitude)) {
                            $combinedData[$mmsi]->latitude = $datum->latitude;
                        }
                    } else {
                        // Falls das Schiff nicht im kombinierten Array vorhanden ist, füge es hinzu
                        $combinedData[$mmsi] = $datum;
                    }
                }
            }
            $this->logger->info("Lesevorgang abgeschlossen");
            $this->redisData->connect();

            $this->logger->debug("Verbinde mit Redis, IP: ". $this->redisData->getIP() .  " , Port: " . $this->redisData->getPort());
            $this->redisData->clear();
            $this->redisData->write($combinedData);
            $this->logger->info("Schreibe Daten in Redis...");
            $this->redisData->close();
            $this->logger->info("Prozess beendet. Status: OK");



        } catch (Exception $e) {
            $this->logger->error('Exception: ' . $e->getMessage(), ['trace' => $e->getTrace()]);
        }
    }

    /**
     * Sendet Daten an den Decoder und gibt die dekodierten Daten zurück.
     *
     * @param array $data Die zu sendenden Daten.
     * @return array Die dekodierten Daten.
     * @throws Exception Wenn ein Fehler beim Senden an den Decoder auftritt.
     */
    function sendDataToDecoder(array $data)
    {
        try {

            $decodedData = $this->helper->decodeMessages($data);

            if (empty($decodedData)) {
                $this->logger->error('Keine dekodierten Daten vorhanden.');
            }

            return $decodedData;
        } catch (Exception $e) {
            $this->logger->error('Fehler beim Senden von Daten an den Decoder: ' . $e->getMessage());
            return [];
        }

    }

    public function setIp($ip) {
        $this->ip = $ip;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Bereinigt den Namen des Schiffs.
     *
     * @param string $name Der zu bereinigende Name.
     * @return string Der bereinigte Name.
     */
    function clearShipsName($name)
    {
        if (strpos($name, "@") !== false && !empty($name)) {
            return str_replace("@", "", $name);
        }
        return $name;
    }

    /**
     * Protokolliert eine CLI-Nachricht mit dem angegebenen Log-Level.
     *
     * @param string $message Die zu protokollierende Nachricht.
     * @param mixed $logLevel Das Log-Level.
     */
    public function logMessageCLI(string $message, $logLevel){

        if(php_sapi_name() !== 'cli'){
            $formattedMessage = sprintf(
                '<p>[%s] %s</p>',
                date('Y-m-d H:i:s'), // Zeitstempel
                $message
            );

            echo $formattedMessage;
        }

        $log = new Logger('cli');
        $streamHandler = new StreamHandler('php://stdout');
        $streamHandler->setLevel($logLevel);
        $log->pushHandler($streamHandler);

        switch ($logLevel){
            case Logger::INFO:
                $log->info($message);
                break;
            case Logger::CRITICAL:
                $log->critical($message);
                break;
            default:
                break;
        }

    }


}

?>
