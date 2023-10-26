<?php

namespace Ais;
use Redis;
class RedisDataViewer
{
    private $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('localhost', 6379);
    }

    public function viewDataFromRedis()
    {
        $aisData = $this->redis->lrange('ais_data', 0, -1);

        foreach ($aisData as $data) {
            echo "AIS Data: " . $data . PHP_EOL;
        }
    }
}