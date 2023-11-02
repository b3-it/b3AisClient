<?php
/**
 * @License Apache License 2 <http://www.apache.org/licenses/LICENSE-2.0>
 */

namespace Ais;

use Exception;
error_reporting(E_ALL);
spl_autoload_register( function ($class) {
    // replace namespace separators with directory separators in the relative
    // class name, append with .php
    $class_path = str_replace('\\', '/', $class);

    $file =  __DIR__ . "/../" .$class_path . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

//spl_autoload_register( '_psr4_autoloader' );


require_once 'DataFetcher.php';
require_once 'RedisDataViewer.php';



$ip = '172.30.11.225';
$port = 31935;
$dataFetcher = new DataFetcher($ip, $port);
$redisDataViewer = new RedisDataViewer();
try {

    // Verbindung zum Server herstellen und Daten abrufen
    $dataFetcher->fetchAndSendToRedis();
    $redisDataViewer->viewDataFromRedis();

    } catch (Exception $e) {
        echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
    }






?>

