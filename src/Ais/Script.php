<?php
namespace Ais;

require_once 'DataFetcher.php';
require_once 'RedisDataViewer.php';

    // IP-Adresse und Port des Servers
$ip = '172.30.11.225';
$port = 31935;
$client = new DataFetcher($ip, $port);
$redisDataViewer = new RedisDataViewer();
    try {
            // Verbindung zum Server herstellen und Daten abrufen
            $client->fetchAndSendToRedis();

            $redisDataViewer->viewDataFromRedis();

    } catch (Exception $e) {
        echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
    }

?>

