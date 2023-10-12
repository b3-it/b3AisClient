<?php
namespace Ais;

require 'DataFetcher.php';
require 'Decoder.php';
// IP-Adresse und Port des Servers
$ip = '127.0.0.1';
$port = 10000;
$client = new DataFetcher($ip, $port);
$decoder = new Decoder();
try {
    $messageBuffer = '';  // Hier wird der Puffer für die AIS-Nachrichten initialisiert
    $messageCount = 0;   // Zähler für die Anzahl der Nachrichten im Puffer
    //while (true)
    {
        // Verbindung zum Server herstellen und Daten abrufen
        $receivedData = $client->connectAndFetchData();
        // Die empfangenen Daten in Zeilen aufteilen
//        $dataLines = explode("\n", $receivedData);
        foreach ($receivedData as $line) {
            $line = trim($line)."\r\n";

            if (trim($line) === '') {
                continue;
            }

            // Füge die Zeile dem Puffer hinzu
            $messageBuffer .= $line;
            $messageCount++;


            // Wenn im Puffer 20 Nachrichten vorhanden sind, verarbeite und gib sie aus
//            if ($messageCount >= 20)
            {

                // Verarbeite den gesamten Puffer (20 Nachrichten)
                $data = $decoder->process_ais_buf($messageBuffer);

                // Zurücksetzen des Puffers und Zählers
                $messageBuffer = '';
                $messageCount = 0;
                var_dump($data);
            }
        }
    }
} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
}

?>

