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
        $this->ETAmonth = bindec(substr($aisdata168, 274, 4)); // 1-12, 0=N/A (default)
        $this->ETAday = bindec(substr($aisdata168, 278, 5)); // 1-31, 0=N/A (default)
        $this->ETAhour = bindec(substr($aisdata168, 283, 5)); // 0-23, 24=N/A (default)
        $this->ETAminute = bindec(substr($aisdata168, 288, 6)); // 0-59, 60=N/A (default)
        $this->destinaton = $this->convertBinaryToAISChars($aisdata168, 302, 120);

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
