<?php

namespace TafDecoder\Test\ChunkDecoder;

use TafDecoder\ChunkDecoder\WeatherChunkDecoder;
use TafDecoder\Entity\WeatherPhenomenon;

class WeatherChunkDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    protected function setup()
    {
        $this->decoder = new WeatherChunkDecoder();
    }

    /**
     * Test parsing of valid weather chunks
     * @param $chunk
     * @param $weather_intens
     * @param $weather_desc
     * @param $weather_phenom
     * @param $remaining
     * @dataProvider getChunk
     */
    public function testParse($chunk, $weather_intens, $weather_desc, $weather_phenom, $remaining)
    {
        $decoded = $this->decoder->parse($chunk);
        /** @var WeatherPhenomenon $weather */
        $weather = $decoded['result']['weather'];
        $this->assertEquals($weather_intens, $weather->getIntensityProximity());
        $this->assertEquals($weather_desc, $weather->getDescriptor());
        $this->assertEquals($weather_phenom, $weather->getPhenomenons());
        $this->assertEquals($remaining, $decoded['remaining_taf']);
    }

    public function getChunk()
    {
        return array(
            array(
                "chunk"             => "VCBLSN AAA",
                "weather_intens"    => "VC",
                "weather_desc"      => "BL",
                "weather_phenom"    => array("SN"),
                "remaining"         => "AAA",
            ),
            array(
                "chunk"             => "-PL BBB",
                "weather_intens"    => "-",
                "weather_desc"      => "",
                "weather_phenom"    => array("PL"),
                "remaining"         => "BBB",
            ),
            array(
                "chunk"             => "+TSRA CCC",
                "weather_intens"    => "+",
                "weather_desc"      => "TS",
                "weather_phenom"    => array("RA"),
                "remaining"         => "CCC",
            ),
            array(
                "chunk"             => "TSRABR DDD",
                "weather_inten"     => "",
                "weather_desc"      => "TS",
                "weather_phenom"    => array("RA","BR"),
                "remaining"         => "DDD",
            ),
        );
    }
}
