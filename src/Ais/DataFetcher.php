<?php
namespace Ais;
use Predis\Client;

require 'Decoder.php';

// Schreibprozess (Daten sammeln und in Redis schreiben):
class DataFetcher {
    private $ip;
    private $port;

    private $redisClient;
    /**
     * @var null
     */


    public function __construct($ip, $port) {
        $this->ip = $ip;
        $this->port = $port;

        $this->redisClient = new Client([
           'scheme' => 'tcp',
           'host' => 'localhost', // Hostname oder IP-Adresse Ihres Redis-Servers
           'post' => '6379', // Port des Redis-Servers
        ]);
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

        return $sock;
    }

    public function fetchAndSendToRedis() {

        $sock = $this->connect();
        $data = [];
        $starttime = time();
        $endTime = $starttime + 2;

        while ($endTime > time()) {

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
//            $data = [];

        }
        $this->writeDataToRedis($decodedData);
        return $data;
    }


    public function writeDataToRedis($decodedData)
    {
        //zuerst das cache leeren fehlt noch
        $this->redisClient->rpush('ais_data', $decodedData);
    }

    function sendDataToDecoder(array $data)
    {

        $decoder = new Decoder();
        foreach ($data as $line){

            if (empty($line)) {
                continue;
            }

           $decodedData = $decoder->process_ais_buf(($line));
        }
        return $decodedData;


    }




}

?>
