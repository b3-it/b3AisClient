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
    $config = new Config('config/config-sample.json');

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
            echo "Redis Key: $redisKey" . PHP_EOL;
            echo "Daten:" . PHP_EOL;
            foreach ($data as $key => $value) {
                if (is_object($value)) {
                    $value = (array)$value;
                }

                if (is_array($value) || is_object($value)) {
                    echo "$key: " . print_r($value, true) . PHP_EOL;
                } else {
                    // Andernfalls zeige den Wert normal an
                    echo "$key: $value\n";
                }
            }
            echo "-----------------" . PHP_EOL;
        }
    }else{
        $redisData = new RedisData($config, $port);
        $redisData->connect();
        $redisKey = 'ais_data_' . $port;
        $aisData = $redisData->read(true);
        $redisData->close();

        echo "Redis Key: $redisKey" . PHP_EOL;
        echo "Daten:" . PHP_EOL;

        foreach ($aisData as $index => $data) {
            echo "$index: Array" . PHP_EOL;
            echo "(" . PHP_EOL;

            foreach ($data as $key => $value) {
                if (is_object($value)) {
                    $value = (array)$value;
                }

                if (is_array($value) || is_object($value)) {
                    echo "    [$key] => " . print_r($value, true) . PHP_EOL;
                } else {
                    echo "    [$key] => $value" . PHP_EOL;
                }
            }

            echo ")" . PHP_EOL;
            echo PHP_EOL;
        }
    }



} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: \n" . $e->getMessage();
}
