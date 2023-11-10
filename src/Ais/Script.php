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
require_once 'Logger.php';
require_once 'Config.php';

try {
    $config = new Config('config.json');
    $logger = new Logger('log.txt');


    $ip = $config->get('ip');
    $port = $config->get('port');

    $dataFetcher = new DataFetcher($config, $logger);
    // Verbindung zum Server herstellen und Daten abrufen
    $dataFetcher->fetchAndSendToRedis();


} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
}

?>

