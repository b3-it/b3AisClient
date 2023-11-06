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

    public function testProcess_ais_buf()
    {
        $helper = new Helper();
        $buf = ["!AIVDM,2,1,5,A,539o8i400000@?CKKT1=Bp`tP4ppE<00000000153hJ<54@P00PTUCThU,0*09\r",
            "!AIVDM,2,2,5,A,AE51C000000000,2*62\r"];
        $expected = ["!AIVDM,2,1,5,A,539o8i400000@?CKKT1=Bp`tP4ppE<00000000153hJ<54@P00PTUCThU,0*09",
            "!AIVDM,2,2,5,A,AE51C000000000,2*62"];
        $result = $helper->process_ais_buf($buf);

        $this->assertEquals($expected,$result);
    }

    public function testcalculateAISChecksum(){
        $helper = new Helper();
        $buf = "!AIVDM,2,1,5,A,539o8i400000@?CKKT1=Bp`tP4ppE<00000000153hJ<54@P00PTUCThU,0*09";
        $expected = "09";
        $result = $helper->calculateAISChecksum($buf);

        $this->assertEquals($expected,$result);

    }

    public function testprocessAisRaw(){
        $helper = new Helper();
        $buf = ["!AIVDM,2,1,5,A,539o8i400000@?CKKT1=Bp`tP4ppE<00000000153hJ<54@P00PTUCThU,0*09\r",
            "!AIVDM,2,2,5,A,AE51C000000000,2*62\r"];
        $expected = "539o8i400000@?CKKT1=Bp`tP4ppE<00000000153hJ<54@P00PTUCThUAE51C000000000";
        $result = $helper->extractPayload($buf);

        $this->assertEquals($expected,$result);

    }



    public function testprocessAisRaw1(){
        $helper = new Helper();
        $buf = ["!AIVDM,1,1,,A,139O`j?0000PwMRNQwi@0@Oh0<1p,0*04\r"];
        $expected = "139O`j?0000PwMRNQwi@0@Oh0<1p";
        $result = $helper->extractPayload($buf);

        $this->assertEquals($expected,$result);

    }

    public function testprocessAisRaw2(){
        $helper = new Helper();
        $buf = ["!AIVDM,2,1,5,A,539o8i400000@?CKKT1=Bp`tP4ppE<00000000153hJ<54@P00PTUCThU,0*09\r", "!AIVDM,1,1,,A,139O`j?0000PwMRNQwi@0@Oh0<1p,0*04\r",
            "!AIVDM,2,2,5,A,AE51C000000000,2*62\r" ];

        $expected = ["539o8i400000@?CKKT1=Bp`tP4ppE<00000000153hJ<54@P00PTUCThUAE51C000000000", "139O`j?0000PwMRNQwi@0@Oh0<1p"];
        $result = $helper->extractPayload($buf);

        $this->assertEqualsCanonicalizing($expected,$result);

    }



    public function testprocessAisRaw3(){
        $helper = new Helper();
        $buf = ["!AIVDM,2,1,0,B,C8u:8C@t7@TnGCKfm6Po`e6N`:Va0L2J;06HV50JV?SjBPL3,0*28\r",
                "!AIVDM,2,2,0,B,11RP,0*17\r",
                "!AIVDO,2,1,5,B,E1c2;q@b44ah4ah0h:2ab@70VRpU<Bgpm4:gP50HH`Th`QF5,0*7B\r",
                "!AIVDO,2,2,5,B,1CQ1A83PCAH0,0*60\r" ];

        $expected = ["C8u:8C@t7@TnGCKfm6Po`e6N`:Va0L2J;06HV50JV?SjBPL311RP", "E1c2;q@b44ah4ah0h:2ab@70VRpU<Bgpm4:gP50HH`Th`QF51CQ1A83PCAH0"];
        $result = $helper->extractPayload($buf);

        $this->assertEquals($expected,$result);

    }
//    public function testdecodeMessages()
//    {
//        // Erstelle eine Instanz der Klasse, die die decodeAIS-Funktion enthält
//        $helper = new Helper();
//
//        // Beispiel AIS-Nachricht für Nachrichtentyp 1 (angenommen, dies ist gültige AIS-Nachricht)
//        $aisdata168 = ["!AIVDM,1,1,,A,139O`j?0000PwMRNQwi@0@Oh0<1p,0*04\r" ,
//            "!AIVDM,1,1,,B,13eK4H01B7PPVj4OD@seDrWb84hD,0*77\r" ,
//            "!AIVDM,1,1,,A,E>j9driW9WhH@860b37a6P00000@ATmQ?Tnh800000sh20,4*56\r" ,
//            "!AIVDM,1,1,,A,339NQJP01HPe90`N`s@:HpKb020@,0*4B\r" ,
//            "!AIVDM,1,1,,A,13A=Ip001t0QNBTO090:n`eh0D25,0*21\r" ,
//            "!AIVDM,1,1,,B,139dl4?00B0RvwpNiM<=2rKh0@Pl,0*08\r" ,
//            "!AIVDM,1,1,,B,13MA0?@Oiw0Ml?RNdhRPuPSf08Pm,0*2C\r" ,
//            "!AIVDM,1,1,,B,33b51:700KPLc8FN`Rt:98Ah00ri,0*05\r" ,
//            "!AIVDM,1,1,,B,139NVn?P00PcVVDNcqi8BOwf28Pn,0*55\r" ,
//            "!AIVDM,1,1,,A,33@Tt2001<PW@J4N`R1b087d00vP,0*12\r" ];
//        $expectedResult = new Message($messageType = bindec(substr($aisdata168[0], 0, 6)));
//
//        $result = $helper->decodeMessages($aisdata168);
//
//        // Erwartete Ausgabe (dies hängt von der Implementierung der DecoderClass ab)
//
//        // Vergleiche das tatsächliche Ergebnis mit dem erwarteten Ergebnis
//        $this->assertEquals($expectedResult, $result);
//    }


}
