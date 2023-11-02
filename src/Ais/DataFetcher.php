<?php

namespace Ais;
use Exception;
use Ais\Helper\Helper;

error_reporting(E_ALL);

// Schreibprozess (Daten sammeln und in Redis schreiben):
class DataFetcher {
    private $ip;
    private $port;

    public function __construct($ip, $port) {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function connect(){

        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($sock === false) {
            throw new Exception("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
        }

        if (!socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 5, "usec" => 0))) {
            throw new Exception("socket_set_option() failed: reason: " . socket_strerror(socket_last_error()));
        }

        if (socket_connect($sock, $this->ip, $this->port) === false) {
            throw new Exception("socket_connect() failed: reason: " . socket_strerror(socket_last_error($sock)));
        }

//        $errno = "";
//        $errstr = "";
//        $sock = fsockopen($this->ip, $this->port,$errno, $errstr,5);
//        if ($sock === false) {
//            throw new Exception("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
//        }
        return $sock;
    }




    public function fetchAndSendToRedis() {

        $sock = $this->connect();
        $data = [];
        $startTime = time();
        $endTime = $startTime + 5;



        $decodedDataA = [];

        while (time() < $endTime) {

            $buffer = socket_read($sock, 1024, PHP_NORMAL_READ);

            if ($buffer === false) {

                $socketError = socket_last_error($sock);

                // Überprüfen, ob die Verbindung bewusst beendet wurde.
                if ($socketError === SOCKET_ECONNRESET) {
                    echo "Verbindung zurückgesetzt." . '<br>';
                } else {
                    echo "Fehler beim Lesen vom Socket: " . socket_strerror($socketError) . '<br>';
                }

                break;
            }

            if (empty($buffer)) {
                echo "Verbindung geschlossen" . '<br>';
                break;
            } elseif ($buffer === "\n"){
                continue;
            }
            else {
                $data[] = $buffer;
            }

            echo "<pre>";
            echo "Empfangene Daten: " . $buffer . PHP_EOL;

            $decodedData = $this->sendDataToDecoder($data);
            if(!empty($decodedData)) {
                $decodedDataA[] = $decodedData;
            }
            //var_dump($decodedData);
//            echo "test";
            //geht nicht aus der schleife raus!
        }
        $redis = new RedisData();
        $redis->connect();
        $redis->clear();
        $redis->write($decodedDataA);
        $test = $redis->read();
        $redis->close();

        echo "out";
    }


//    public function writeDataToRedis($decodedData)
//    {
//
//        //serialisierung
//        $redis = new \Redis();
//        $redis->connect('127.0.0.1', 6379);
////        echo "Verbindung zum Server erfolgreich hergestellt." . PHP_EOL;
//        $redis->del('ais_data');
//        $redis->rpush('ais_data', serialize($decodedData));
//        $redis->close();
//    }

    function sendDataToDecoder(array $data)
    {
        $decodedData = null;
        $helper = new Helper();

        foreach ($data as $line){

            if (empty($line)) {
                continue;
            }

            $helper->process_ais_buf($line);
            $decodedData = $helper->_resultBuffer;
            //var_dump($decodedData);
        }
        return $decodedData;
    }

}

?>
