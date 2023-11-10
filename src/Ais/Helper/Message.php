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

    //public $channel = 0; Indikator für nicht dekodierte Nachricht, AIS-Klasse nicht definiert
    public $name = '';                // Name des Schiffs oder der Einrichtung
    public $speedOverGround;
    public $courseOverGround;
    public $longitude ;
    public $latitude;

    public $timestamp;                // Zeitstempel der Nachricht
    public $messageType;              // ID des Nachrichtentyps
    public $mmsi;                     // Maritime Mobile Service Identity (MMSI) des Senders

    public $receivedTimestamp;



//    public $destinaton;

//    public $ETAmonth;
//    public $ETAday;
//    public $ETAhour;
//    public $ETAminute;



    public function __construct($messageType)
    {
        $this->messageType = $messageType;
    }

    public function decode($aisdata168)
    {}
    public function printObject(){}


    public function getEncodeData()
    {
        $res = [];
        $res['name']  = $this->name;
//        $res['speedOverGround'] =  $this->speedOverGround;
//        $res['courseOverGround']  = $this->courseOverGround ;
        $res['longitude']  = $this->longitude;
        $res['latitude']  = $this->latitude ;

        $res['receivedTimestamp']  = $this->receivedTimestamp;
        $res['messageType']  = $this->messageType;
        $res['mmsi']  = $this->mmsi;

//        $res['destinaton']  = $this->destinaton;

//        $res['ETAmonth']  = $this->ETAmonth;
//        $res['ETAday']  = $this->ETAday;
//        $res['ETAhour']  = $this->ETAhour;
//        $res['ETAminute']  = $this->ETAminute;

        return json_encode($res);
    }
}
