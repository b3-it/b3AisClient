<?php

namespace Ais\Helper;

use Ais\Helper\Message;
use Ais\Helper\Message123;
use Ais\Helper\Message18;
use Ais\Helper\Message19;
use Ais\Helper\Message24;
use Ais\Helper\Message5;

use Exception;
use function PHPUnit\Framework\exactly;


require_once('Message.php');
require_once('Message123.php');
require_once('Message5.php');
require_once('Message18.php');
require_once('Message19.php');
require_once('Message24.php');


//define('ERROR_MISSING_ASTERISK', -1);
//define('ERROR_INVALID_CHECKSUM_LENGTH', -2);
//define('ERROR_INVALID_NUMBER_OF_SEQUENCES', -3);
//define('ERROR_INVALID_SEQUENCE_NUMBER', -4);
//define('ERROR_INVALID_SEQUENCE_ORDER', -5);
//define('ERROR_INVALID_MULTIPART_MESSAGE', -6);


/**
 * Die Klasse Helper enthält eine Sammlung von Hilfsfunktionen, die zur Dekodierung von AIS-Nachrichten
 * und zur Verarbeitung von Rohdaten verwendet werden. Diese Funktionen bieten Unterstützung bei der
 * Umwandlung von Rohdaten in interpretierbare Informationen und bei verschiedenen Berechnungen,
 * die für die Interpretation von AIS-Nachrichten erforderlich sind.
 */
class Helper
{


    /**
     * Konvertiert einen gegebenen Wert in eine Breitengrad-Koordinate.
     *
     * @param int $coordinateValue - Der Wert, der in eine Koordinate umgewandelt werden soll.
     * @return float - Die resultierende Gleitkommazahl (float) der Breitengrad-Koordinate.
     */
    function convertToLatitude($coordinateValue) {
        $coordinateValue = $coordinateValue & 0x07FFFFFF; // Maske zur Entfernung der höherwertigen Bits (nur die unteren 27 Bits bleiben erhalten)

        if ($coordinateValue & 0x04000000) {
            // Kehrt die Bits um (Bildung des Zweierkomplements) und fügt 1 hinzu.
            $coordinateValue = ($coordinateValue ^ 0x07FFFFFF) + 1;
            // Konvertiert den Wert in einen negativen Gleitkommawert für die Südhalbkugel.
            $latitude = (float)($coordinateValue / (60.0 * 10000.0)) * -1.0;
        } else {
            // Konvertiert den Wert in einen positiven Gleitkommawert für die Nordhalbkugel.
            $latitude = (float)($coordinateValue / (60.0 * 10000.0));
        }

        return $latitude;
    }


    /**
     * Konvertiert einen gegebenen Wert in eine Längengrad-Koordinate.
     *
     * @param int $coordinateValue - Der Wert, der in eine Koordinate umgewandelt werden soll.
     * @return float - Die resultierende Gleitkommazahl (float) der Längengrad-Koordinate.
     */
    function convertToLongitude($coordinateValue) {
        $longitude = 0.0; // Initialisierung der Variable für den Längengrad
        $coordinateValue = $coordinateValue & 0x0FFFFFFF; // Maske zur Entfernung der höherwertigen Bits (nur die unteren 28 Bits bleiben erhalten)

        if ($coordinateValue & 0x08000000) {
            // Kehrt die Bits um (Bildung des Zweierkomplements) und fügt 1 hinzu.
            $coordinateValue = ($coordinateValue ^ 0x0FFFFFFF) + 1;
            // Konvertiert den Wert in einen negativen Gleitkommawert für die Westhalbkugel.
            $longitude = (float)($coordinateValue / (60.0 * 10000.0)) * -1.0;
        } else {
            // Konvertiert den Wert in einen positiven Gleitkommawert für die Osthalbkugel.
            $longitude = (float)($coordinateValue / (60.0 * 10000.0));
        }

        return $longitude;
    }


    /**
     * Konvertiert ein ASCII-Zeichen in einen Dezimalwert.
     *
     * @param string $char - Das ASCII-Zeichen, das konvertiert werden soll.
     * @return int - Der resultierende Dezimalwert des ASCII-Zeichens.
     */
    function convertAsciiToDecimal($char) {
        // Konvertiert das ASCII-Zeichen in einen Dezimalwert
        return ord($char);
    }


