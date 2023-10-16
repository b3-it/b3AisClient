<?php
/**
 * @License Apache License 2 <http://www.apache.org/licenses/LICENSE-2.0>
 */
namespace Ais;


use Ais\Helper\Message;
use Ais\Helper\Helper;

require('Helper/Helper.php');
require('Helper/Message.php');



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
//    private $httpClient;
//
//    public function __construct() {
//
//        $this->httpClient = new GuzzleHttpClient();
//    }
//
//    public function doSomethingWithHttp() {
//    $response = $this->httpClient->sendHttpRequest('https://example.com/api/data');
//    // Verarbeiten die Antwort.
//    }

    /**
     * Decodiert AIS-Daten und erstellt ein Nachrichtenobjekt.
     *
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     *
     * @return Message - Ein Nachrichtenobjekt mit den decodierten Informationen.
     */
    public function decodeAIS($aisdata168)
    {
        // Initialisieren eines Nachrichtenobjekts
        date_default_timezone_set('Europe/Berlin');
        $message = new Message();
        $message->timestamp = date("Y-m-d H:i:s");
        $message->messageType = bindec(substr($aisdata168, 0, 6));
        $message->mmsi = bindec(substr($aisdata168, 8, 30));

        // Klassifizieren der Nachricht anhand der ID
        if ($message->messageType >= 1 && $message->messageType <= 3) {
            $this->decodeType123Message($message, $aisdata168);
        } elseif ($message->messageType == 5) {
            $this->decodeType5Message($message, $aisdata168);
        } elseif ($message->messageType == 18) {
            $this->decodeType18Message($message, $aisdata168);
        }elseif ($message->messageType == 19){
            $this->decodeType19Message($message, $aisdata168);
        }
        elseif ($message->messageType == 24) {
            $this->decodeType24Message($message, $aisdata168);
        }

        // Ausgabe des decodierten Nachrichtenobjekts für Debugging-Zwecke
        //var_dump($message);
        return $message;
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 1, 2 oder 3.
     *
     * @param Message $message - Das Nachrichtenobjekt, das aktualisiert wird.
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    private function decodeType123Message($message, $aisdata168)
    {
        $message->courseOverGround = bindec(substr($aisdata168, 116, 12)) / 10;
        $message->speedOverGround = bindec(substr($aisdata168, 50, 10)) / 10;
        $message->longitude = $this->convertToLongitude(bindec(substr($aisdata168, 61, 28)));
        $message->latitude = $this->convertToLatitude(bindec(substr($aisdata168, 89, 27)));
        $message->class = 1; // Class A
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 5.
     *
     * @param Message $message - Das Nachrichtenobjekt, das aktualisiert wird.
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    private function decodeType5Message($message, $aisdata168)
    {
        $message->name = $this->convertBinaryToAISChars($aisdata168, 112, 120);
        $message->class = 1; // Class A
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 18.
     *
     * @param Message $message - Das Nachrichtenobjekt, das aktualisiert wird.
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    private function decodeType18Message($message, $aisdata168)
    {
        $message->courseOverGround = bindec(substr($aisdata168, 112, 12)) / 10;
        $message->speedOverGround = bindec(substr($aisdata168, 46, 10)) / 10;
        $message->longitude = $this->convertToLongitude(bindec(substr($aisdata168, 57, 28)));
        $message->latitude = $this->convertToLatitude(bindec(substr($aisdata168, 85, 27)));
        $message->class = 2; // Class B
    }

    /**
     * Decodiert eine AIS-Nachricht vom Typ 19.
     *
     * @param Message $message - Das Nachrichtenobjekt, das aktualisiert wird.
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    private function decodeType19Message($message, $aisdata168)
    {
        $message->courseOverGround = bindec(substr($aisdata168, 112, 12)) / 10;
        $message->speedOverGround = bindec(substr($aisdata168, 46, 10)) / 10;
        $message->longitude = $this->convertToLongitude(bindec(substr($aisdata168, 61, 28)));
        $message->latitude = $this->convertToLatitude(bindec(substr($aisdata168, 89, 27)));
        $message->name = $this->convertBinaryToAISChars($aisdata168,143,120);
        $message->class = 2; // Class B
    }


    /**
     * Decodiert eine AIS-Nachricht vom Typ 24.
     *
     * @param Message $message - Das Nachrichtenobjekt, das aktualisiert wird.
     * @param string $aisdata168 - Die AIS-Rohdaten (168 Bit).
     */
    private function decodeType24Message($message, $aisdata168)
    {
        $partNumber = bindec(substr($aisdata168, 38, 2));
        if ($partNumber == 0) {
            $message->name = $this->convertBinaryToAISChars($aisdata168, 40, 120);
        }
        $message->class = 2; // Class B
    }


}


$decoder = new Decoder();
if (1) {
    $buf = "!AIVDM,1,1,,A,139O`j?0000PwMRNQwi@0@Oh0<1p,0*04\r\n";
    // Important Note:
    // After receiving input from incoming serial or TCP/IP, call the process_ais_buf(...) method and pass in
    // the input from device for further processing.
    $decoder->process_ais_buf($buf);
}


//$decoder->doSomethingWithHttp();


//if (1) {
//    $test2_a = array( "sdfdsf!AIVDM,1,1,,B,18JfEB0P007Lcq00gPAdv?v000Sa,0*21\r\n!AIVDM,1,1,,B,18Jjr@00017Kn",
//        "jh0gNRtaHH00@06,0*37\r\n!AI","VDM,1,1,,B,18JTd60P017Kh<D0g405cOv00L<c,0*",
//        "42\r\n",
//        "!AIVDM,2,1,8,A,55RiwV02>3bLS=HJ220t<D4r0<u84j222222221?=PD?55Pf0BTjCQhD,0*73\r\n",
//        "!AIVDM,2,2,8,A,3lQH888888",
//        "88880,2*6A\r",
//        "\n!AIVDM,2,1,9,A,569w5`02>0V090=V221@DpN0<PV222222222221EC8S@:5O`0B4jCQhD,0*11\r\n!AIVDM,2,2,9,A,3lQH88888888880,2*6B\r\n!AIVDO,1,1,",
//        ",A,D05GdR1MdffpuTf9H0,4*7","E\r\n!AIVDM,1,1,,A,?","8KWpp0kCm2PD00,2*6C\r\n!AIVDM,1,1,,A,?8KWpp1Cf15PD00,2*3B\r\nUIIII"
//    );
//    foreach ($test2_a as $test2_1) {
//        // Important Note:
//        // After receiving input from incoming serial or TCP/IP, call the process_ais_buf(...) method and pass in
//        // the input from device for further processing.
//        $decoder->process_ais_buf($test2_1);
//    }
//}
