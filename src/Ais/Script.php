<?php
// Inkludieren Sie die SocketClient-Klasse
use Ais\Decoder;

require 'DataFetcher.php'; // Stellen Sie sicher, dass der Dateiname korrekt ist
require 'Decoder.php';
// IP-Adresse und Port des Servers
$ip = '127.0.0.1'; // Passen Sie dies an die tatsächlichen Serverdetails an
$port = 10000;    // Passen Sie dies an die tatsächlichen Serverdetails an

// Erstellen Sie eine Instanz der SocketClient-Klasse
$client = new DataFetcher($ip, $port);
$decoder = new Decoder();
try {
    // Verbindung zum Server herstellen und Daten abrufen
    $receivedData = $client->connectAndFetchData();

    // Teilen Sie die empfangenen Daten in Zeilen auf
    $dataLines = explode("\n", $receivedData);

    // Dekodieren und ausgeben jeder Zeile
    foreach ($dataLines as $line) {
        // Überspringen Sie leere Zeilen
        if (trim($line) === '') {
            continue;
        }

        // Dekodieren Sie die Zeile, falls erforderlich
        $decoder->process_ais_buf($line);

        // Ausgabe der dekodierten Zeile auf der Kommandozeile
//       echo "Dekodierte Zeile:\n" . $decodedData . "\n";
    }
} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
}

?>

