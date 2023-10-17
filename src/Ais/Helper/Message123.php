<?php

namespace Ais\Helper;


class Message123 extends Message
{

    function __construct($messageType)
    {
        parent::__construct($messageType);
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 1, 2 oder 3.
     *
     * @param Message123 $message123 - Das Nachrichtenobjekt, das aktualisiert wird.
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    public function decode($aisdata168)
    {

        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        $this->courseOverGround = bindec(substr($aisdata168, 116, 12)) / 10;
        $this->speedOverGround = bindec(substr($aisdata168, 50, 10)) / 10;
        $this->longitude = $this->convertToLongitude(bindec(substr($aisdata168, 61, 28)));
        $this->latitude = $this->convertToLatitude(bindec(substr($aisdata168, 89, 27)));
        $this->timestamp = bindec(substr($aisdata168, 137, 6));
        $this->channel = "A"; // Class A
    }

}
