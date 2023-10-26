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
class Decoder
{

//    /**
//     * Decodiert AIS-Daten und erstellt ein Nachrichtenobjekt.
//     *
//     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
//     * @param string $channel
//     * @return Message - Ein Nachrichtenobjekt mit den decodierten Informationen.
//     */
//    public function decodeAIS($aisdata168, $channel)
//    {
//        $message = [];
//        $messageType = bindec(substr($aisdata168, 0, 6));
//        $messageChannel = $channel;
//        switch ($messageType) {
//            case 1:
//            case 2:
//            case 3:
//                $message = new Message123($messageType);
//                break;
//            case 5:
//                $message = new Message5($messageType);
//                break;
//            case 18:
//                $message = new Message18($messageType);
//                break;
//            case 19:
//                $message = new Message19($messageType);
//                break;
//            case 24:
//                $message = new Message24($messageType);
//                break;
//        }
//        $message->decode($aisdata168, $messageChannel);
//        $message->printObject();
//
//        return $message;
//    }

}


//$decoder = new Decoder();
$helper = new Helper();
//if (1) {
//    $buf = "!AIVDM,1,1,,A,139O`j?0000PwMRNQwi@0@Oh0<1p,0*04\r\n";
//
//    // Important Note:
//    // After receiving input from incoming serial or TCP/IP, call the process_ais_buf(...) method and pass in
//    // the input from device for further processing.
//    $decoder->process_ais_buf($buf);
//}




// Testdaten
if (1) {
    $test2_a = array(
        "!AIVDM,1,1,,A,18UG;P0012G?Uq4EdHa=c;7@051@,0*53\r\n",
        "!AIVDM,2,1,0,B,539S:k40000000c3G04PPh63<00000000080000o1PVG2uGD:00000000000,0*34\r\n",
        "!AIVDM,2,2,0,B,00000000000,2*27\r\n",
        "!AIVDM,2,1,8,A,55RiwV02>3bLS=HJ220t<D4r0<u84j222222221?=PD?55Pf0BTjCQhD,0*73\r\n",
        "!AIVDM,2,2,8,A,3lQH88888888880,2*6A\r\n",
        "!AIVDM,2,1,0,B,C8u:8C@t7@TnGCKfm6Po`e6N`:Va0L2J;06HV50JV?SjBPL3,0*28\r\n",
        "!AIVDM,2,2,0,B,11RP,0*17\r\n",
        "!AIVDM,2,1,0,A,58wt8Ui`g??r21`7S=:22058<v05Htp000000015>8OA;0sk,0*7B\r\n",
        "!AIVDM,2,2,0,A,eQ8823mDm3kP00000000000,2*5D\r\n"
    );
    foreach ($test2_a as $test2_1) {
        // Important Note:
        // After receiving input from incoming serial or TCP/IP, call the process_ais_buf(...) method and pass in
        // the input from device for further processing.

        $helper->process_ais_buf($test2_1);
        $result = $helper->_resultBuffer;
        var_dump($result);
    }
}



