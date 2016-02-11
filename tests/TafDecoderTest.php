<?php

namespace TafDecoder\Test;

use TafDecoder\TafDecoder;
use TafDecoder\Entity\ForecastPeriod;

class TafDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    public function __construct()
    {
        $this->decoder = new TafDecoder();
    }

    public function testConstruct()
    {
        $d = new TafDecoder();
    }

    /**
     * Test parsing of a valid TAF
     */
    public function testParse()
    {
        $raw_taf = "TAF TAF LIRU 032244Z 0318/0406\nCNL\n";
        $d       = $this->decoder->parseStrict($raw_taf);

        $this->assertTrue($d->isValid());
        $this->assertEquals("TAF TAF LIRU 032244Z 0318/0406 CNL", $d->getRawTaf());
        $this->assertEquals('TAF', $d->getType());
        $this->assertEquals('LIRU', $d->getIcao());
        $this->assertEquals(3, $d->getDay());
        $this->assertEquals('22:44 UTC', $d->getTime());
        /** @var ForecastPeriod $fp */
        $fp = $d->getForecastPeriod();
        $this->assertEquals(3, $fp->getFromDay());
        $this->assertEquals(18, $fp->getFromHour());
        $this->assertEquals(4, $fp->getToDay());
        $this->assertEquals(6, $fp->getToHour());

    }

    /**
     * Test parsing of a short, invalid TAF, without strict option activated
     */
    public function testParseInvalid()
    {
        // launch decoding
        $d = $this->decoder->parseNotStrict('TAF TAF LIRU 032244Z 0318/0206 CNL\n');

        $this->assertFalse($d->isValid());
        $this->assertEquals(1, count($d->getDecodingExceptions()));
    }

    /**
     * Test object-wide strict option
     */
    public function testParseDefaultStrictMode()
    {
        // strict mode, max 1 error triggered
        $this->decoder->setStrictParsing(true);
        $d = $this->decoder->parse('TAF TAF LIR 032244Z 0318/0206 CNL\n');
        $this->assertEquals(1, count($d->getDecodingExceptions()));

        // not strict: several errors triggered (3 because the icao failure causes datetime to fail too)
        $this->decoder->setStrictParsing(false);
        $d = $this->decoder->parse('TAF TAF LIR 032244Z 0318/0206 CNL\n');
        $this->assertEquals(3, count($d->getDecodingExceptions()));
    }

}