<?php

namespace Ais\Helper;

class Message24 extends Message
{

    public function __construct($messageType)
    {
        parent::__construct($messageType);
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 24.
     *
     * @param $messageChannel
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    public function decode($aisdata168, $messageChannel)
    {
        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        //        $partNumber = bindec(substr($aisdata168, 38, 2));
        $this->name = $this->convertBinaryToAISChars($aisdata168, 40, 120);
        $this->shipType = bindec(substr($aisdata168,40,8));
        $this->channel = $messageChannel;

        //        if ($partNumber == 0) {
        //            $this->channel = "A"; //Class B
        //        } else {
        //            $this->channel = "B"; // Class B
        //        }
    }

    function printObject()
    {
        $output =   '<br>'. "Object ID: " . spl_object_id($this). '<br>'.
                    "Message type: " .$this->messageType. '<br>'.
                    "MMSI: " .$this->mmsi. '<br>' .
                    "Name:  " .$this->name. '<br>' .
                    "Ship type" . $this->shipType . '<br>' .
                    "Channel: " .$this->channel. '<br>' .
                    "Message type: " .$this->messageType. '<br>';

        echo $output;
    }
}