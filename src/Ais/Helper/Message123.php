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
     * @param $messageChannel
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    public function decode($aisdata168)
    {

        $this->mmsi = bindec(substr($aisdata168, 8, 30));
//        $this->courseOverGround = bindec(substr($aisdata168, 116, 12)) / 10;
//        $this->speedOverGround = bindec(substr($aisdata168, 50, 10)) / 10;
        $this->longitude = $this->convertToLongitude(bindec(substr($aisdata168, 61, 28)));
        $this->latitude = $this->convertToLatitude(bindec(substr($aisdata168, 89, 27)));
        $this->receivedTimestamp = time();


        return $this;
    }

    function printObject()
    {
        $output =   '<br>'. "MMSI: " .$this->mmsi. '<br>' .
            "Message type: " .$this->messageType. '<br>'.
            "Speed over Ground: " .$this->speedOverGround. '<br>' .
            "Longitude: " .$this->longitude. '<br>' .
            "Latitude: " .$this->latitude. '<br>' .
            "Course over Ground: " .$this->courseOverGround. '<br>' .
            "Timestamp:  " .$this->timestamp. '<br>';

        echo $output;
    }

}