    /**
     * Konvertiert einen ASCII-Dezimalwert in einen 8-Bit-Dezimalwert gemäß spezifischer Regeln.
     *
     * @param int $ascii - Der ASCII-Dezimalwert, der konvertiert werden soll.
     * @return int - Der resultierende 8-Bit-Dezimalwert nach den spezifischen Regeln.
     */

    function convertAsciiTo8Bit($ascii) {
        //Verarbeite nur im folgenden Bereich: 48-87, 96-119.
        if (($ascii >= 48 && $ascii <= 87) || ($ascii >= 96 && $ascii <= 119)) {
            $ascii += 40;
            if ($ascii > 128) {
                $ascii += 32;
            } else {
                $ascii += 40;
            }
        }
        return $ascii;
    }

//    function convertAsciiTo8Bit($ascii) {
//        //only process in the following range: 48-87, 96-119
//        if ($ascii < 48) { }
//        else {
//            if($ascii>119) { }
//            else {
//                if ($ascii>87 && $ascii<96) ;
//                else {
//                    $ascii=$ascii+40;
//                    if ($ascii>128){$ascii=$ascii+32;}
//                    else{$ascii=$ascii+40;}
//                }
//            }
//        }
//        return ($ascii);
//    }



    /**
     * Konvertiert einen Dezimalwert in einen 6-Bit-Binärwert und gibt die letzten 6 Bits zurück.
     *
     * @param int $dec - Der Dezimalwert, der konvertiert werden soll.
     * @return string - Die letzten 6 Bits des resultierenden Binärwerts.
     */
    function convertDecimalTo6Bit($dec) {
        $bin = decbin($dec); // Konvertiert den Dezimalwert in Binär
        return substr($bin, -6); // Gibt die letzten 6 Bits zurück
    }


    /**
     * Konvertiert eine binäre Zeichenfolge in eine Zeichenkette unter Verwendung des AIS-Zeichensatzes.
     *
     * @param string $binaryString - Die binäre Zeichenfolge, die konvertiert werden soll.
     * @param int $startIndex - Der Startindex in der binären Zeichenfolge.
     * @param int $partSize - Die Größe des zu konvertierenden Teils der binären Zeichenfolge (muss durch 6 teilbar sein).
     * @return string - Die konvertierte Zeichenkette.
     */
    function convertBinaryToAISChars($binaryString, $startIndex, $partSize) {
        $aisChars = array(
            '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
            'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
            'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']',
            '^', '_', ' ', '!', '\"', '#', '$', '%', '&', '\'',
            '(', ')', '*', '+', ',', '-', '.', '/', '0', '1',
            '2', '3', '4', '5', '6', '7', '8', '9', ':', ';',
            '<', '=', '>', '?'
        );

        $convertedString = '';

        // Überprüfen, ob die Größe durch 6 teilbar ist
        if ($partSize % 6 == 0) {
            $numBlocks = $partSize / 6;

            // Iteriere über die binären Blöcke und konvertiere sie in Zeichen
            for ($blockIndex = 0; $blockIndex < $numBlocks; $blockIndex++) {
                $blockOffset = $blockIndex * 6;
                $binaryBlock = substr($binaryString, $startIndex + $blockOffset, 6);
                $charIndex = bindec($binaryBlock);
                $convertedString .= $aisChars[$charIndex];
            }
        }

