<?php

use Ais\Request\Config;
use Ais\Request\RedisData;
use Ais\Request\requestHandler;

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once  __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Ais/Request/Config.php';
require_once __DIR__ . '/Ais/Request/RedisData.php';

try {
    $requestHandler = new RequestHandler();
    $requestHandler->parseArgumentsViewer();
    $config = new Config(__DIR__.'/config/config.json');

    $port = $requestHandler->getPortViewer();
    $redisPorts = [31935, 31936, 31937];
    $aisDataCombined = [];

    function readData($config, $port, &$aisDataCombined, $isCli)
    {
        $redisData = new RedisData($config, $port);
        $redisData->connect();
        $redisKey = $redisData->getDataKey();
        $aisData = $redisData->read(true);
        $redisData->close();
        $aisDataCombined[$redisKey] = $aisData;

        echo $isCli ? "Redis Key: $redisKey" . PHP_EOL . "Daten:" . PHP_EOL : "<p><strong>Redis Key:</strong> $redisKey</p><p><strong>Daten:</strong></p><ul>";

        foreach ($aisData as $index => $arrayData) {
            echo $isCli ? "Schiff " . ($index + 1) . PHP_EOL : "<br><li>Schiff " . ($index + 1) . "<br>";

            foreach ($arrayData as $key => $value) {
                echo $isCli ? "  $key: " . print_r($value, true) . PHP_EOL : "<br>&nbsp;&nbsp;<strong>$key</strong> => " . print_r($value, true);
            }

            echo $isCli ? "-----------------" . PHP_EOL : "</li>";
        }

        echo $isCli ? '' : "</ul>-----------------<br>";
    }

    $isCli = php_sapi_name() === 'cli';

    if (!$port) {
        foreach ($redisPorts as $port) {
            readData($config, $port, $aisDataCombined, $isCli);
        }
    } else {
        readData($config, $port, $aisDataCombined, $isCli);
    }


} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: <br>" . $e->getMessage();
}

?>
