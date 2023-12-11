<?php

namespace Ais\Message;

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
    public function decode($aisdata168)
    {
        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        $this->name = $this->convertBinaryToAISChars($aisdata168, 90, 42);
        $this->receivedTimestamp = time();

        return $this;

    }

    function printObject()
    {
        $output =   '<br>'. "MMSI: " .$this->mmsi. '<br>' .
                    "Message type: " .$this->messageType. '<br>'.
                    "Name:  " .$this->name. '<br>' ;

        echo $output;
    }
}