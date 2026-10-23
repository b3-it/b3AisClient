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

    /**
     * Gibt die konfigurierten AIS-Quellen zurück. Der Port wird auch als Postfix des Redis-Keys verwendet.
     *
     * @return array<int, string> Die Quellen als Port => Name.
     * @throws \Exception Wenn ein Port in der Konfiguration ungültig ist.
     */
    public function getSources()
    {
        $sources = [];

        foreach ($this->get('sources') ?? [] as $source) {
            $port = filter_var($source['port'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);

            if ($port === false) {
                throw new \Exception('Ungültiger Port in der Konfiguration: ' . json_encode($source));
            }

            $sources[$port] = (string)($source['name'] ?? $port);
        }

        return $sources;
    }
}



