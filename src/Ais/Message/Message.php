<?php

namespace Ais\Message;

/**
 * Die Klasse Message repräsentiert eine AIS-Nachricht und dient zur Speicherung und Organisation
 * der decodierten Informationen. Sie enthält Attribute, die verschiedene Aspekte der Nachricht
 * darstellen, wie Name, Geschwindigkeit über Grund, Kurs über Grund, Längengrad, Breitengrad,
 * Zeitstempel, ID und MMSI (Maritime Mobile Service Identity).
 */
class Message extends Helper
{

    //public $channel = 0; Indikator für nicht dekodierte Nachricht, AIS-Klasse nicht definiert
    public $name = '';

   // Name des Schiffs oder der Einrichtung
    public $speedOverGround;
    public $courseOverGround;
    public $longitude ;
    public $latitude;
    public $timestamp;                // Zeitstempel der Nachricht
    public $messageType;              // ID des Nachrichtentyps
    public $mmsi;                     // Maritime Mobile Service Identity (MMSI) des Senders
    public $receivedTimestamp;


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
        $res['longitude']  = $this->longitude;
        $res['latitude']  = $this->latitude ;

        $res['receivedTimestamp']  = $this->receivedTimestamp;
        $res['mmsi']  = $this->mmsi;


        return json_encode($res);
    }

}
