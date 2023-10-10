<?php
namespace Ais;
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

        if (socket_connect($sock, $this->ip, $this->port) === false) {
            throw new Exception("socket_connect() failed: reason: " . socket_strerror(socket_last_error($sock)));
        }

        while ($buffer = socket_read($sock, 1024, PHP_NORMAL_READ)) {

            if ($buffer === false) {
                $socketError = socket_last_error($sock);

                // Überprüfen, ob die Verbindung bewusst beendet wurde
                if ($socketError === SOCKET_ECONNRESET) {
                    echo "Verbindung zurückgesetzt.\n";
                } else {
                    echo "Fehler beim Lesen vom Socket: " . socket_strerror($socketError) . "\n";
                }

                break;
            }

            if ($buffer === '') {
                // Wenn $buffer leer ist, bedeutet dies, dass die Verbindung geschlossen wurde.
                echo "Verbindung geschlossen.\n";
                break;
            }
            $data .=$buffer;

           echo "Empfangene Daten: " . $buffer . "\n";
        }
        return $data;
    }
}

//// Verwendung der SocketClient-Klasse
//$ip = '127.0.0.1'; // IP-Adresse des Servers
//$port = 10000;  // Port des Servers
//
//$client = new DataFetcher($ip, $port);
//
//try {
//    $receivedData = $client->connectAndFetchData();
//    echo "Empfangene Daten:\n" . $receivedData;
//} catch (Exception $e) {
//    echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
//}


?>