        return $convertedString;
    }


    /**
     *
     * Dekodiert eine Liste von AIS-Nachrichten und gibt ein Array mit dekodierten
     * Nachrichten zurück
     *
     * @param array $incomingArray Ein Array von AIS-Nachrichten, die dekodiert werden sollen.
     *
     * @return array Ein Array von dekodierten Nachrichten.
     */
    function decodeMessages($incomingArray) {
        $bitsArray = $this->processPayload($incomingArray);

        if (!is_array($bitsArray)) {
            throw new Exception("Fehler beim Verarbeiten der Payload");
        }


        if (empty($bitsArray)) {
            echo "Warnung: Leeres Array nach der Payload-Verarbeitung." . PHP_EOL;
        }

        $decodedMessages = [];
        $i= 0;
        foreach ($bitsArray as $bits){
            $messageType = bindec(substr($bits, 0, 6));
            $message = null;
            $i++;
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
                default:

                    echo "Unerkannte Nachricht vom Typ $messageType: ".$this->createAisMessage($bits).'<br>'.PHP_EOL ;

                    break;
            }

            if(!empty($message)) {
                $decodedMessage = $message->decode($bits);
                $decodedMessages[] = $decodedMessage;
            }
        }

        return $decodedMessages;
    }

    /**
     * Hilfsfunktion: Extrahiert die Nutzdaten aus einer AIS-Nachricht und konvertiert sie in eine Bitfolge
     * zur weiteren Dekodierung.
     *
     * @param array $incomingArray Ein Array von AIS-Nachrichten in ASCII-Format.
     *
     * @return array Ein Array von AIS-Bit-Daten, wobei jede AIS-Nachricht in 6-Bit-Form vorliegt.
     */
    function processPayload($incomingArray) {

        try {
            $payloadArray  = $this->extractPayload($incomingArray);

            if (!is_array($payloadArray)) {
                throw new Exception("Fehler beim Extrahieren der Payload");
            }

            if (empty($payloadArray)) {
                echo "Warnung: Leeres Array nach der Payload-Extraktion." . PHP_EOL;
            }


            $bitsArray = [];

            foreach ($payloadArray as $payload) {
                $aisData168 = ""; // Sechs-Bit-Array von ASCII-Zeichen
                $symbolsArray = str_split($payload); // In ein Array konvertieren

                foreach ($symbolsArray as $symbol) {
                    $decimalValue = $this->convertAsciiToDecimal($symbol); // ASCII zu Dezimal konvertieren

                    if (!$decimalValue) {
                        throw new Exception("Fehler beim Umwandeln von ASCII in Dezimal.");
                    }

                    $eightBitValue = $this->convertAsciiTo8Bit($decimalValue); // Dezimal zu 8-Bit umwandeln

                    if (!$eightBitValue) {
                        throw new Exception("Fehler beim Umwandeln von Dezimal in 8-Bit.");
                    }

                    $sixBitValue = $this->convertDecimalTo6Bit($eightBitValue); // 8-Bit zu 6-Bit umwandeln

                    if (!$sixBitValue) {
                        throw new Exception("Fehler beim Umwandeln von 8-Bit in 6-Bit.");
                    }

                    $aisData168 .=  $sixBitValue; // An das 6-Bit-Array anhängen
                }

                $bitsArray[] = $aisData168;
            }

            return $bitsArray;
        } catch (Exception $e) {
            error_log('Fehler beim Verarbeiten des Payloads: ' . $e->getMessage(), 0);
            return [];
        }

    }



    /**
     * Berechnet und überprüft die AIS-Checksumme für eine AIS-Nachricht.
     *
     * @param string $message Die AIS-Nachricht, für die die Prüfsumme berechnet und überprüft werden soll.
     *
     * @return bool|int Wenn die berechnete Prüfsumme mit der in der AIS-Nachricht angegebenen Prüfsumme übereinstimmt,
     *                 wird `true` zurückgegeben. Andernfalls wird `false` zurückgegeben. Im Fehlerfall wird `-1` zurückgegeben.
     */

    function calculateAISChecksum($message)
    {
        try {
            $calculatedChecksum = 0;

            // Berechnung der Checksumme von '!' bis '*'
            $endPosition = strrpos($message, '*'); // Suche nach *
            if ($endPosition === false) {
                throw new Exception("Fehler: '*' nicht gefunden.");
            }

            $checksumHexString = substr($message, $endPosition + 1);
            if (strlen($checksumHexString) !== 2) {
                throw new Exception("Fehler: Ungültige Checksummenlänge.");
            }

            $decodedChecksum = (int) hexdec($checksumHexString);

            // XOR-Verknüpfung
            for ($i = 1; $i < $endPosition; $i++) {
                $calculatedChecksum ^= ord($message[$i]);
            }

            if ($calculatedChecksum === $decodedChecksum) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log('Fehler beim Überprüfen der NMEA-Checksumme: ' . $e->getMessage(), 0);
            return false;
        }

    }


    /**
     * Extrahiert die Nutzdaten aus den bereinigten AIS-Nachrichten und gibt sie als Array zurück.
     * Kombiniert Segmente, falls eine Nachricht aus mehreren Segmenten besteht.
     *
     * @param array $incomingArray Ein Array von AIS-Nachrichten, aus denen die Nutzdaten extrahiert werden sollen.
     *
     * @return array Ein Array von AIS-Nutzdaten, die aus den AIS-Nachrichten extrahiert wurden.
     * @throws Exception
     */

    public function extractPayload($incomingArray)
    {
        try {
            $cleanedArray = $this->cleanMessage($incomingArray);

            if (!is_array($cleanedArray)) {
                throw new Exception("Fehler beim Bereinigen der Nachricht");
            }

            if (empty($cleanedArray)) {
                echo "Warnung: Leeres Array nach der Nachrichtenreinigung." . PHP_EOL;
            }

            $mergedPayload = [];

            $reihenfolgeArray = [];
            foreach ($cleanedArray as $cleanedLine) {

                if ($this->calculateAISChecksum($cleanedLine)) {

                    $cleanedLineExploded = explode(",", $cleanedLine);

                    if (count($cleanedLineExploded) >= 6) {
                        $numSequences = (int) $cleanedLineExploded[1];
                        $sequenceNumber = (int) $cleanedLineExploded[2];
                        $messageId = ($cleanedLineExploded[3] == '') ? -1 : (int) $cleanedLineExploded[3];
                        $payload = $cleanedLineExploded[5];

                        if ($numSequences == 1) {
                            $mergedPayload[] = $payload;
                        } else {
                            // Wenn es mehrere Segmente gibt, jedem Payload die SeqNum zuweisen und anschließend sortieren
                            if ($sequenceNumber >= 1 && $sequenceNumber <= $numSequences) {
                                $reihenfolgeArray[$sequenceNumber] = $payload;

                                // Wenn alle Segmente gesammelt sind, kombinieren
                                if (count($reihenfolgeArray) === $numSequences) {
                                    ksort($reihenfolgeArray); // Das Array sortieren, falls die Reihenfolge nicht aufeinanderfolgend ist
                                    $mergedPayload[] = implode('', $reihenfolgeArray);
                                    $reihenfolgeArray = [];
                                }
                            }
                        }
                    } else {
                        throw new Exception("Ungültiges Datenformat: $cleanedLine");
                    }
                }
            }

            return $mergedPayload;
        } catch (Exception $e) {
            error_log('Fehler beim Extrahieren der Payload: ' . $e->getMessage(), 0);
            return [];
        }
    }





