<?php

namespace Ais\Helper;

class Message5 extends Message
{
    public function __construct($messageType)
    {
        parent::__construct($messageType);
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 5.
     *
     * @param $messageChannel
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    public function decode($aisdata168, $messageChannel)
    {
        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        $this->name = $this->convertBinaryToAISChars($aisdata168, 112, 120);
        $this->shipType = bindec(substr($aisdata168,232,8));
        $this->ETAmonth = bindec(substr($aisdata168, 274, 4));
        $this->ETAday = bindec(substr($aisdata168, 278, 5));
        $this->ETAhour = bindec(substr($aisdata168, 283, 5));
        $this->ETAminute = bindec(substr($aisdata168, 288, 6));
        $this->destinaton = $this->convertBinaryToAISChars($aisdata168, 302, 120);
        $this->channel = $messageChannel;
        $this->printObject();
    }

    function printObject()
    {
        $output =   '<br>'. "Object ID: " . spl_object_id($this). '<br>'.
                    "Message type: " .$this->messageType. '<br>' .
                    "MMSI: " .$this->mmsi. '<br>' .
                    "Name: " .$this->name. '<br>' .
                    "Ship type: " .$this->shipType . '<br>'.
                    "ETA month (UTC): ". $this->ETAmonth . '<br>' .
                    "ETA day (UTC): ". $this->ETAday . '<br>' .
                    "ETA hour (UTC): ". $this->ETAhour . '<br>' .
                    "ETA minute (UTC): ". $this->ETAminute . '<br>' .
                    "Destination: " .$this->destinaton. '<br>' .
                    "Channel: " .$this->channel. '<br>' ;

        echo $output;
    }

}