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
     * @param Message $message - Das Nachrichtenobjekt, das aktualisiert wird.
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    public function decode($aisdata168)
    {
        $this->mmsi = bindec(substr($aisdata168, 8, 30));
        $this->name = $this->convertBinaryToAISChars($aisdata168, 112, 120);
        $this->destinaton = $this->convertBinaryToAISChars($aisdata168, 302, 120);
        $this->channel = "A"; // Class A
        $this->printObject();
    }

    function printObject()
    {
        $output =   "Object ID: " . spl_object_id($this). '<br>'.
                    "Message type: " .$this->messageType. '<br>' .
                    "MMSI: " .$this->mmsi. '<br>' .
                    "Name: " .$this->name. '<br>' .
                    "Destination: " .$this->destinaton. '<br>' .
                    "Channel: " .$this->channel. '<br>' ;

        echo $output;
    }

}