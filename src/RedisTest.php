<?php


$redis = new \Redis();
echo "Conecting ... <br>";
$redis->connect('127.0.0.1', 6379);
echo "Conected ... <br>";
$redis->del('ais_data_');
echo "Cleared <br>";
$redis->rpush('ais_data_', date('l jS \of F Y h:i:s A'));
echo "Writen <br>";

$aisData = $redis->lrange('ais_data_', 0, -1);
echo "Read: <br>";
var_dump($aisData);
echo '<br>';
$redis->close();
echo "Closed";





