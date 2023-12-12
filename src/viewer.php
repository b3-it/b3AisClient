<?php

use Ais\Request\Config;
use Ais\Request\RedisData;
use Ais\Request\requestHandler;

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once  __DIR__ . '/../vendor/autoload.php';
require_once 'Ais/Request/Config.php';
require_once 'Ais/Request/RedisData.php';

try {
    $requestHandler = new requestHandler();
    $requestHandler->parseArgumentsViewer();
    $config = new Config('config/config.json');

    $port = $requestHandler->getPortViewer();

    if (!$port) {
        $redisPorts = [31935, 31936, 31937];
        $aisDataCombined = [];

        foreach ($redisPorts as $port) {
            $redisData = new RedisData($config, $port);
            $redisData->connect();
            $redisKey = $redisData->getDataKey();
            $aisData = $redisData->read(true);
            $redisData->close();

            // Speichern der Daten unter dem entsprechenden Redis-Key
            $aisDataCombined[$redisKey] = $aisData;
        }
        foreach ($aisDataCombined as $redisKey => $data) {
            echo "<p><strong>Redis Key:</strong> $redisKey</p>";
            echo "<p><strong>Daten:</strong></p>";
            echo "<ul>";

            foreach ($data as $index => $arrayData) {
                echo "<br>";
                echo "<li>";
                $schiffNummer = $index + 1;
                echo "Schiff $schiffNummer <br>";
                foreach ($arrayData as $key => $value) {
                    echo "<br>&nbsp;&nbsp;<strong>$key</strong> => " . print_r($value, true);
                }
                echo "</li>";
            }
            echo "</ul>";
            echo "-----------------<br>";
        }
    } else {
        $redisData = new RedisData($config, $port);
        $redisData->connect();
        $redisKey = 'ais_data_' . $port;
        $aisData = $redisData->read(true);
        $redisData->close();

        echo "<p><strong>Redis Key:</strong> $redisKey</p>";
        echo "<p><strong>Daten:</strong></p>";
        echo "<ul>";

        foreach ($aisData as $index => $arrayData) {
            echo "<br>";
            echo "<li>";
            $schiffNummer = $index + 1;
            echo "Schiff $schiffNummer <br>";

            foreach ($arrayData as $key => $value) {
                echo "<br>&nbsp;&nbsp;<strong>$key</strong> => " . print_r($value, true);
            }
            echo "</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: <br>" . $e->getMessage();
}
?>
