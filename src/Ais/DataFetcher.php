<?php

namespace Ais;
use Ais\Helper\Helper;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


error_reporting(E_ALL);

// Schreibprozess (Daten sammeln und in Redis schreiben):
class DataFetcher {
    private $ip;
    private $port;

    private $logger;

    private $helper;

    private $redisData;


    public function __construct(Logger $logger, Helper $helper, RedisData $redisData) {

        $this->logger = $logger;
        $this->helper = $helper;
        $this->redisData = $redisData;
    }

    public function connect(){

        $allowedList = [
            '172.30.11.225' => [31935, 31936,31937],
            '172.30.21.225' => [31935, 31936,31937],
            '172.30.31.225' => [31935, 31936,31937],
        ];

        if(!$this->validateIpAndPort($this->ip, $this->port, $allowedList)){
            $this->logMessageCLI("Ungültige IP und Port-Kombination", Logger::CRITICAL);
            throw new Exception("Ungültige IP und Port-Kombination");
        }else{
            $this->logMessageCLI("Baue den Tunnel auf : IP: $this->ip, Port: $this->port", Logger::INFO);
        }
        //$this->validateIpAndPort($this->ip, $this->port, $allowedList);

        if (!filter_var($this->ip, FILTER_VALIDATE_IP)) {
            $errorMessage = "Ungültige IP-Adresse: " . $this->ip;
            $this->logger->error($errorMessage);
            throw new Exception($errorMessage);
        }

        $sock = fsockopen($this->ip,$this->port, $errno, $errstr, 5);

        if (!$sock){
            $errorMessage = "fsockopen() failed: error_code: $errno, error_message: $errstr";
            $this->logger->error($errorMessage);
            throw new Exception($errorMessage);
        }else{
            $this->logMessageCLI("Verbindung erfolgreich hergestellt. Lese die Daten ein...", Logger::INFO);
        }
        return $sock;
    }


    public function fetchAndSendToRedis()
    {
        try {

            $sock = $this->connect();
            $data = [];
            $startTime = time();
            $endTime = $startTime + 10;
            $readTimeout = 300;
            $incompleteMessage = '';

            stream_set_timeout($sock, $readTimeout);

            $info = stream_get_meta_data($sock);
            if ($info['timed_out']) {
                $this->logger->critical('Timeout');
                throw new Exception('Timeout');
            }

            $combinedData = [];
            while (time() < $endTime) {

                $buffer = fread($sock, 1024);

                if (!$buffer) {
                    if (feof($sock)) {
                        $this->logger->critical('Verbindung geschlossen.');
                    } else {
                        $this->logger->critical('Fehler beim Lesen vom Socket.');
                    }
                    break;
                }

                //Falls die Nachricht unvollständig ankommt, weil der Buffer voll ist
                $buffer = $incompleteMessage . $buffer;

                $data = explode("\n", $buffer);
                $incompleteMessage = '';

                if (end($data) !== '') {
                    $incompleteMessage = array_pop($data);
                }

                $data = array_filter($data, 'strlen'); // Leere Zeilen aus den Nachrichten entfernen
                $this->logger->debug('Array von empfangenen Daten: ' . json_encode($data));

                //echo "Array von empfangenen Daten: ". PHP_EOL;
                //var_dump($data). PHP_EOL;

                $decodedData = $this->sendDataToDecoder($data);


                foreach ($decodedData as $datum) {
                    $mmsi = trim($datum->mmsi);

                    if (isset($combinedData[$mmsi])) {
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
                        $combinedData[$mmsi] = $datum;
                    }
                }
            }
            $this->logMessageCLI("Lesevorgang abgeschlossen, schreibe Daten in Redis...", Logger::INFO);
            $this->redisData->connect();
            $this->redisData->clear();
            $this->redisData->write($combinedData);
            $this->redisData->close();
            $this->logMessageCLI("OK!", Logger::INFO);

        } catch (Exception $e) {
            $this->logger->error('Exception: ' . $e->getMessage(), ['trace' => $e->getTrace()]);
        }
    }

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

    function clearShipsName($name)
    {
        if (strpos($name, "@") !== false && !empty($name)) {
            return str_replace("@", "", $name);
        }
        return $name;
    }

    public function logMessageCLI(string $message, $logLevel){
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

    public function validateIpAndPort($ip, $port, $allowedList)
    {
        // Überprüfen, ob die Kombination von IP und Port in der Liste enthalten ist
        if (!isset($allowedList[$ip]) || !in_array($port, $allowedList[$ip])) {
            return false;
        }
        return true;
    }

}

?>
