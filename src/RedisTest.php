<?php

/*
 * Prüft die Verbindung zu Redis mit den Einstellungen aus config/config.json (TCP oder Unix-Socket):
 * schreibt einen Testeintrag, liest ihn zurück und löscht ihn wieder.
 */

use Ais\Request\Config;
use Ais\Request\RedisData;

require_once __DIR__ . '/../vendor/autoload.php';

$nl = php_sapi_name() === 'cli' ? PHP_EOL : '<br>';

$config = new Config(__DIR__ . '/config/config.json');
$redisData = new RedisData($config, 'connection_test');

echo "Connecting to " . ($redisData->isUnixSocket()
        ? "socket {$redisData->getIP()}"
        : "{$redisData->getIP()}:{$redisData->getPort()}") . " ..." . $nl;
$redisData->connect();
echo "Connected" . $nl;

$entry = new class {
    public function getEncodeData()
    {
        return json_encode(['written' => date('Y-m-d H:i:s')]);
    }
};
$redisData->replace([$entry]);
echo "Written" . $nl;

$read = implode(', ', $redisData->read());
echo "Read: " . (php_sapi_name() === 'cli' ? $read : htmlspecialchars($read, ENT_QUOTES)) . $nl;

$redisData->replace([]);
echo "Cleared" . $nl;

$redisData->close();
echo "Closed" . $nl;
