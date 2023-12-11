<?php
namespace Ais\Request;


/**
 * Class Config
 *
 * Die Klasse Config stellt Methoden zum Lesen von Konfigurationsdaten aus einer JSON-Datei bereit.
 *
 * @package Ais
 */
class Config
{
    private $config;

    /**
     * Konstruktor der Klasse.
     *
     * @param string $configFile Der Pfad zur Konfigurationsdatei im JSON-Format.
     */
    public function __construct($configFile)
    {
        $configJson = file_get_contents($configFile);
        $this->config = json_decode($configJson, true);
    }

    public function get($key)
    {
        return $this->config[$key] ?? null;
    }
}



