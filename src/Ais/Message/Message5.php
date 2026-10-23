<?php

namespace Ais\Message;

class Message5 extends Message
{
    // Bis zum Ende des Schiffsnamens (Bit 112-231)
    const MIN_LENGTH = 232;
    public function __construct($messageType,$receivedTimestamp)
    {
        parent::__construct($messageType, $receivedTimestamp);
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 5.
     *
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    public function decode($aisdata168)
    {
        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        $this->name = $this->convertBinaryToShipName($aisdata168, 112, 120);
        return $this;
    }






}
