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
        $data = [];
        $starttime = time();
        $endTime = $starttime + 30;

        while (true) {

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
                // Wenn $buffer leer ist, bedeutet dies, dass die Verbindung geschlossen wurde.
                echo "Verbindung geschlossen" . '<br>';
                break;
            }

            $buffer = str_replace(["\r","\n"],'',$buffer);

            if (!empty($buffer)) {
                $data[] = $buffer;
            }

            echo "<pre>";
            echo "Empfangene Daten: " . $buffer . "\r\n";

            if (time() > $endTime) {
                break;
            }

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
        $uniqueMMSI = [];
        $lockEntryInProgress = false; // Variable, um den Einfahrvorgang in die Schleuse zu überwachen
        $lockEntryStartTime = null; // Zeitpunkt des Einfahrvorgangs
        $lockMMSI = null; // MMSI des Schiffs im Einfahrvorgang

        foreach ($receivedData as $line) {
            $line = trim ($line) . "\r\n";

            if (trim($line) === '') {
                continue;
            }

            $messageBuffer .= $line;
            $decodedData = $decoder->process_ais_buf($messageBuffer);
            $messageBuffer = '';
            var_dump($decodedData);
            $mmsi = $decodedData->mmsi;

            if (isset($uniqueMMSI[$mmsi])) {
                echo "Doppelte MMSI gefunden: " . $mmsi . '<br>';
            } else {
                $uniqueMMSI[$mmsi] = $decodedData;
            }

            if (!$lockEntryInProgress && $decodedData->speedOverGround > 0) {
                // Das Schiff ist in Bewegung, und der Einfahrvorgang hat begonnen
                $lockEntryInProgress = true;
                $lockEntryStartTime = time();
                $lockMMSI = $mmsi;
            }

            if ($lockEntryInProgress && $mmsi === $lockMMSI) {
                // Überwachen, ob das Schiff MMSI alle 10 Sekunden sendet
                $currentTime = time();
                if ($currentTime - $lockEntryStartTime >= 10) {
                    // Das Schiff hat erfolgreich MMSI gesendet
                    echo "Schiff $lockMMSI ist in die Schleuse eingefahren " .'<br>';
                    $lockEntryInProgress = false;
                    $lockEntryStartTime = null;
                    $lockMMSI = null;
                }
            }

        //TODO: zusammenfügen von nachrichten aus mehreren segmenten

        }




    }
}

?>
