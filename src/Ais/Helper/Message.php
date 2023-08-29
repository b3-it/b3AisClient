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
class Message
{
    public $class = 0;                // Indikator für nicht dekodierte Nachricht, AIS-Klasse nicht definiert
    public $name = '';                // Name des Schiffs oder der Einrichtung
    public $speedOverGround = -1.0;   // Standardwert für unbekannte Geschwindigkeit
    public $courseOverGround = 0.0;   // Standardwert für unbekannten Kurs
    public $longitude = 0.0;          // Standardwert für Längengrad
    public $latitude = 0.0;           // Standardwert für Breitengrad

    public $timestamp;                // Zeitstempel der Nachricht
    public $id;                       // ID des Nachrichtentyps
    public $mmsi;                     // Maritime Mobile Service Identity (MMSI) des Senders
}
