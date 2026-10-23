<?php

namespace Ais\Message;

/**
 * Die Klasse Message repräsentiert eine AIS-Nachricht und dient zur Speicherung und Organisation
 * der decodierten Informationen. Sie enthält Attribute, die verschiedene Aspekte der Nachricht
 * darstellen, wie Name, Geschwindigkeit über Grund, Kurs über Grund, Längengrad, Breitengrad,
 * Zeitstempel, ID und MMSI (Maritime Mobile Service Identity).
 */
abstract class Message extends Helper
{
    // Mindestanzahl an Bits, die für die dekodierten Felder benötigt wird
    const MIN_LENGTH = 38;

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
    public $receivedTimestamp;        // Zeitstempel, wann die letzte Nachricht von einem Schiff empfangen wurde


    public function __construct($messageType, $receivedTimestamp)
    {
        $this->messageType = $messageType;
        $this->receivedTimestamp = $receivedTimestamp;
    }

    abstract public function decode($aisdata168);

    /**
     * Setzt die Position aus den Rohwerten. AIS sendet 181° (Längengrad) bzw. 91° (Breitengrad),
     * wenn keine Position verfügbar ist. In diesem Fall oder bei ungültigen Werten bleibt die Position null.
     *
     * @param int $longitudeValue - Der Rohwert des Längengrads (28 Bit).
     * @param int $latitudeValue - Der Rohwert des Breitengrads (27 Bit).
     */
    protected function setPosition($longitudeValue, $latitudeValue)
    {
        $longitude = $this->convertToLongitude($longitudeValue);
        $latitude = $this->convertToLatitude($latitudeValue);

        if (abs($longitude) > 180 || abs($latitude) > 90) {
            $this->longitude = null;
            $this->latitude = null;
            return;
        }

        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }

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
