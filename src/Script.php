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

$ip = null;
$port = null;

function printInputFormat() {
    echo "Bitte geben Sie die Parameter wie folgt ein:\n";
    echo "Für die Befehlszeile (CLI):\n";
    echo "php script.php --ip xxx.xxx.xxx.xx --port xxxxx\n";
    echo "\n";
    echo "Für den Webbrowser:\n";
    echo "http://example.com/script.php?ip=xxx.xxx.xxx.xx&port=xxxxx\n";
    echo "\n";
}

function parseWebArguments() {
    global $ip, $port;

    $ip = $_GET['ip'] ?? null;
    $port = $_GET['port'] ?? null;

}

function parseCommandLineArguments() {
    global $ip, $port;

    $argc = $_SERVER['argc'];

    $argv = $_SERVER['argv'];

    for ($i = 1; $i < $argc; $i++) {
        switch ($argv[$i]) {
            case '--ip':
                if (isset($argv[$i + 1])) {
                    $ip = $argv[++$i];
                } else {
                    echo "Error: --ip requires a value.\n";
                    exit(1);
                }
                break;

            case '--port':
                if (isset($argv[$i + 1])) {
                    $port = $argv[++$i];
                } else {
                    echo "Error: --port requires a value.\n";
                    exit(1);
                }
                break;

            default:
                break;
        }
    }

}

try {

    printInputFormat();

    if (php_sapi_name() == 'cli') {
        // Command-line Ausführung

        parseCommandLineArguments();
    } else {
        // Browser Ausführung
        parseWebArguments();
    }

    if (!$ip || !$port) {
        throw new Exception("Bitte geben Sie IP und Port als Argumente an.");
    }

    $config = new Config('config/config.json');

//   $ip = $config->get('ip');
//   $port = $config->get('port');

    $logger = new Logger("Schleuse_$port");
    $helper = new Helper();
    $redisData = new RedisData($config, $port);
    $logLevel = ($config->get('logLevel_DEBUG')) ?? Logger::INFO; // DEBUG, INFO, NOTICE, WARNING, ERROR, CRITICAL, ALERT, EMERGENCY
    $logger->pushHandler(new StreamHandler('logs/log.txt', $logLevel));
    $dataFetcher = new DataFetcher($logger, $helper, $redisData);
    $dataFetcher->setIp($ip);
    $dataFetcher->setPort($port);
    $dataFetcher->fetchAndSendToRedis();


} catch (Exception $e) {
    echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
}
        // TODO:
        // Logging Console
        // alles aufräumen
        // eine lib aussuchen
        //https://github.com/nategood/commando
        //https://github.com/c9s/GetOptionKit
        //https://github.com/vanilla/garden-cli
        //https://www.php.net/manual/en/function.getopt.php
        //überall passende namen ausdenken
?>

