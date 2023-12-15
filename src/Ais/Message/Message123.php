<?php

namespace Ais\Message;


class Message123 extends Message
{

    public function __construct($messageType,$receivedTimestamp)
    {
        parent::__construct($messageType, $receivedTimestamp);
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 1, 2 oder 3.
     *
     * @param $messageChannel
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    public function decode($aisdata168)
    {

        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        $this->longitude = $this->convertToLongitude(bindec(substr($aisdata168, 61, 28)));
        $this->latitude = $this->convertToLatitude(bindec(substr($aisdata168, 89, 27)));

        return $this;
    }


}
