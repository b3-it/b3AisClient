<?php

namespace Ais;

use Psr\Log\LogLevel;

/**
 * Class RedisData
 *
 * Die Klasse RedisData stellt Methoden zur Verfügung, um Daten mit Redis zu speichern, zu lesen und zu verwalten.
 * Sie dient als Schnittstelle zur Kommunikation zwischen dem AIS-Verarbeitungsskript und dem Redis-Server.
 *
 * @package Ais
 */

class RedisData
{
    protected $redis_ip;
    protected $redis_port;

    protected $data_key_prefix = 'ais_data_';


    protected $_redis = null;

    protected $config;
    protected $host_port;

    public function __construct(Config $config, $port)
    {
        $this->host_port = $port;
        $this->redis_ip = $config->get('redis_ip') ?? '127.0.0.1';
        $this->redis_port = $config->get('redis_port') ?? 6379;
    }


    /**
     * Stellt eine Verbindung zum Redis-Server her.
     *
     * @throws \Exception Wenn ein Fehler bei der Verbindung mit Redis auftritt.
     */

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

    /**
     * Löscht alle Daten, die mit dem aktuellen Daten-Key in Redis verknüpft sind.
     *
     * @throws \Exception Wenn ein Fehler beim Löschen von Daten in Redis auftritt.
     */
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

    /**
     * Schließt die Verbindung zum Redis-Server.
     *
     * @throws \Exception Wenn ein Fehler beim Schließen der Verbindung zu Redis auftritt.
     */
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

    /**
     * Schreibt Daten in Redis, entweder als einzelnes Objekt oder als Array von Objekten.
     *
     * @param mixed $data Die zu schreibenden Daten.
     * @throws \Exception Wenn ein Fehler beim Schreiben von Daten in Redis auftritt.
     */
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

    /**
     * Liest Daten aus Redis, optional als Objekte.
     *
     * @param bool $asObjects Gibt an, ob die Daten als Objekte zurückgegeben werden sollen.
     * @return array|string Die gelesenen Daten aus Redis.
     * @throws \Exception Wenn ein Fehler beim Lesen von Daten aus Redis auftritt.
     */
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

    /**
     * Generiert den Daten-Key, der den Port der Schleuse enthält.
     *
     * @return string Der generierte Daten-Key.
     */
    public function getDataKey(){
        return $this->data_key_prefix . $this->host_port;
    }




}
