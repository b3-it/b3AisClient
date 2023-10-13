<?php
namespace Ais;


require 'Decoder.php';

// Die DataFetcher-Klasse ermöglicht die Verbindung zum Server und das Abrufen von AIS-Daten.
class DataFetcher {
    private $ip;
    private $port;

    public function __construct($ip, $port) {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function fetchData() {

        $sock = $this->connect();

        $n = 0;
        $data = [];
        while (($buffer = socket_read($sock, 1024, PHP_NORMAL_READ)) && ($n < 50 )) {
            $n++;
            if ($buffer === false) {
                $socketError = socket_last_error($sock);

                // Überprüfen, ob die Verbindung bewusst beendet wurde.
                if ($socketError === SOCKET_ECONNRESET) {
                    echo "Verbindung zurückgesetzt.\n";
                } else {
                    echo "Fehler beim Lesen vom Socket: " . socket_strerror($socketError) . "\n";
                }

                break;
            }

            if (empty($buffer)) {
                // Wenn $buffer leer ist, bedeutet dies, dass die Verbindung geschlossen wurde.
                echo "Verbindung geschlossen.\n";
                break;
            }

            $buffer = str_replace(["\r","\n"],'',$buffer);
            if (!empty($buffer)) {

                $data[] = $buffer;
            }

            echo "<pre>";
            echo "Empfangene Daten: " . $buffer . "\r\n";

        }
        return $data;
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

    public function decodeAISMessages ($receivedData){

        $decoder = new Decoder();
        $messageBuffer = '';
        $old = [];

        foreach ($receivedData as $line) {
            $line = trim ($line) . "\r\n";

            if (trim($line) === '') {
                continue;
            }

            $messageBuffer .= $line;

            $data = $decoder->process_ais_buf($messageBuffer);
            $messageBuffer = '';
            var_dump($data);

            $mmsi = $data->mmsi;
            if (isset($old[$mmsi])) {
                echo "Found " . $mmsi . '<br>';
            } else {
                $old[$mmsi] = $data;
            }

        }
    }
}

?>
