<?php

namespace Ais;

use Ais\Request\Config;
use Ais\Request\RedisData;

error_reporting(E_ALL);
spl_autoload_register( function ($class) {
    // replace namespace separators with directory separators in the relative
    // class name, append with .php
    $class_path = str_replace('\\', '/', $class);

    $file =  __DIR__ . "/../../" .$class_path . '.php';
//echo $file;
    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});


echo '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Schleuse Brunsbüttel</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">

  <style>
    .map {
      width: 100%;
      height: 800px;
    }
    td {
      padding: 0 0.5em;
      text-align: right;
    }
  </style>

</head>
<body>
<div id="map" class="map">
  <div id="popup"></div>
</div>
<div id="info"></div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  let featureList = [];
</script>';

//  featureList[0] = {'lon' : 9.1455916666667,'lat': 53.89135, 'name':'','mmsi':'211207100'};
//  featureList[1] = {'lon' : 9.1503416666667,'lat': 53.892906666667, 'name':'','mmsi':'244129905'};
//  featureList[2] = {'lon' : 9.1441183333333,'lat': 53.893051666667, 'name':'','mmsi':'209543000'};
//  featureList[3] = {'lon' : 9.1511666666667,'lat': 53.893463333333, 'name':'','mmsi':'244700462'};
$config = new Config(__DIR__ . "/../../config/config.json");

/*
$redis = new RedisData($config, $config->get('port'));
$redis->connect();
$aisData = $redis->read(true);
$redis->close();
*/

function readAndPrintAisData($config, $port, $title)
{
    $redisData = new RedisData($config, $port);
    $redisData->connect();
    $aisData = $redisData->read(true);
    echo "<h1>$title</h1>";
    foreach ($aisData as $data) {
        $lat = $data->latitude;
        $long = $data->longitude;
        $name = htmlspecialchars($data->name, ENT_QUOTES);
        $mmsi = htmlspecialchars($data->mmsi, ENT_QUOTES);
        $timestamp =  date('Y-m-d H:i:s', $data->receivedTimestamp);
        echo "lon: $long lat: $lat name: $name mmsi: $mmsi receivedTimestamp: $timestamp<br>" . PHP_EOL;
    }
    $redisData->close();
    return $aisData;
}
$redisPorts = [31935, 31936, 31937];
$aisDataCombined = [];

foreach ($redisPorts as $port) {
    $title = '';
    switch ($port) {
        case 31935:
            $title = 'Brunsbuttel';
            break;
        case 31936:
            $title = 'Kiel';
            break;
        case 31937:
            $title = 'Gieselau';
            break;
    }

    $aisData = readAndPrintAisData($config, $port, $title);
    $aisDataCombined = array_merge($aisDataCombined, $aisData);
}

if (empty($aisDataCombined)) {
    echo "Keine Daten in Redis gefunden." . PHP_EOL;
} else {
    echo '<script type="module">';
    $i = 0;
    foreach ($aisDataCombined as $data) {
        // Schiffe ohne Position (nur Name empfangen) können nicht auf der Karte dargestellt werden
        if (!is_numeric($data->longitude) || !is_numeric($data->latitude)) {
            continue;
        }

        $feature = [
            'lon' => (float)$data->longitude,
            'lat' => (float)$data->latitude,
            'name' => (string)$data->name,
            'mmsi' => (string)$data->mmsi,
            'receivedTimestamp' => (string)$data->receivedTimestamp,
        ];
        // JSON_HEX_* verhindert, dass Zeichen aus dem Schiffsnamen das Script-Tag oder den String verlassen
        $json = json_encode($feature, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        echo "featureList[$i] = $json;" . PHP_EOL;
        $i++;
    }
    echo '</script>';
}



echo '  

<script type="module"  src="./assets/index-91cdf84d.js"></script>
<link rel="stylesheet" href="./assets/index-ff0860cc.css">

</body>
</html>
';