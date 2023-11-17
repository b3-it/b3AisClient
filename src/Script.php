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

//spl_autoload_register( '_psr4_autoloader' );

try {

    $config = new Config('config/config.json');
    $logger = new Logger("my_logger");
    $helper = new Helper();
    $redisData = new RedisData($config);
    $logLevel = ($config->get('logLevel_DEBUG')) ?? Logger::INFO; // DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY

    $logger->pushHandler(new StreamHandler('logs/log.txt', $logLevel));



    $ip = $config->get('ip');
    $port = $config->get('port');

    $dataFetcher = new DataFetcher($logger, $helper, $redisData);
    $dataFetcher->setIp($ip);
    $dataFetcher->setPort($port);
    $dataFetcher->fetchAndSendToRedis();


} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
}
        //TODO: Encode funktioniert bisschen schief, testen -> weil channel direkt zugewiesen wird


?>

