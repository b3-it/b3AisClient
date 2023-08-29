<?php

namespace Ais\tests;

use Ais\Decoder;
use Ais\Helper\Message;
use Ais\Helper\Helper;

use PHPUnit\Framework\TestCase;

class DecoderTest extends TestCase
{
    public function testDecodeAISMessageType1()
    {
        // Erstelle eine Instanz der Klasse, die die decodeAIS-Funktion enthält
        $decoder = new Decoder(); // Ersetze "DecoderClass" durch den tatsächlichen Namen deiner Klasse

        // Beispiel AIS-Nachricht für Nachrichtentyp 1 (angenommen, dies ist gültige AIS-Nachricht)
        $aisdata168 = "000001000100101101010111011100001010010000000000000000000000110111001000110111100001101000011100011101110010011111101011110000110101010111010000000000001000011000011011";
        // Rufe die decodeAIS-Funktion auf
        $result = $decoder->decodeAIS($aisdata168, $aux);

        // Erwartete Ausgabe (dies hängt von der Implementierung der DecoderClass ab)
        $expectedResult = new Message();
        $expectedResult->timestamp = time();
        $expectedResult->id = 1;
        $expectedResult->mmsi = 316005417;
        $expectedResult->courseOverGround = 301.1; // Angenommen, dies ist der erwartete Wert
        $expectedResult->speedOverGround = 0; // Angenommen, dies ist der erwartete Wert
        $expectedResult->longitude = -123.89196666666666; // Angenommen, dies ist der erwartete Wert
        $expectedResult->latitude = 49.74698333333333; // Angenommen, dies ist der erwartete Wert
        $expectedResult->class = 1;

        // Vergleiche das tatsächliche Ergebnis mit dem erwarteten Ergebnis
        $this->assertEquals($expectedResult, $result);
    }

}
