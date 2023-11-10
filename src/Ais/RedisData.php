<?php

namespace Ais;



class RedisData
{
    protected $_ip;
    protected $_port;

    protected $data_key = 'ais_data';

    protected $_redis = null;
    public function __construct(Config $config)
    {
        $this->_ip = $config->get('redis_ip') ?? '127.0.0.1';
        $this->_port = $config->get('redis_port') ?? 6379;
    }



    public function connect()
    {
        $this->_redis = new \Redis();
        if($this->_redis == null) {
            $this->_redis->connect($this->_ip, $this->_port);
        }
    }

    public function clear()
    {
        if($this->_redis != null) {

            $this->_redis->del($this->data_key);
        }


    }

    public function close()
    {
        if($this->_redis != null) {
            $this->_redis->close();
        }

        $this->_redis = null;
    }

    public function write($data){
        if(is_array($data)){
            foreach ($data as $d){
                $this->_redis->rpush($this->data_key, $d->getEncodeData());
            }
        }else{
            $this->_redis->rpush($this->data_key, $data->getEncodeData());
        }
    }

    public function read($asObjects = false){
        $aisData = $this->_redis->lrange($this->data_key, 0, -1);

        if($asObjects){
            $tmp = [];
            foreach ($aisData as $d){
                $o = json_decode($d,false);
                $tmp[] = $o;
            }
            return $tmp;
        }


        return $aisData;
    }



}
