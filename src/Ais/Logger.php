<?php
namespace Ais;
use Exception;

class Logger
{
    private $logFile;

    public function __construct($logFile)
    {
        $this->logFile = $logFile;
    }

    public function log($message, $type = 'info')
    {
        try {
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[$timestamp][$type] $message" . PHP_EOL;
            error_log($logMessage, 3, $this->logFile);
        } catch (Exception $e) {
            error_log('Fehler beim Loggen: ' . $e->getMessage(), 0);
        }
    }
}



