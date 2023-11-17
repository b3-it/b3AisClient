<?php

/**
 * @License Apache License 2 <http://www.apache.org/licenses/LICENSE-2.0>
 */

namespace Ais;

use Ais\Helper\Helper;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Exception;

require_once  __DIR__ . '/../vendor/autoload.php';
require_once 'Ais/DataFetcher.php';
require_once 'Ais/Logger.php';
require_once 'Ais/Config.php';

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
    $config = new Config('config/config.json');
    $helper = new Helper();


    $hosts = $config->get('hosts');

    // Array zum Speichern von Prozess-IDss
    $pids = [];

    foreach ($hosts as $host) {
        // Fork
        $pid = pcntl_fork();

        if ($pid == -1) {
            // Forking failed
            die('Fork failed');
        } elseif ($pid) {
            // Elternprozess
            $pids[] = $pid;
        } else {
            // Kindprozess
            $ip = $host['ip'];
            $port = $host ['port'];

            $logger = new Logger("my_logger");
            $redisData = new RedisData3($config, $ip);
            $logLevel = ($config->get('logLevel_DEBUG')) ?? Logger::INFO;
            $logger->pushHandler(new StreamHandler('logs/log_' . $ip . '_' . $port . '.txt', $logLevel)); // DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY

            $dataFetcher = new DataFetcher3($logger, $helper, $redisData);
            $dataFetcher->setIp($ip);  // IP ind Port für den aktuellen Prozess festlegen
            $dataFetcher->setPort($port);
            $dataFetcher->fetchAndSendToRedis();

            exit();
        }
    }

    // Warten bis alle Kindprozesse beendet haben
    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
    }

} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
}
?>
