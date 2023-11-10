<?php

namespace Ais;
use Exception;
use Ais\Helper\Helper;

error_reporting(E_ALL);

// Schreibprozess (Daten sammeln und in Redis schreiben):
class DataFetcher {
    private $ip;
    private $port;

    private $logger;
    private $config;


    public function __construct(Config $config, Logger $logger) {
        $this->config = $config;
        $this->logger = $logger;

        $this->ip = $config->get('ip');
        $this->port = $config->get('port');

    }

    public function connect(){

        $sock = fsockopen($this->ip,$this->port, $errno, $errstr, 5);

        if (!$sock){
            $errorMessage = "fsockopen() failed: error_code: $errno, error_message: $errstr";
            $this->logger->log($errorMessage, 'error');
            throw new Exception($errorMessage);
        }

        return $sock;
    }


    public function fetchAndSendToRedis()
    {
        try {
            $sock = $this->connect();
            $data = [];
            $startTime = time();
            $endTime = $startTime + 5;
            $readTimeout = 300;
            $decodedDataA = [];
            $incompleteMessage = '';

            stream_set_timeout($sock, $readTimeout);

            $info = stream_get_meta_data($sock);
            if ($info['timed_out']) {
                $this->logger->log('Timeout', 'error');
                throw new Exception('Timeout');
            }

            while (time() < $endTime) {

                $buffer = fread($sock, 1024);  //Problem: schneidet die letzen nachtichten ab

                if (!$buffer) {
                    if (feof($sock)) {
                        $this->logger->log('Verbindung geschlossen.', 'error');
                    } else {
                        $this->logger->log('Fehler beim Lesen vom Socket.', 'error');
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
                $this->logger->log('Array von empfangenen Daten: ' . json_encode($data));

                echo "Array von empfangenen Daten: ". PHP_EOL;
                var_dump($data);

                $decodedData = $this->sendDataToDecoder($data);

                if (!empty($decodedData)) {
                    foreach ($decodedData as $datum) {
                        $decodedDataA[$datum->mmsi] = $datum;
                        $this->logger->log("Dekodierte Nachricht: " . json_encode($datum), "");
                        //var_dump($datum);
                    }
                }

            }

            fclose($sock);
            $redis = new RedisData($this->config);
            $redis->connect();
            $redis->clear();
            $redis->write($decodedDataA);
            $test = $redis->read();
            var_dump($test);
            $redis->close();

        } catch (Exception $e) {
            $this->logger->log('Exception: ' . $e->getMessage(), 'error');
        }
    }



    function sendDataToDecoder(array $data)
    {

        try {
            $helper = new Helper();
            $decodedData = $helper->decodeMessages($data);

            if (empty($decodedData)) {
                $this->logger->log('Keine dekodierten Daten vorhanden.', 'info');
            }

            return $decodedData;
        } catch (Exception $e) {
            $this->logger->log('Fehler beim Senden von Daten an den Decoder: ' . $e->getMessage(), 'error');
            return [];
        }

    }

}

?>