//        static $numSequences, $sequenceNumber,$previousSequenceNumber; // Variablen für Sequenzen (1-9)
//        static $messageSid = -1, $currentMessageSid; // Variablen für Nachrichten-ID
//        static $ituBuffer; // Puffer für ITU-Nachricht
//        $checksum = 0; // Initialisierung der Prüfsumme
//
//        // Berechnung der Checksumme von '!' bis '*'
//        $endPosition = strrpos($rawdata, '*'); // Suche nach *
//        if ($endPosition === false) return -1; // Fehler bei fehlendem '*'
//
//        $checksumHexString = substr($rawdata, $endPosition + 1); // Extrahieren der Checksumme als Hex-String
//        if (strlen($checksumHexString) != 2) return -1; // Fehler bei ungültiger Checksummenlänge
//
//        $decodedChecksum = (int)hexdec($checksumHexString); // Umwandeln des Hex-Strings in Dezimalwert
//
//        // XOR-Verknüpfung für die NMEA-Checksumme
//        for ($index = 1; $index < $endPosition; $index++) $checksum ^= ord($rawdata[$index]);
//
//        if ($checksum == $decodedChecksum) { // Überprüfung der NMEA-Checksumme
//            $rawDataArray = explode(',', $rawdata); // Aufteilen der Rohdaten
//
//            // Extrahieren der Sequenz- und Nachrichten-IDs
//            $numSequences = (int)$rawDataArray[1];
//            $sequenceNumber = (int)$rawDataArray[2];
//
//            // Extrahieren der Nachrichten-ID, Prüfen auf leere Nachrichten-ID
//            $messageSid = ($rawDataArray[3] == '') ? -1 : (int)$rawDataArray[3];
//
//            if ($numSequences < 1 || $numSequences > 9) {
//                echo "ERROR,INVALID_NUMBER_OF_SEQUENCES ".time()." $rawdata\n";
//                return -1;
//            }
//            else if ($sequenceNumber < 1 || $sequenceNumber > 9) { // invalid sequences number
//                echo "ERROR,INVALID_SEQUENCES_NUMBER ".time()." $rawdata\n";
//                return -1;
//            }
//            else if ($sequenceNumber > $numSequences) {
//                echo "ERROR,INVALID_SEQUENCE_NUMBER_OR_INVALID_NUMBER_OF_SEQUENCES ".time()." $rawdata\n";
//                return -1;
//            }
//            else { // Sequenzierung ist in Ordnung, Behandlung von Einzel- und Mehrteilnachrichten
//                if ($sequenceNumber == 1) {
//                    // Initialisierung für die erste Sequenz
//                    $ituBuffer = "";
//                    $previousSequenceNumber = 0;
//                    $currentMessageSid = $messageSid;
//                }
//
//                if ($numSequences > 1) { // Für Mehrteilnachrichten
//                    // Überprüfen der Nachrichten-ID und Sequenzreihenfolge
//                    if ($currentMessageSid != $messageSid || $messageSid == -1 || ($sequenceNumber - $previousSequenceNumber) != 1) {
//                        // Ungültige Nachrichten-ID, ungültige Anfangsnachrichten-ID oder nicht in Reihenfolge
//                        $messageSid = -1;
//                        $currentMessageSid = -1;
//                        echo "ERROR, INVALID_MULTIPART_MESSAGE " . time() . " $rawdata\n";
//                        return -1;
//                    } else {
//                        $previousSequenceNumber++;
//                    }
//                }
//
//                // Hinzufügen der ITU-Nachricht und Extrahieren der Füllbits
//                $ituBuffer .= $rawDataArray[5];
//
//                // Verarbeiten der Nachricht, abhängig von der Sequenz
//                if ($numSequences == 1 || $numSequences == $previousSequenceNumber) {
//                    return $this->processAisItu($ituBuffer);
//                }
//            }
//        }
//        return -1; // Fehler
//    }


    /**
     * Bereinigt die eingehenden AIS-Nachrichten, entfernt unnötige Zeichen und gibt ein Array der bereinigten Nachrichten zurück.
     *
     * @param array $incomingArray Ein Array von eingehenden AIS-Nachrichten.
     *
     * @return array Ein Array von bereinigten AIS-Nachrichten ohne unnötige Zeichen.
     * @throws Exception
     */

    public function cleanMessage($incomingArray)
    {

        $cleanedMessages = [];

        try {
            foreach ($incomingArray as $line) {
                if (empty($line)) {
                    continue;
                }

                // Prüfen, ob Zeichenfolge rtrim-Fehler verursacht
                $cleanedLine = rtrim($line, "\r");

                if (!$cleanedLine) {
                    throw new Exception('Fehler beim Entfernen des Carriage Return von der Zeichenfolge');
                }

                $cleanedMessages[] = $cleanedLine;
            }
        } catch (Exception $e) {
            echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
            return [];
        }

        return $cleanedMessages;

    }




