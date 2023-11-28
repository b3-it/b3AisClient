<?php

namespace Ais;

use Ais\Helper\Helper;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


require_once  __DIR__ . '/../vendor/autoload.php';
require_once 'Ais/DataFetcher.php';
require_once 'Ais/Config.php';
require_once 'Ais/requestHandler.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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

    $requestHandler = new requestHandler();
    $requestHandler->parseArguments();

    $ip = $requestHandler->getIP();
    $port = $requestHandler->getPort();

    $config = new Config('config/config.json');

    $logger = new Logger("Schleuse_$port");
    $helper = new Helper();
    $redisData = new RedisData($config, $port);

    //LogLevel einstellen
    $logLevel = ($config->get('logLevel')) ?? Logger::INFO;

    $logger->pushHandler(new StreamHandler('logs/log.txt', $logLevel));
    $dataFetcher = new DataFetcher($logger, $helper, $redisData);
    $dataFetcher->setIp($ip);
    $dataFetcher->setPort($port);
    $dataFetcher->fetchAndSendToRedis();

} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: \n" . $e->getMessage();
}


