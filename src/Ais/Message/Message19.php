<?php

namespace Ais\Message;

class Message19 extends Message
{
    // Bis zum Ende des Schiffsnamens (Bit 143-262)
    const MIN_LENGTH = 263;

    public function __construct($messageType,$receivedTimestamp)
    {
        parent::__construct($messageType, $receivedTimestamp);
    }
    /**
     * Decodiert eine AIS-Nachricht vom Typ 19.
     *
     * @param $messageChannel
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    public function decode($aisdata168)
    {
        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        $this->setPosition(bindec(substr($aisdata168, 57, 28)), bindec(substr($aisdata168, 85, 27)));
        $this->name = $this->convertBinaryToShipName($aisdata168,143,120);

        return $this;
    }



}
