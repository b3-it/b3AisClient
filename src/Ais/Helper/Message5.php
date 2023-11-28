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
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    public function decode($aisdata168)
    {
        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        $this->name = $this->convertBinaryToAISChars($aisdata168, 112, 120);
        $this->receivedTimestamp = time();

        return $this;
    }


    function printObject()
    {
        $output =   '<br>'. "MMSI: " .$this->mmsi. '<br>' .
            "Message type: " .$this->messageType. '<br>' .
            "Name: " .$this->name. '<br>' .
            "ETA month (UTC): ". $this->ETAmonth . '<br>' .
            "ETA day (UTC): ". $this->ETAday . '<br>' .
            "ETA hour (UTC): ". $this->ETAhour . '<br>' .
            "ETA minute (UTC): ". $this->ETAminute . '<br>' .
            "Destination: " .$this->destinaton. '<br>' ;

        echo $output;
    }



}
