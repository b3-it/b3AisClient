<?php
namespace Ais;

// Die DataFetcher-Klasse ermöglicht die Verbindung zum Server und das Abrufen von AIS-Daten.
class DataFetcher {
    private $ip;
    private $port;

    public function __construct($ip, $port) {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function connectAndFetchData() {
        $data = '';

        if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            throw new Exception("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
        }

        if(! socket_set_option($sock,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>5, "usec"=>0))){
            throw new Exception("socket_set_option() failed: reason: " . socket_strerror(socket_last_error()));
        }

        if (socket_connect($sock, $this->ip, $this->port) === false) {
            throw new Exception("socket_connect() failed: reason: " . socket_strerror(socket_last_error($sock)));
        }

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

//            $data .=$buffer;
            echo "<pre>";
            echo "Empfangene Daten: " . $buffer . "\r\n";

        }
        return $data;
    }
}

?>
