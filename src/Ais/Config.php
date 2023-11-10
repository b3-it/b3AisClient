<?php
namespace Ais;

class Config
{
    private $config;

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



