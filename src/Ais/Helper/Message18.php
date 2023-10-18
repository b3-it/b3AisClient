<?php

namespace Ais\Helper;

class Message18 extends Message
{

    public function __construct($messageType)
    {
        parent::__construct($messageType);
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 18.
     *
     * @param Message $message - Das Nachrichtenobjekt, das aktualisiert wird.
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
        $this->channel = "B"; // Class B
    }

    function printObject()
    {
        $output =   "Object ID: " . spl_object_id($this). '<br>'.
                    "Message type: " .$this->messageType. '<br>'.
                    "MMSI: " .$this->mmsi. '<br>' .
                    "Speed over Ground: " .$this->speedOverGround. '<br>' .
                    "Longitude : " .$this->longitude. '<br>' .
                    "Latitude: " .$this->latitude. '<br>' .
                    "Course over Ground: " .$this->courseOverGround. '<br>' .
                    "Timestamp: ". $this->timestamp . '<br>'.
                    "Channel: " .$this->channel. '<br>';

        echo $output;
    }

}