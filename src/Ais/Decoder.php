<?php
/**
 * @License Apache License 2 <http://www.apache.org/licenses/LICENSE-2.0>
 */
namespace Ais;


use Ais\Helper\Message;
use Ais\Helper\Helper;
use Ais\Helper\Message123;
use Ais\Helper\Message18;
use Ais\Helper\Message19;
use Ais\Helper\Message24;
use Ais\Helper\Message5;

require_once('Helper/Helper.php');
require_once('Helper/Message.php');
require_once('Helper/Message123.php');
require_once('Helper/Message5.php');
require_once('Helper/Message18.php');
require_once('Helper/Message19.php');
require_once('Helper/Message24.php');


/**
 * Die Klasse Decoder erweitert die Hilfsklasse Helper und ist für die Dekodierung von AIS-Nachrichten
 * und die Verarbeitung von Rohdaten verantwortlich.
 *
 * Sie bietet Methoden zum Dekodieren verschiedener Typen von AIS-Nachrichten und zur Umwandlung von
 * Rohdaten in interpretierbare Nachrichtenobjekte. Diese Klasse nutzt Methoden aus der Hilfsklasse
 * Helper, um Konvertierungen und Berechnungen durchzuführen.
 */
class Decoder extends Helper
{

    /**
     * Decodiert AIS-Daten und erstellt ein Nachrichtenobjekt.
     *
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     *
     * @return Message - Ein Nachrichtenobjekt mit den decodierten Informationen.
     */
    public function decodeAIS($aisdata168, $channel)
    {

        $messageType = bindec(substr($aisdata168, 0, 6));
        $messageChannel = $channel;
        switch ($messageType) {
            case 1:
            case 2:
            case 3:
                $message = new Message123($messageType);
                break;
            case 5:
                $message = new Message5($messageType);
                break;
            case 18:
                $message = new Message18($messageType);
                break;
            case 19:
                $message = new Message19($messageType);
                break;
            case 24:
                $message = new Message24($messageType);
                break;
        }
        $message->decode($aisdata168, $messageChannel);
        $message->printObject();

//        // Klassifizieren der Nachricht anhand der ID
//        if ($message->messageType >= 1 && $message->messageType <= 3) {
//            $this->decodeType123Message($message, $aisdata168);
//        } elseif ($message->messageType == 5) {
//            $this->decodeType5Message($message, $aisdata168);
//        } elseif ($message->messageType == 18) {
//            $this->decodeType18Message($message, $aisdata168);
//        }elseif ($message->messageType == 19){
//            $this->decodeType19Message($message, $aisdata168);
//        }
//        elseif ($message->messageType == 24) {
//            $this->decodeType24Message($message, $aisdata168);
//        }

        // Ausgabe des decodierten Nachrichtenobjekts für Debugging-Zwecke

        return $message;
    }

}


$decoder = new Decoder();
//if (1) {
//    $buf = "!AIVDM,1,1,,A,139O`j?0000PwMRNQwi@0@Oh0<1p,0*04\r\n";
//
//    // Important Note:
//    // After receiving input from incoming serial or TCP/IP, call the process_ais_buf(...) method and pass in
//    // the input from device for further processing.
//    $decoder->process_ais_buf($buf);
//}



if (1) {
    $test2_a = array(
        "!AIVDM,2,1,8,A,55RiwV02>3bLS=HJ220t<D4r0<u84j222222221?=PD?55Pf0BTjCQhD,0*73\r\n",
        "!AIVDM,2,2,8,A,3lQH88888888880,2*6A\r\n"
    );
    foreach ($test2_a as $test2_1) {
        // Important Note:
        // After receiving input from incoming serial or TCP/IP, call the process_ais_buf(...) method and pass in
        // the input from device for further processing.
        $decoder->process_ais_buf($test2_1);
    }
}
