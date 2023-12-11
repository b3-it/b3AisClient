<?php

namespace Ais;

use Ais\Message\Helper;
use Ais\Request\Config;
use Ais\Request\DataFetcher;
use Ais\Request\RedisData;
use Ais\Request\requestHandler;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once  __DIR__ . '/../vendor/autoload.php';
require_once 'Ais/Request/DataFetcher.php';
require_once 'Ais/Request/Config.php';
require_once 'Ais/Request/requestHandler.php';



spl_autoload_register( function ($class) {

    // replace namespace separators with directory separators in the relative
    // class name, append with .php
    $class_path = str_replace('\\', '/', $class);

    $file =  __DIR__ . "/src/" .$class_path . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

try {
    // Request-Handler erstellen und Argumente (IP, Port) einselen
    $requestHandler = new requestHandler();
    $requestHandler->parseArguments();

    $ip = $requestHandler->getIP();
    $port = $requestHandler->getPort();

    $configFilePath = 'config/config-sample.json';

    if (!file_exists($configFilePath)) {
        die("Die Konfigurationsdatei '$configFilePath' existiert nicht.");
    }

    $config = new Config($configFilePath);

    $logger = new Logger("Schleuse_$port");
    $helper = new Helper();
    $redisData = new RedisData($config, $port);

    //LogLevel einstellen
    $logLevel = ($config->get('logLevel')) ?? Logger::INFO;

    $logger->pushHandler(new StreamHandler($config->get('logFile'), $logLevel));
    $dataFetcher = new DataFetcher($logger, $helper, $redisData, $config);
    $dataFetcher->setIp($ip);
    $dataFetcher->setPort($port);
    $dataFetcher->fetchAndSendToRedis();




} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: \n" . $e->getMessage();
}




