<?php

namespace Ais\Helper\tests;
use Ais\Helper\Message;
use Ais\Helper\Helper;

use PHPUnit\Framework\TestCase;




class HelperTest extends TestCase
{
    public function testConvertToLatitude()
    {
        // Testfall 1: Positive Nordhalbkugel-Koordinate
        $coordinateValue1 = 12345678; // Beispielwert für Nordhalbkugel
        $expectedLatitude1 = 20.57613; // Erwarteter Wert für Nordhalbkugel
        $tolerance = 0.0001; // Toleranz für den Vergleich

        // Testfall 2: Negative Südhalbkugel-Koordinate
        $coordinateValue2 = -1442774017; // Beispielwert für Südhalbkugel
        $expectedLatitude2 = 56.034985; // Erwarteter Wert für Südhalbkugel

        // Instanz der Klasse mit der Funktion
        $helper = new Helper();

        // Testfall 1 überprüfen
        $result1 = $helper->convertToLatitude($coordinateValue1);
        $this->assertEquals($expectedLatitude1, $result1);

        // Testfall 2 überprüfen
        $result2 = $helper->convertToLatitude($coordinateValue2);
        $this->assertEquals($expectedLatitude2, $result2);
    }

    public function testConvertsPositiveLongitudeToPositiveFloat()
    {
        $helper = new Helper();
        $coordinateValue = 1234567890;
        $expectedLongitude = -179.34898333333334;

        $actualLongitude = $helper->convertToLongitude($coordinateValue);

        $this->assertEquals($expectedLongitude, $actualLongitude);
    }

    public function testConvertsNegativeLongitudeToNegativeFloat()
    {
        $helper = new Helper();
        $coordinateValue = -98765432;
        $expectedLongitude = -164.60905333333332;

        $actualLongitude = $helper->convertToLongitude($coordinateValue);

        $this->assertEquals($expectedLongitude, $actualLongitude);
    }

    public function testconvertAsciiToDecimalLetterA()
    {
        $helper = new Helper();
        $char = 'A'; // ASCII-Zeichen 'A' (Dezimalwert 65)

        $decimalValue = $helper->convertAsciiToDecimal($char);

        $this->assertEquals(65, $decimalValue);
    }

    public function testconvertAsciiToDecimalDigit1()
    {
        $helper = new Helper();
        $char = '1'; // ASCII-Zeichen '1' (Dezimalwert 49)

        $decimalValue = $helper->convertAsciiToDecimal($char);

        $this->assertEquals(49, $decimalValue);
    }

    public function testConvertAsciiTo8BitRange1()
    {
        $helper = new Helper();
        $ascii = 60; // Beispielwert innerhalb des Bereichs (48-87)

        $result = $helper->convertAsciiTo8Bit($ascii);

        $this->assertEquals(140, $result);
    }

    public function testConvertAsciiTo8BitRange2()
    {
        $helper = new Helper();
        $ascii = 110; // Beispielwert innerhalb des Bereichs (96-119)

        $result = $helper->convertAsciiTo8Bit($ascii);

        $this->assertEquals(182, $result);
    }

    public function testConvertAsciiTo8BitOutsideRange()
    {
        $helper = new Helper();
        $ascii = 30; // Beispielwert außerhalb des Bereichs

        $result = $helper->convertAsciiTo8Bit($ascii);

        $this->assertEquals(30, $result); // Erwartet, dass der Wert unverändert bleibt
    }



    public function testConvertDecimalTo6BitPositiveNumber()
    {
        $helper = new Helper();
        $decimal = 42;

        $result = $helper->convertDecimalTo6Bit($decimal);

        $this->assertEquals('101010', $result);
    }


    public function testConvertDecimalTo6BitNegativeNumber()
    {
        $helper = new Helper();
        $decimal = -42; // Beachte: PHP wandelt negative Dezimalwerte in Binär als 2er-Komplement um.

        $result = $helper->convertDecimalTo6Bit($decimal);

        $this->assertEquals('010110', $result);
    }

    public function testConvertBinaryToAISCharsValidInput()
    {
        $helper = new Helper();
        $binaryString = '010101110011001100110011';
        $startIndex = 0;
        $partSize = 24;

        $result = $helper->convertBinaryToAISChars($binaryString, $startIndex, $partSize);

        $this->assertEquals('U3L3', $result);
    }

    public function testConvertBinaryToAISCharsInvalidInput()
    {
        $helper = new Helper();
        $binaryString = '1010101'; // Ungültige Länge, da nicht durch 6 teilbar
        $startIndex = 0;
        $partSize = 7;

        $result = $helper->convertBinaryToAISChars($binaryString, $startIndex, $partSize);

        $this->assertEquals('', $result);
    }









}
