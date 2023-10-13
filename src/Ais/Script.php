<?php
namespace Ais;

require 'DataFetcher.php';

    // IP-Adresse und Port des Servers
    $ip = '127.0.0.1';
    $port = 10000;
    $client = new DataFetcher($ip, $port);
    try {
            // Verbindung zum Server herstellen und Daten abrufen
            $receivedData = $client->fetchData();
            $client->decodeAISMessages($receivedData);

    } catch (Exception $e) {
        echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
    }

?>

