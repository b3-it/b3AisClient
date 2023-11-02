<?php

namespace Ais\Helper;

/**
 * Die Klasse Message repräsentiert eine AIS-Nachricht und dient zur Speicherung und Organisation
 * der decodierten Informationen. Sie enthält Attribute, die verschiedene Aspekte der Nachricht
 * darstellen, wie AIS-Klasse, Name, Geschwindigkeit über Grund, Kurs über Grund, Längengrad, Breitengrad,
 * Zeitstempel, ID und MMSI (Maritime Mobile Service Identity).
 *
 * Standardwerte werden für einige Attribute festgelegt, um den Fall von nicht decodierten Nachrichten
 * oder unbekannten Informationen abzudecken. Diese Klasse wird verwendet, um Nachrichtenobjekte
 * zu erstellen und die darin enthaltenen AIS-Daten zu organisieren.
 */
class Message extends Helper
{

    protected $channel = 0;                // Indikator für nicht dekodierte Nachricht, AIS-Klasse nicht definiert
    protected $name = '';                // Name des Schiffs oder der Einrichtung
    protected $speedOverGround = -1.0;   // Standardwert für unbekannte Geschwindigkeit
    protected $courseOverGround = 0.0;   // Standardwert für unbekannten Kurs
    protected $longitude = 0.0;          // Standardwert für Längengrad
    protected $latitude = 0.0;           // Standardwert für Breitengrad

    protected $timestamp;                // Zeitstempel der Nachricht
    protected $messageType;              // ID des Nachrichtentyps
    protected $mmsi;                     // Maritime Mobile Service Identity (MMSI) des Senders

    protected $destinaton;

    protected $ETAmonth;
    protected $ETAday;
    protected $ETAhour;
    protected $ETAminute;

    public function __construct($messageType)
    {
        $this->messageType = $messageType;
    }

    protected function decode($aisdata168)
    {}
    protected function printObject(){}


    public function getEncodeData()
    {
        $res = [];
        $res['name']  = $this->name;
        $res['speedOverGround'] =  $this->speedOverGround;
        $res['courseOverGround']  = $this->courseOverGround ;
        $res['longitude']  = $this->longitude;
        $res['latitude']  = $this->latitude ;

        $res['timestamp']  = $this->timestamp;
        $res['messageType']  = $this->messageType;
        $res['mmsi']  = $this->mmsi;

        $res['destinaton']  = $this->destinaton;

        $res['ETAmonth']  = $this->ETAmonth;
        $res['ETAday']  = $this->ETAday;
        $res['ETAhour']  = $this->ETAhour;
        $res['ETAminute']  = $this->ETAminute;

        return json_encode($res);
    }
}
