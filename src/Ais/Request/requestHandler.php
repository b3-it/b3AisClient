<?php

namespace Ais\Request;

/**
 * Class requestHandler
 *
 * Diese Klasse behandelt die Verarbeitung von Eingabeparametern, insbesondere IP-Adresse und Port.
 * Sie bietet Methoden zum Analysieren von Parametern aus der Befehlszeile (CLI) oder einer Webanfrage.
 *
 * @package Ais
 */

class requestHandler
{
    private $ip;
    private $port;

    private $portViewer;

    public function __construct()
    {
        $this->ip = null;
        $this->port = null;
    }


    /**
     * Analysiert die Eingabeparameter je nach Kontext (CLI oder Web) und setzt IP und Port entsprechend.
     * Gibt eine Benachrichtigung aus, falls die Eingabe nicht vollständig ist.
     */
    public function parseArguments()
    {
        if (php_sapi_name() == 'cli') {
            $options = getopt('', ['ip:', 'port:']);

            $this->ip = $options['ip'] ?? null;
            $this->port = $options['port'] ?? null;
        } else {
            $this->ip = $_GET['ip'] ?? null;
            $this->port = $_GET['port'] ?? null;
        }

        if (empty($this->ip) || empty($this->port)) {
            echo $this->printInputFormat();
        }
    }

    public function parseArgumentsViewer()
    {
        if (php_sapi_name() == 'cli') {
            $options = getopt('', ['port:']);
            $this->portViewer = $options['port'] ?? null;
        } else {
            $this->portViewer = $_GET['port'] ?? null;
        }
    }



    public function getIP()
    {
        return $this->ip;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getPortViewer()
    {
        return $this->portViewer;
    }

    public function printInputFormat()
    {
        $message = "Bitte geben Sie die Parameter wie folgt ein:\n";
        $message .= "\n";
        if (php_sapi_name() == 'cli') {
            $message .= "Für die Befehlszeile (CLI):\n";
            $message .= "php processor.php --ip xxx.xxx.xxx.xx --port xxxxx\n";
        } else {

            $message .= "<br>Für den Webbrowser:<br>";
            $message .= "http://example.com/processor.php?ip=xxx.xxx.xxx.xx&port=xxxxx\n";
        }
        $message .= "\n";

        return $message;
    }

}

