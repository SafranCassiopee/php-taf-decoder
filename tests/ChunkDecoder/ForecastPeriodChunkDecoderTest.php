<?php

namespace tafDecoder\Test\ChunkDecoder;

use TafDecoder\Exception\ChunkDecoderException;
use TafDecoder\ChunkDecoder\ForecastPeriodChunkDecoder;
use TafDecoder\Entity\ForecastPeriod;

class ForecastPeriodChunkDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    protected function setup()
    {
        $this->decoder = new ForecastPeriodChunkDecoder();
    }


    /**
     * Test parsing of valid forecast period chunks
     * @param $chunk
     * @param $from_day
     * @param $from_hour
     * @param $to_day
     * @param $to_hour
     * @param $is_valid
     * @param $remaining
     * @dataProvider getValidChunk
     */
    public function testParse($chunk, $from_day, $from_hour, $to_day, $to_hour, $is_valid, $remaining)
    {
        $decoded = $this->decoder->parse($chunk);
        /** @var ForecastPeriod $fp */
        $fp = $decoded['result']['forecastPeriod'];

        $this->assertEquals($from_day, $fp->getFromDay());
        $this->assertEquals($from_hour, $fp->getFromHour());
        $this->assertEquals($to_day, $fp->getToDay());
        $this->assertEquals($to_hour, $fp->getToHour());
        $this->assertEquals($is_valid, $fp->isValid());
        $this->assertEquals($remaining, $decoded['remaining_taf']);
    }

    /**
     * Test parsing of invalid forecast period chunks
     * @param $chunk
     * @expectedException \TafDecoder\Exception\ChunkDecoderException
     * @dataProvider getInvalidChunk
     */
    public function testParseInvalidChunk($chunk)
    {
        $this->decoder->parse($chunk);
    }


    public function getValidChunk()
    {
        return array(
            array(
                "chunk"     => "0318/0406 CNL",
                "from_day"  => 3,
                "from_hour" => 18,
                "to_day"    => 4,
                "to_hour"   => 6,
                "is_valid"  => true,
                "remaining" => "CNL",
            ),
            array(
                "chunk"     => "0318/0323 CNL",
                "from_day"  => 3,
                "from_hour" => 18,
                "to_day"    => 3,
                "to_hour"   => 23,
                "is_valid"  => true,
                "remaining" => "CNL",
            ),
        );
    }

    public function getInvalidChunk()
    {
        return array(
            array(
                "chunk"     => "0318 CNL",
                "from_day"  => 3,
                "from_hour" => 18,
                "to_day"    => 0,
                "to_hour"   => 0,
                "is_valid"  => false,
                "remaining" => "CNL",
            ),
            array(
                "chunk"     => "3218/0206 CNL",
                "from_day"  => 32,
                "from_hour" => 18,
                "to_day"    => 2,
                "to_hour"   => 6,
                "is_valid"  => false,
                "remaining" => "CNL",
            ),
        );
    }
}