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
            $this->logger->info("Baue die Verbindung auf : IP: $this->ip, Port: $this->port");
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

    function closeSocket($socket) {
        if (is_resource($socket)) {
            fclose($socket);
            return true;
        } else {
            return false;
        }
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
            $startTime = time();
            $endTime = $startTime + $this->config->get('request_duration_seconds');
            $incompleteMessage = ''; // Unvollständige Nachrichten, die im vorherigen Durchlauf empfangen wurden
            $combinedData = [];

            stream_set_timeout($sock, 10);

            while (time() < $endTime) {

                $buffer = fread($sock, 1024);
                $receivedTimestamp= time();

                if ($buffer === false || $buffer === '') {
                    // Ohne diese Prüfung liefe die Schleife nach dem Schließen der Verbindung ohne Pause weiter
                    if (feof($sock)) {
                        $this->logger->warning('Verbindung wurde von der Gegenstelle geschlossen.');
                        break;
                    }

                    if (stream_get_meta_data($sock)['timed_out']) {
                        $this->logger->debug('Timeout beim Lesen vom Socket, keine Daten empfangen.');
                    }
                    continue;
                }

                $this->logger->debug("Daten gelesen am: " . date('Y-m-d H:i:s', $receivedTimestamp));
                //Falls die Nachricht unvollständig ankommt, weil der Buffer voll ist
                $buffer = $incompleteMessage . $buffer;

                $messages = explode("\n", $buffer);
                // Falls die letzte Zeile nicht leer ist, handelt es sich um eine unvollständige Nachricht
                $incompleteMessage = end($messages) === '' ? '' : array_pop($messages);

                $this->logger->debug('Empfangene Nachrichten im aktuellen Puffer: ' . json_encode($messages));
                $decodedData = $this->sendDataToDecoder(array_filter($messages, 'strlen'), $receivedTimestamp);


                // Nachrichten mit nur dem Schiffsnamen und solchen mit nur geografischen Daten kombinieren.
                foreach ($decodedData as $datum) {
                    $mmsi = trim($datum->mmsi);

                    if (isset($combinedData[$mmsi])) {
                        // Falls das Schiff bereits im kombinierten Array vorhanden ist, aktualisiere die Daten
                        if (!empty($datum->name)) {
                            $combinedData[$mmsi]->name = $datum->name;
                        }
                        // Der Zeitstempel gibt das Alter der Position an und wird daher nur mit ihr aktualisiert
                        if (!is_null($datum->longitude) && !is_null($datum->latitude)) {
                            $combinedData[$mmsi]->longitude = $datum->longitude;
                            $combinedData[$mmsi]->latitude = $datum->latitude;
                            $combinedData[$mmsi]->receivedTimestamp = $datum->receivedTimestamp;
                        }
                    } else {
                        // Falls das Schiff nicht im kombinierten Array vorhanden ist, füge es hinzu
                        $combinedData[$mmsi] = $datum;
                    }
                }


                if (!empty($decodedData)) {
                    $lastMmsi = trim(end($decodedData)->mmsi);
                    $this->logger->info(sprintf(
                        "Letzte Nachricht von: MMSI: %s Name: %s. Empfangen am: %s",
                        $lastMmsi,
                        $combinedData[$lastMmsi]->name ?: 'Unbekannt',
                        date('Y-m-d H:i:s', $receivedTimestamp)
                    ));
                }

            }
            $this->closeSocket($sock);
            $this->logger->notice("Lesevorgang abgeschlossen. Es wurden Daten von " . count($combinedData) . " Schiffen gelesen.");
            $this->storeDataInRedis($combinedData);



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
    function sendDataToDecoder(array $data, $receivedTimestamp)
    {
        try {

            $decodedData = $this->helper->decodeMessages($data,$receivedTimestamp);

            if (empty($decodedData)) {
                $this->logger->error('Keine dekodierten Daten vorhanden.');
            }

            return $decodedData;
        } catch (Exception $e) {
            $this->logger->error('Fehler beim Senden von Daten an den Decoder: ' . $e->getMessage());
            return [];
        }

    }

    /**
     * Speichert die kombinierten Daten in Redis. Ohne empfangene Schiffe bleiben die bisherigen Daten erhalten.
     *
     * @param array $combinedData Die zu speichernden Daten.
     */
    private function storeDataInRedis(array $combinedData)
    {
        if (empty($combinedData)) {
            $this->logger->warning("Keine Schiffsdaten empfangen, die bisherigen Daten in Redis bleiben erhalten.");
            return;
        }

        try {
            $this->redisData->connect();
            $this->logger->debug($this->redisData->isUnixSocket()
                ? "Verbunden mit Redis, Socket: {$this->redisData->getIP()}"
                : "Verbunden mit Redis, IP: {$this->redisData->getIP()}, Port: {$this->redisData->getPort()}");
            $this->redisData->replace($combinedData);
            $this->logger->info("Daten erfolgreich in Redis geschrieben.");
            $this->redisData->close();
            $this->logger->info("Redis-Verbindung geschlossen.");
        } catch (Exception $e) {
            $this->logger->error('Fehler beim Schreiben der Daten in Redis: ' . $e->getMessage());
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
