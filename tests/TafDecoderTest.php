<?php

namespace TafDecoder\Test;

use TafDecoder\TafDecoder;
use TafDecoder\Entity\Value;
use TafDecoder\Entity\ForecastPeriod;
use TafDecoder\Entity\SurfaceWind;
use TafDecoder\Entity\Visibility;
use TafDecoder\Entity\WeatherPhenomenon;
use TafDecoder\Entity\CloudLayer;
use TafDecoder\Entity\Temperature;

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
        $raw_taf = "TAF TAF LIRU 032244Z 0318/0406 23010KT P6SM -SHDZRA BKN020CB TX05/0318Z TNM03/0405Z";
        $d       = $this->decoder->parseStrict($raw_taf);

        $this->assertTrue($d->isValid());
        $this->assertEquals("TAF TAF LIRU 032244Z 0318/0406 23010KT P6SM -SHDZRA BKN020CB TX05/0318Z TNM03/0405Z", $d->getRawTaf());
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
        /** @var SurfaceWind $sw */
        $sw = $d->getSurfaceWind();
        $this->assertFalse($sw->withVariableDirection());
        $this->assertEquals(230, $sw->getMeanDirection()->getValue());
        $this->assertEquals('deg', $sw->getMeanDirection()->getUnit());
        $this->assertNull($sw->getDirectionVariations());
        $this->assertEquals(10, $sw->getMeanSpeed()->getValue());
        $this->assertEquals('kt', $sw->getMeanSpeed()->getUnit());
        $this->assertNull($sw->getSpeedVariations());
        /** @var Visibility $v */
        $v = $d->getVisibility();
        $this->assertEquals(6, $v->getVisibility()->getValue());
        $this->assertEquals('SM', $v->getVisibility()->getUnit());
        $this->assertTrue($v->getGreater());
        /** @var WeatherPhenomenon $wp */
        $wp = $d->getWeatherPhenomenon();
        $this->assertEquals('-', $wp[0]->getIntensityProximity());
        $this->assertEquals('SH', $wp[0]->getDescriptor());
        $phenomena = $wp[0]->getPhenomena();
        $this->assertEquals('DZ', $phenomena[0]);
        $this->assertEquals('RA', $phenomena[1]);
        $cls = $d->getClouds();
        /** @var CloudLayer $cl */
        $cl = $cls[0];
        $this->assertEquals('BKN', $cl->getAmount());
        $this->assertEquals(2000, $cl->getBaseHeight()->getValue());
        $this->assertEquals('ft', $cl->getBaseHeight()->getUnit());
        $this->assertEquals('CB', $cl->getType());
        /** @var Temperature $mint */
        $mint = $d->getMinTemperature();
        $this->assertEquals(-3, $mint->getTemperature()->getValue());
        $this->assertEquals('deg C', $mint->getTemperature()->getUnit());
        $this->assertEquals(4, $mint->getDay());
        $this->assertEquals(5, $mint->getHour());
        /** @var Temperature $maxt */
        $maxt = $d->getMaxTemperature();
        $this->assertEquals(5, $maxt->getTemperature()->getValue());
        $this->assertEquals('deg C', $maxt->getTemperature()->getUnit());
        $this->assertEquals(3, $maxt->getDay());
        $this->assertEquals(18, $maxt->getHour());

    }

    /**
     * Test parsing of a valid TAF
     */
    public function testParseSecond()
    {
        $raw_taf = "TAF TAF LIRU 032244Z 0318/0406 23010KT P6SM +TSRA FG BKN020CB TX05/0318Z TNM03/0405Z";
        $d       = $this->decoder->parseStrict($raw_taf);

        $this->assertTrue($d->isValid());
        $this->assertEquals("TAF TAF LIRU 032244Z 0318/0406 23010KT P6SM +TSRA FG BKN020CB TX05/0318Z TNM03/0405Z", $d->getRawTaf());
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
        /** @var SurfaceWind $sw */
        $sw = $d->getSurfaceWind();
        $this->assertFalse($sw->withVariableDirection());
        $this->assertEquals(230, $sw->getMeanDirection()->getValue());
        $this->assertEquals('deg', $sw->getMeanDirection()->getUnit());
        $this->assertNull($sw->getDirectionVariations());
        $this->assertEquals(10, $sw->getMeanSpeed()->getValue());
        $this->assertEquals('kt', $sw->getMeanSpeed()->getUnit());
        $this->assertNull($sw->getSpeedVariations());
        /** @var Visibility $v */
        $v = $d->getVisibility();
        $this->assertEquals(6, $v->getVisibility()->getValue());
        $this->assertEquals('SM', $v->getVisibility()->getUnit());
        $this->assertTrue($v->getGreater());
        /** @var WeatherPhenomenon $wp */
        $wp = $d->getWeatherPhenomenon();
        $this->assertEquals('+', $wp[0]->getIntensityProximity());
        $this->assertEquals('TS', $wp[0]->getDescriptor());
        $phenomena = $wp[0]->getPhenomena();
        $this->assertEquals('RA', $phenomena[0]);
        $phenomena = $wp[1]->getPhenomena();
        $this->assertEquals('FG', $phenomena[0]);
        $cls = $d->getClouds();
        /** @var CloudLayer $cl */
        $cl = $cls[0];
        $this->assertEquals('BKN', $cl->getAmount());
        $this->assertEquals(2000, $cl->getBaseHeight()->getValue());
        $this->assertEquals('ft', $cl->getBaseHeight()->getUnit());
        $this->assertEquals('CB', $cl->getType());
        /** @var Temperature $mint */
        $mint = $d->getMinTemperature();
        $this->assertEquals(-3, $mint->getTemperature()->getValue());
        $this->assertEquals('deg C', $mint->getTemperature()->getUnit());
        $this->assertEquals(4, $mint->getDay());
        $this->assertEquals(5, $mint->getHour());
        /** @var Temperature $maxt */
        $maxt = $d->getMaxTemperature();
        $this->assertEquals(5, $maxt->getTemperature()->getValue());
        $this->assertEquals('deg C', $maxt->getTemperature()->getUnit());
        $this->assertEquals(3, $maxt->getDay());
        $this->assertEquals(18, $maxt->getHour());

    }

    /**
     * Test parsing of a short, invalid TAF, without strict option activated
     */
    public function testParseInvalid()
    {
        // launch decoding (forecast was cancelled)
        $d = $this->decoder->parseNotStrict("TAF LFMT 032244Z 0318/0206 CNL");
        $this->assertFalse($d->isValid());

        // launch decoding (surface wind is invalid)
        $d = $this->decoder->parseNotStrict("TAF TAF LIRU 032244Z 0318/0420 2300ABKT PSSM\nBKN020CB TX05/0318Z TNM03/0405Z\n");
        $this->assertFalse($d->isValid());
    }

    /**
     * Test object-wide strict option
     */
    public function testParseDefaultStrictMode()
    {
        // strict mode, max 1 error triggered
        $this->decoder->setStrictParsing(true);
        $d = $this->decoder->parse("TAF TAF LIR 032244Z 0318/0206 23010KT P6SM BKN020CB TX05/0318Z TNM03/0405Z\n");
        $this->assertEquals(1, count($d->getDecodingExceptions()));

        // not strict: several errors triggered (6 because the icao failure causes the next ones to fail too)
        $this->decoder->setStrictParsing(false);
        $d = $this->decoder->parse("TAF TAF LIR 032244Z 0318/0206 23010KT\n");
        $this->assertEquals(6, count($d->getDecodingExceptions()));
    }

    /**
     * Test parsing of invalid TAFs
     */
    public function testParseErrors()
    {
        $error_dataset = array(
            array('TAF LFPG aaa bbb cccc', 'DatetimeChunkDecoder', 'AAA BBB CCCC END'),
            array('TAF LFPO 231027Z NIL 1234', 'ForecastPeriodChunkDecoder', 'NIL 1234 END'),
            array('TAF LFPO 231027Z 2310/2411 NIL 12345 ','SurfaceWindChunkDecoder','NIL 12345 END'),
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
            $d->resetDecodingExceptions();
            $this->assertEmpty($d->getDecodingExceptions());
        }

    }

    /**
     *  Test invalid values
     */
    public function testValueErrors()
    {
        $newValue = new Value(null, null);
        $this->assertNull($newValue->getValue());
        $this->assertNull($newValue->newValue(null, null));
        $this->assertNull($newValue->newIntValue('AB', null)->getValue());
    }
}
