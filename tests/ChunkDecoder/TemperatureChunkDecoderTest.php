<?php

namespace TafDecoder\Test\ChunkDecoder;

use TafDecoder\ChunkDecoder\TemperatureChunkDecoder;
use TafDecoder\Entity\Temperature;

class TemperatureChunkDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    protected function setup()
    {
        $this->decoder = new TemperatureChunkDecoder();
    }

    /**
     * Test parsing valid temperature chunks
     * @param $chunk
     * @param $min_temp
     * @param $min_day
     * @param $min_hour
     * @param $max_temp
     * @param $max_day
     * @param $max_hour
     * @param $remaining
     * @dataProvider getChunk
     */
    public function testParse($chunk, $min_temp, $min_day, $min_hour, $max_temp, $max_day, $max_hour, $remaining)
    {
        $decoded = $this->decoder->parse($chunk);
        /** @var Temperature $min_temperature */
        $min_temperature = $decoded['result']['minTemperature'];
        /** @var Temperature $max_temperature */
        $max_temperature = $decoded['result']['maxTemperature'];

        $this->assertEquals("TN", $min_temperature->getType());
        $this->assertEquals($min_temp, $min_temperature->getTemperature()->getValue());
        $this->assertEquals("deg C", $min_temperature->getTemperature()->getUnit());
        $this->assertEquals($min_day, $min_temperature->getDay());
        $this->assertEquals($min_hour, $min_temperature->getHour());
        $this->assertEquals("TX", $max_temperature->getType());
        $this->assertEquals($max_temp, $max_temperature->getTemperature()->getValue());
        $this->assertEquals("deg C", $max_temperature->getTemperature()->getUnit());
        $this->assertEquals($max_day, $max_temperature->getDay());
        $this->assertEquals($max_hour, $max_temperature->getHour());
        $this->assertEquals($remaining, $decoded['remaining_taf']);
    }

    /**
     * Test parsing invalid chunks
     * @param $chunk
     * @expectedException \TafDecoder\Exception\ChunkDecoderException
     * @dataProvider getInvalidChunk
     */
    public function testParseInvalidChunk($chunk)
    {
        $this->decoder->parse($chunk);
    }


    public function getChunk()
    {
        return array(
            array(
                "chunk"     => "TX20/1012Z TN16/1206Z AAA",
                "min_temp"  => 16,
                "min_day"   => 12,
                "min_hour"  => 6,
                "max_temp"  => 20,
                "max_day"   => 10,
                "max_hour"  => 12,
                "remaining" => "AAA",
            ),
            array(
                "chunk"     => "TX03/1012Z TNM05/1206Z",
                "min_temp"  => -5,
                "min_day"   => 12,
                "min_hour"  => 6,
                "max_temp"  => 3,
                "max_day"   => 10,
                "max_hour"  => 12,
                "remaining" => "",
            ),
        );
    }

    public function getInvalidChunk()
    {
        return array(
            array("chunk" => "AAA"),
            array("chunk" => "TX04"),
            array("chunk" => "TX04/0102"),
            array("chunk" => "TX04/0102Z"),
            array("chunk" => "TX04/0102Z TX05/0203Z"),
            array("chunk" => "TX04/0102Z TN3/0203Z"),
            array("chunk" => "TX04/0102Z TN05/0203Z"),
        );
    }
}