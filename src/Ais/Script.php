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
    // Verbindung zum Server herstellen und Daten abrufen
    $receivedData = $client->connectAndFetchData();

    // Die empfangenen Daten in Zeilen aufteilen
    $dataLines = explode("\n", $receivedData);
//    print_r($dataLines);

      //Dekodieren und ausgeben jeder Zeile
    foreach ($dataLines as $line) {
        $line = trim($line)."\r\n";
//         leere Zeilen überspringen
        if (trim($line) === '') {
            continue;
        }
        $decoder->process_ais_buf($line);

        // Ausgabe der dekodierten Zeile auf der Kommandozeile
//       echo "Dekodierte Zeile:\n" . $decodedData . "\n";
    }
} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
}

?>

