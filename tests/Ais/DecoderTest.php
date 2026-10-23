<?php

namespace Ais\tests;

use Ais\Message\Helper;
use Ais\Message\Message123;
use Ais\Message\Message18;
use Ais\Message\Message24;
use Ais\Message\Message5;

use PHPUnit\Framework\TestCase;

class DecoderTest extends TestCase
{
    private const RECEIVED_TIMESTAMP = 1700000000;

    private Helper $helper;

    protected function setUp(): void
    {
        $this->helper = new Helper();
    }

    public function testDecodeBitsMessageType1()
    {
        // Bereits in 6-Bit-Form vorliegende Nutzdaten einer Nachricht vom Typ 1
        $aisdata168 = "000001000100101101010111011100001010010000000000000000000000110111001000110111100001101000011100011101110010011111101011110000110101010111010000000000001000011000011011";

        $message = new Message123(1, self::RECEIVED_TIMESTAMP);
        $result = $message->decode($aisdata168);

        $this->assertSame($message, $result);
        $this->assertSame(1, $result->messageType);
        $this->assertSame(316005417, $result->mmsi);
        $this->assertEqualsWithDelta(-123.891966, $result->longitude, 0.000001);
        $this->assertEqualsWithDelta(49.746983, $result->latitude, 0.000001);
        $this->assertSame(self::RECEIVED_TIMESTAMP, $result->receivedTimestamp);
    }

    public function testDecodeMessageType1()
    {
        $result = $this->helper->decodeMessages(
            ["!AIVDM,1,1,,B,177KQJ5000G?tO`K>RA1wUbN0TKH,0*5C"],
            self::RECEIVED_TIMESTAMP
        );

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Message123::class, $result[0]);
        $this->assertSame(477553000, $result[0]->mmsi);
        $this->assertEqualsWithDelta(-122.345833, $result[0]->longitude, 0.000001);
        $this->assertEqualsWithDelta(47.582833, $result[0]->latitude, 0.000001);
    }

    public function testDecodeMultipartMessageType5()
    {
        // Nachricht vom Typ 5 besteht aus zwei Segmenten
        $result = $this->helper->decodeMessages(
            [
                "!AIVDM,2,1,1,A,55?MbV02;H;s<HtKR20EHE:0@T4@Dn2222222216L961O5Gf0NSQEp6ClRp8,0*1C",
                "!AIVDM,2,2,1,A,88888888880,2*25",
            ],
            self::RECEIVED_TIMESTAMP
        );

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Message5::class, $result[0]);
        $this->assertSame(351759000, $result[0]->mmsi);
        $this->assertSame('EVER DIADEM', $result[0]->name);
        $this->assertNull($result[0]->longitude);
        $this->assertNull($result[0]->latitude);
    }

    public function testConvertBinaryToShipNameRemovesPadding()
    {
        // "AB C" gefolgt von Füllzeichen: '@' (0) und Leerzeichen (32)
        $bits = '000001' . '000010' . '100000' . '000011' . '000000' . '100000' . '000000';

        $this->assertSame('AB C', $this->helper->convertBinaryToShipName($bits, 0, 42));
    }

    public function testDecodeMessageType18()
    {
        $result = $this->helper->decodeMessages(
            ["!AIVDM,1,1,,A,B52K>;h00Fc>jpUlNV@ikwpUoP06,0*4C"],
            self::RECEIVED_TIMESTAMP
        );

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Message18::class, $result[0]);
        $this->assertSame(338087471, $result[0]->mmsi);
        $this->assertEqualsWithDelta(-74.072131, $result[0]->longitude, 0.000001);
        $this->assertEqualsWithDelta(40.68454, $result[0]->latitude, 0.000001);
    }

    public function testDecodeMessageType24PartA()
    {
        $result = $this->helper->decodeMessages(
            ["!AIVDM,1,1,,A,H42O55i18tMET00000000000000,2*6D"],
            self::RECEIVED_TIMESTAMP
        );

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Message24::class, $result[0]);
        $this->assertSame(271041815, $result[0]->mmsi);
        $this->assertSame('PROGUY', $result[0]->name);
    }

    public function testDecodeMessageType24PartBHasNoName()
    {
        // Teil B enthält keinen Schiffsnamen, ein bekannter Name darf nicht überschrieben werden
        $result = $this->helper->decodeMessages(
            ["!AIVDM,1,1,,A,H42O55lti4hhhilD3nink000?050,0*40"],
            self::RECEIVED_TIMESTAMP
        );

        $this->assertCount(1, $result);
        $this->assertInstanceOf(Message24::class, $result[0]);
        $this->assertSame(271041815, $result[0]->mmsi);
        $this->assertSame('', $result[0]->name);
    }

    public function testInvalidChecksumIsIgnored()
    {
        // Der Decoder gibt bei leeren Zwischenergebnissen Warnungen aus
        $this->expectOutputRegex('/Warnung/');

        $result = $this->helper->decodeMessages(
            ["!AIVDM,1,1,,B,177KQJ5000G?tO`K>RA1wUbN0TKH,0*00"],
            self::RECEIVED_TIMESTAMP
        );

        $this->assertSame([], $result);
    }

    public function testGetEncodeData()
    {
        $result = $this->helper->decodeMessages(
            ["!AIVDM,1,1,,B,177KQJ5000G?tO`K>RA1wUbN0TKH,0*5C"],
            self::RECEIVED_TIMESTAMP
        );

        $data = json_decode($result[0]->getEncodeData(), true);

        $this->assertSame(['name', 'longitude', 'latitude', 'receivedTimestamp', 'mmsi'], array_keys($data));
        $this->assertSame(477553000, $data['mmsi']);
        $this->assertSame(self::RECEIVED_TIMESTAMP, $data['receivedTimestamp']);
    }

}
