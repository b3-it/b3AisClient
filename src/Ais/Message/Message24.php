<?php

namespace Ais\Message;

class Message24 extends Message
{

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

        if ($partNumber === 0) {
            $this->name = $this->convertBinaryToShipName($aisdata168, 40, 120);
        }

        return $this;

    }


}
