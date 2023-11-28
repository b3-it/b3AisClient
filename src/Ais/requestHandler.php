<?php

namespace Ais;

class requestHandler
{
    private $ip;
    private $port;

    public function __construct()
    {
        $this->ip = null;
        $this->port = null;
    }

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

        if (empty($ip) || empty($port)) {
            echo $this->printInputFormat();
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

