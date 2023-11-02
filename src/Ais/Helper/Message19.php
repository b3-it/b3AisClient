<?php

namespace Ais\Helper;

class Message19 extends Message
{

    public function __construct($messageType)
    {
        parent::__construct($messageType);
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
        $this->courseOverGround = bindec(substr($aisdata168, 112, 12)) / 10;
        $this->speedOverGround = bindec(substr($aisdata168, 46, 10)) / 10;
        $this->longitude = $this->convertToLongitude(bindec(substr($aisdata168, 57, 28)));
        $this->latitude = $this->convertToLatitude(bindec(substr($aisdata168, 85, 27)));
        $this->timestamp = bindec(substr($aisdata168, 133, 6));
        $this->name = $this->convertBinaryToAISChars($aisdata168,143,120);
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
            "Timestamp: ". $this->timestamp . '<br>'.
            "Name:  " .$this->name. '<br>';

        echo $output;
    }

}