//        $currentBuffer = $currentBuffer.$incomingBuffer; // Fügt die empfangenen Daten zum aktuellen Puffer hinzu
//        $lastPosition = 0; // Speichert die letzte Position, bis zu der die Daten verarbeitet wurden
//
//        // Durchsuchen des Puffers nach Nachrichtensegmenten mit dem Startmuster "VDM"
//        while (($start = strpos($currentBuffer, "VDM", $lastPosition)) !== FALSE) {
//            // Prüfen, ob das Ende des aktuellen Segments (beendet mit "\r\n") gefunden wurde
//            if (($end = strpos($currentBuffer, "\r", $start)) !== FALSE) {
//                // Extrahieren des Nachrichtensegments aus dem Puffer
//                $messageSegment = substr($currentBuffer, $start - 3, ($end - $start + 3));
//
//
//                $this->processAisRaw($messageSegment);
//
//                // Aktualisieren der letzten Position im Puffer
//                $lastPosition = $end + 1;
//            } else break; // Wenn das Ende des Segments nicht gefunden wurde, wird die Schleife unterbrochen
//        }
//
//        // Bereinigen des Puffers: Entfernen der bereits verarbeiteten Daten
//        if ($lastPosition > 0) $currentBuffer = substr($currentBuffer, $lastPosition);
//
//        // Prüfen auf Pufferüberlauf und Zurücksetzen bei Bedarf
//        if (strlen($currentBuffer) > 1024) $currentBuffer = "";



    /**
     * Erzeugt die AIS-kodierte Darstellung für eine Breitengrad-Koordinate.
     *
     * @param float $latitude Die Breitengrad-Koordinate, die kodiert werden soll
     * @return int Die AIS-kodierte Darstellung der Breitengrad-Koordinate
     */
    function encodeAisLatitude($latitude) {
        if ($latitude < 0.0) {
            $latitude = -$latitude;
            $isNegative = true;
        } else {
            $isNegative = false;
        }

        $aisEncodedLatitude = 0x00000000;
        $aisEncodedLatitude = intval($latitude * 600000.0);

        // Falls die Koordinate negativ war, wird sie invertiert, um das Zweierkomplement zu bilden
        if ($isNegative) {
            $aisEncodedLatitude = ~$aisEncodedLatitude;
            $aisEncodedLatitude += 1;
            $aisEncodedLatitude &= 0x07FFFFFF; // Begrenzung auf 28 Bits
        }

        return $aisEncodedLatitude;
    }

    /**
     * Erzeugt die AIS-kodierte Darstellung für eine Längengrad-Koordinate.
     *
     * @param float $longitude Die Längengrad-Koordinate, die kodiert werden soll
     * @return int Die AIS-kodierte Darstellung der Längengrad-Koordinate
     */
    function encodeAisLongitude($longitude) {
        // Prüfen, ob der Längengrad negativ ist
        if ($longitude < 0.0) {
            $longitude = -$longitude;
            $isNegative = true; // Markierung für negativen Wert
        } else {
            $isNegative = false;
        }

        $aisEncodedLongitude = 0x00000000;
        $aisEncodedLongitude = intval($longitude * 600000.0); // Kodieren des Längengrads

        // Falls die Koordinate negativ war, wird sie invertiert, um das Zweierkomplement zu bilden
        if ($isNegative) {
            $aisEncodedLongitude = ~$aisEncodedLongitude;
            $aisEncodedLongitude += 1;
            $aisEncodedLongitude &= 0x0FFFFFFF; // Begrenzung auf 28 Bits
        }

        return $aisEncodedLongitude;
    }

    /**
     * Konvertiert einen Zeichenstring in eine binäre Darstellung.
     *
     * @param string $inputString - Der Eingabezeichenstring
     * @param int $maxBinaryLength - Die maximale Länge der binären Darstellung
     * @return string - Die binäre Darstellung des Zeichenstrings
     */
    function charToBinary($inputString, $maxBinaryLength) {
        $stringLength = strlen($inputString);

        // Kürzen des Eingabezeichenstrings, wenn er länger ist als die maximale Länge
        if ($stringLength > $maxBinaryLength) {
            $inputString = substr($inputString, 0, $maxBinaryLength);
        }

        // Auffüllen des Zeichenstrings mit Nullen, falls er kürzer ist als die maximale Länge
        if ($stringLength < $maxBinaryLength) {
            $padding = str_repeat('0', ($maxBinaryLength - $stringLength) * 6);
        } else {
            $padding = '';
        }

        $binaryRepresentation = '';

        // Tabelle für die Zuordnung von Zeichen zu binären Werten
        $aisChars = array(
            '@'=>0, 'A'=>1, 'B'=>2, 'C'=>3, 'D'=>4, 'E'=>5, 'F'=>6, 'G'=>7, 'H'=>8, 'I'=>9,
            'J'=>10, 'K'=>11, 'L'=>12, 'M'=>13, 'N'=>14, 'O'=>15, 'P'=>16, 'Q'=>17, 'R'=>18, 'S'=>19,
            'T'=>20, 'U'=>21, 'V'=>22, 'W'=>23, 'X'=>24, 'Y'=>25, 'Z'=>26, '['=>27, '\\'=>28, ']'=>29,
            '^'=>30, '_'=>31, ' '=>32, '!'=>33, '\"'=>34, '#'=>35, '$'=>36, '%'=>37, '&'=>38, '\''=>39,
            '('=>40, ')'=>41, '*'=>42, '+'=>43, ','=>44, '-'=>45, '.'=>46, '/'=>47, '0'=>48, '1'=>49,
            '2'=>50, '3'=>51, '4'=>52, '5'=>53, '6'=>54, '7'=>55, '8'=>56, '9'=>57, ':'=>58, ';'=>59,
            '<'=>60, '='=>61, '>'=>62, '?'=>63
        );

        $charactersArray = str_split($inputString);
        if ($charactersArray) {
            foreach ($charactersArray as $character) {
                // Konvertieren des Zeichens in den zugehörigen binären Wert
                if (isset($aisChars[$character])) {
                    $decimalValue = $aisChars[$character];
                } else {
                    $decimalValue = 0;
                }

                // Konvertieren des Dezimalwerts in eine 6-stellige binäre Darstellung
                $binaryValue = str_pad(decbin($decimalValue), 6, '0', STR_PAD_LEFT);
                $binaryRepresentation .= $binaryValue;
            }
        }

        return $binaryRepresentation.$padding;
    }

    /**
     * Erzeugt eine AIS-Nachricht aus codierten Daten und optionalen Parametern.
     *
     * @param string $encodedData - Die codierten Daten
     * @param int $messagePart - Der Teil der Nachricht (optional, Standard: 1)
     * @param int $totalParts - Die Gesamtzahl der Teile (optional, Standard: 1)
     * @param string $sequenceNumber - Die Sequenznummer (optional)
     * @param string $aisChannel - Der AIS-Kanal (optional, Standard: 'A')
     * @return string - Die erzeugte AIS-Nachricht
     */
    function createAisMessage($encodedData, $messagePart = 1, $totalParts = 1, $sequenceNumber = '', $aisChannel = 'A') {
        $bitLength = strlen($encodedData);
        $remainingBits = $bitLength % 6;
        $paddingLength = ($remainingBits > 0) ? 6 - $remainingBits : 0;
        $encodedData .= str_repeat("0", $paddingLength);

        $ituMessage = '';

        foreach (str_split($encodedData, 6) as $chunk) {
            $decimalValue = bindec($chunk);
            $decimalValue += ($decimalValue < 40) ? 48 : 56;
            $ituMessage .= chr($decimalValue);
        }

        $checksum = 0;
        $ituMessage = "AIVDM,$messagePart,$totalParts,$sequenceNumber,$aisChannel,".$ituMessage.",0";

        foreach (str_split($ituMessage) as $char) {
            $checksum ^= ord($char);
        }

        $hexArray = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
        $lsb = $checksum & 0x0F;
        $lsbHex = ($lsb >=0 && $lsb <= 15) ? $hexArray[$lsb] : '0' ;

        $msb = (($checksum & 0xF0) >> 4) & 0x0F;
        $msbHex = ($msb >=0 && $msb <= 15) ? $hexArray[$msb] : '0';

        $finalAisMessage = '!'.$ituMessage."*{$msbHex}{$lsbHex}\r\n";

        // Entfernen der Padding-Bits vor der Rückgabe der Nachricht
        return $finalAisMessage;
    }

