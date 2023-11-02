<?php

namespace Ais;



class RedisDataViewer
{
    public function __construct()
    {
    }

    public function viewDataFromRedis()
    {
        $redis = new RedisData();

        $redis->connect();
        $aisData = $redis->read();
        $redis->close();
        if (empty($aisData)) {
            echo "Keine Daten in Redis gefunden." . PHP_EOL;
        } else {
            echo "AIS-Daten aus Redis:" . PHP_EOL;
            foreach ($aisData as $data) {
                echo $data . PHP_EOL;
            }
        }
    }
}
