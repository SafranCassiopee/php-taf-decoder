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
        $raw_taf = "TAF TAF LIRU 032244Z 0318/0406 23010KT\nCNL\n";
        $d       = $this->decoder->parseStrict($raw_taf);

        $this->assertTrue($d->isValid());
        $this->assertEquals("TAF TAF LIRU 032244Z 0318/0406 23010KT CNL", $d->getRawTaf());
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
        $d = $this->decoder->parseNotStrict('TAF TAF LIRU 032244Z 0318/0206 23010KT CNL\n');

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
        $d = $this->decoder->parse('TAF TAF LIR 032244Z 0318/0206 23010KT CNL\n');
        $this->assertEquals(1, count($d->getDecodingExceptions()));

        // not strict: several errors triggered (4 because the icao failure causes the next ones to fail too)
        $this->decoder->setStrictParsing(false);
        $d = $this->decoder->parse('TAF TAF LIR 032244Z 0318/0206 23010KT CNL\n');
        $this->assertEquals(4, count($d->getDecodingExceptions()));
    }

    /**
     * Test parsing of invalid TAFs
     */
    public function testParseErrors()
    {
        $error_dataset = array(
            array('TAF LFPG aaa bbb cccc', 'DatetimeChunkDecoder', 'AAA BBB CCCC'),
            array('TAF LFPO 231027Z NIL 1234', 'ForecastPeriodChunkDecoder', 'NIL 1234'),
            array('TAF LFPO 231027Z 2310/2411 NIL 12345 ','SurfaceWindChunkDecoder','NIL 12345'),
        );

        foreach ($error_dataset as $taf_error) {
            // launch decoding
            $d = $this->decoder->parseNotStrict($taf_error[0]);

            // check the error triggered
            $this->assertFalse($d->isValid());
            $errors = $d->getDecodingExceptions();
            $first_error = $errors[0];
            $this->assertEquals($taf_error[1], $first_error->getChunkDecoder());
            $this->assertEquals($taf_error[2], $first_error->getChunk());
        }
    }
}