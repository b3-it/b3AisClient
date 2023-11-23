<?php

namespace Ais;
//Ersatz für RedisData, wenn die Daten aus mehreren IPs ankommen werden.
//Schreibt die Daten in Redis mit jeweils unterschiedlichen Schlüsseln für die Quellen.

class RedisData3
{
    protected $redis_ip;
    protected $redis_port;

    protected $data_key_prefix = 'ais_data_';
    //key + port von der schleuse

    protected $config;
    protected $_redis = null;

    protected $host_port;

    public function __construct(Config $config, $host_port)
    {
        $this->host_port = $host_port;

        $this->redis_ip = $config->get('redis_ip') ?? '127.0.0.1';
        $this->redis_port = $config->get('redis_port') ?? 6379;
    }

    public function connect()
    {
        try {
            $this->_redis = new \Redis();
            if ($this->_redis == null) {
                $this->_redis->connect($this->redis_ip, $this->redis_port);
            }
        } catch (\Exception $e) {
            throw new \Exception('Fehler beim Verbinden mit Redis: ' . $e->getMessage());
        }
    }

    public function clear()
    {
        try {
            if ($this->_redis != null) {
                $this->_redis->del($this->getDataKey());
            }
        } catch (\Exception $e) {
            throw new \Exception('Fehler beim Löschen von Daten in Redis: '. $e->getMessage());
        }
    }

    public function close()
    {
        try {
            if ($this->_redis != null) {
                $this->_redis->close();
            }

            $this->_redis = null;
        } catch (\Exception $e) {
            throw new \Exception('Fehler beim Schließen der Verbindung zu Redis: '. $e->getMessage());
        }
    }

    public function write($data){
        try {
            if (is_array($data)) {
                foreach ($data as $d) {
                    $this->_redis->rpush($this->getDataKey(), $d->getEncodeData());
                }
            } else {
                $this->_redis->rpush($this->getDataKey(), $data->getEncodeData());
            }
        } catch (\Exception $e) {
            throw new \Exception('Fehler beim Schreiben von Daten in Redis: ' . $e->getMessage());
        }
    }

    public function read($asObjects = false){
        try {
            $aisData = $this->_redis->lrange($this->getDataKey(), 0, -1);

            if ($asObjects) {
                $tmp = [];
                foreach ($aisData as $d) {
                    $o = json_decode($d, false);
                    $tmp[] = $o;
                }
                return $tmp;
            }

            return $aisData;
        } catch (\Exception $e) {
            throw new \Exception('Fehler beim Lesen von Daten aus Redis: ' . $e->getMessage());
        }
    }

    protected function getDataKey(){
        return $this->data_key_prefix . $this->host_port;
    }


}