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



try {
    $config = new Config("config.json");
    $redis = new RedisData($config);
    $redis->connect();
    $aisData = $redis->read(true);
    $redis->close();

    if (empty($aisData)) {
        echo "Keine Daten in Redis gefunden." . PHP_EOL;
    } else {
        echo "AIS-Daten aus Redis:" . PHP_EOL;
        foreach ($aisData as $data) {
            echo var_dump($data) . PHP_EOL;
        }

        $i = 0;
        foreach ($aisData as $data) {
            $lat = $data->latitude;
            $long = $data->longitude;
            $name = $data->name;
            $mmsi = $data->mmsi;
//            echo "featureList[$i] =  new Feature({geometry:new Point([$long, $lat]),name:'$name', mmsi:'$mmsi'}); <br>";
            echo "featureList[$i] = {'lon' : $long,'lat':  $lat, 'name':'$name','mmsi':'$mmsi'}; <br>";
            $i++;
        }
    }


    } catch (Exception $e) {
        echo "Fehler beim Verbinden und Empfangen von Daten: " . $e->getMessage();
    }






?>

