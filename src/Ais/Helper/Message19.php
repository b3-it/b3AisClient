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
    public function decode($aisdata168, $messageChannel)
    {
        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        $this->courseOverGround = bindec(substr($aisdata168, 112, 12)) / 10;
        $this->speedOverGround = bindec(substr($aisdata168, 46, 10)) / 10;
        $this->longitude = $this->convertToLongitude(bindec(substr($aisdata168, 61, 28)));
        $this->latitude = $this->convertToLatitude(bindec(substr($aisdata168, 89, 27)));
        $this->timestamp = bindec(substr($aisdata168, 133, 6));
        $this->name = $this->convertBinaryToAISChars($aisdata168,143,120);
        $this->channel = $messageChannel;
    }

    function printObject()
    {
        $output =   '<br>'. "Object ID: " . spl_object_id($this). '<br>'.
                    "Message type: " .$this->messageType. '<br>'.
                    "MMSI: " .$this->mmsi. '<br>' .
                    "Speed over Ground: " .$this->speedOverGround. '<br>' .
                    "Longitude: " .$this->longitude. '<br>' .
                    "Latitude: " .$this->latitude. '<br>' .
                    "Course over Ground: " .$this->courseOverGround. '<br>' .
                    "Timestamp: ". $this->timestamp . '<br>'.
                    "Name:  " .$this->name. '<br>' .
                    "Channel: " .$this->channel. '<br>';

        echo $output;
    }

}