//    function createAisMessage($_enc, $_part=1,$_total=1,$_seq='',$_ch='A') {
//        $len_bit = strlen($_enc);
//        $rem6 = $len_bit % 6;
//        $pad6_len = 0;
//        if ($rem6) $pad6_len = 6 - $rem6;
//        //echo  $pad6_len.'<br>';
//        $_enc .= str_repeat("0", $pad6_len); // pad the text...
//        $len_enc = strlen($_enc) / 6;
//        //echo $_enc.' '.$len_enc.'<br/>';
//
//        $itu = '';
//
//        for ($i=0; $i<$len_enc; $i++) {
//            $offset = $i * 6;
//            $dec = bindec(substr($_enc,$offset,6));
//            if ($dec < 40) $dec += 48;
//            else $dec += 56;
//            //echo chr($dec)." $dec<br/>";
//            $itu .= chr($dec);
//        }
//
//        // add checksum
//        $chksum = 0;
//        $itu = "AIVDM,$_part,$_total,$_seq,$_ch,".$itu.",0";
//
//        $len_itu = strlen($itu);
//        for ($i=0; $i<$len_itu; $i++) {
//            $chksum ^= ord( $itu[$i] );
//        }
//
//        $hex_arr = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
//        $lsb = $chksum & 0x0F;
//        if ($lsb >=0 && $lsb <= 15 ) $lsbc = $hex_arr[$lsb];
//        else $lsbc = '0';
//        $msb = (($chksum & 0xF0) >> 4) & 0x0F;
//        if ($msb >=0 && $msb <= 15 ) $msbc = $hex_arr[$msb];
//        else $msbc = '0';
//
//        $itu = '!'.$itu."*{$msbc}{$lsbc}\r\n";
//        return $itu;
//    }

}
