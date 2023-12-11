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
$config = new Config(__DIR__ . "/../../config/config-sample.json");

/*
$redis = new RedisData($config, $config->get('port'));
$redis->connect();
$aisData = $redis->read(true);
$redis->close();
*/

$redis31935 = new RedisData($config, 31935);
$redis31935->connect();
$aisDataBrunsbuettel = $redis31935->read(true);

echo "<h1> Brunsbüttel </h1>";
foreach ($aisDataBrunsbuettel as $data) {
    $lat = $data->latitude;
    $long = $data->longitude;
    $name = $data->name;
    $mmsi = $data->mmsi;
    echo 'lon: '. $long . ' lat: '.  $lat.  ' name: '. $name. ' mmsi: '. $mmsi. "<br>". PHP_EOL;
}


$redis31935->close();

$redis31936 = new RedisData($config, 31936);
$redis31936->connect();
$aisDataKiel = $redis31936->read(true);
echo "<h1> Kiel </h1>";
foreach ($aisDataKiel as $data) {
    $lat = $data->latitude;
    $long = $data->longitude;
    $name = $data->name;
    $mmsi = $data->mmsi;
    echo 'lon: '. $long . ' lat: '.  $lat.  ' name: '. $name. ' mmsi: '. $mmsi. "<br>". PHP_EOL;
}
$redis31936->close();

$redis31937 = new RedisData($config, 31937);
$redis31937->connect();
$aisDataGieselau = $redis31937->read(true);
echo "<h1> Giselau </h1>";
foreach ($aisDataGieselau as $data) {
    $lat = $data->latitude;
    $long = $data->longitude;
    $name = $data->name;
    $mmsi = $data->mmsi;
    echo 'lon: '. $long . ' lat: '.  $lat.  ' name: '. $name. ' mmsi: '. $mmsi. "<br>". PHP_EOL;
}
$redis31937->close();

$aisData = array_merge($aisDataBrunsbuettel, $aisDataKiel, $aisDataGieselau);

if (empty($aisData)) {
    echo "Keine Daten in Redis gefunden." . PHP_EOL;
} else {

    echo '<script type="module">';
    $i = 0;
    foreach ($aisData as $data) {
        $lat = $data->latitude;
        $long = $data->longitude;
        $name = $data->name;
        $mmsi = $data->mmsi;
//            echo "featureList[$i] =  new Feature({geometry:new Point([$long, $lat]),name:'$name', mmsi:'$mmsi'}); <br>";
        echo "featureList[$i] = {'lon' : $long,'lat':  $lat, 'name':'$name','mmsi':'$mmsi'};". PHP_EOL;
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
