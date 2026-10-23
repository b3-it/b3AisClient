<?php

namespace Ais\Message;

class Message24 extends Message
{
    // Bis zum Ende der Teilnummer (Bit 38-39), Teil A benötigt für den Namen NAME_END Bits
    const MIN_LENGTH = 40;
    const NAME_END = 160;

    public function __construct($messageType,$receivedTimestamp)
    {
        parent::__construct($messageType, $receivedTimestamp);
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 24.
     * Nur Teil A (Teilnummer 0) enthält den Schiffsnamen, Teil B enthält keinen Namen.
     *
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    public function decode($aisdata168)
    {
        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        $partNumber = bindec(substr($aisdata168, 38, 2));

        if ($partNumber === 0 && strlen($aisdata168) >= self::NAME_END) {
            $this->name = $this->convertBinaryToShipName($aisdata168, 40, 120);
        }

        return $this;

    }


}
