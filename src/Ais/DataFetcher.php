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

        $sock = fsockopen($this->ip,$this->port, $errno, $errstr, 5);

        if (!$sock){
            $errorMessage = "fsockopen() failed: error_code: $errno, error_message: $errstr";
            $this->logger->error($errorMessage);
        }
        return $sock;
    }


    public function fetchAndSendToRedis()
    {
        try {
            $sock = $this->connect();
            $data = [];
            $startTime = time();
            $endTime = $startTime + 50;
            $readTimeout = 300;
            $incompleteMessage = '';
            //$combinedData = [];

            stream_set_timeout($sock, $readTimeout);

            $info = stream_get_meta_data($sock);
            if ($info['timed_out']) {
                $this->logger->critical('Timeout');
                throw new Exception('Timeout');
            }

            $combinedData = [];
            while (time() < $endTime) {

                $buffer = fread($sock, 1024);  //Problem: schneidet die letzen nachtichten ab

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


                echo "Array von empfangenen Daten: ". PHP_EOL;
                var_dump($data). PHP_EOL;

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

//                if (!empty($decodedData)) {
//                    foreach ($decodedData as $datum) {
//                        $decodedDataA[$datum->mmsi] = $datum;
//                        $this->logger->debug("Dekodierte Nachricht: " . json_encode($datum));
//                        //var_dump($datum);
//                    }
               // var_dump($combinedData);
            }

            //$redis = new RedisData($this->config, $this->logger);
            $this->redisData->connect();
            $this->redisData->clear();
            $this->redisData->write($combinedData);
            $test = $this->redisData->read();
            var_dump($test);
            $this->redisData->close();

        } catch (Exception $e) {
            $this->logger->error('Exception: ' . $e->getMessage(), ['trace' => $e->getTrace()]);
        }
        finally {
            fclose($sock);
        }
    }



    function sendDataToDecoder(array $data)
    {

        try {
            //$helper = new Helper();
//            $channels = [];
//            foreach ($data as $datum){
//                $dataParts = explode(",", $datum);
//                $channels = $dataParts[3];
//            }

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

}

?>
