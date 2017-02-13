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
     * @param $test_weather_phenoms
     * @param $remaining
     * @dataProvider getChunk
     */
    public function testParse($chunk, $test_weather_phenoms, $remaining)
    {
        $i = 0;
        $decoded = $this->decoder->parse($chunk);
        /** @var WeatherPhenomenon $weather */
        $weatherArray = $decoded['result']['weatherPhenomenon'];
        foreach ($weatherArray as $weather) {
            $this->assertEquals($test_weather_phenoms[$i]['weather_intens'], $weather->getIntensityProximity());
            $this->assertEquals($test_weather_phenoms[$i]['weather_desc'], $weather->getDescriptor());
            $this->assertEquals($test_weather_phenoms[$i]['weather_phenom'], $weather->getPhenomena());
            $i++;
        }
        $this->assertEquals($remaining, $decoded['remaining_taf']);
    }

    public function getChunk()
    {
        return array(
            array(
                "chunk" => "VCBLSN AAA",
                array(
                    array(
                        "weather_intens" => "VC",
                        "weather_desc" => "BL",
                        "weather_phenom" => array("SN")
                    )
                ),
                "remaining" => "AAA",
            ),
            array(
                "chunk" => "-PL BBB",
                array(
                    array(
                        "weather_intens" => "-",
                        "weather_desc" => "",
                        "weather_phenom" => array("PL")
                    )
                ),
                "remaining" => "BBB",
            ),
            array(
                "chunk" => "+TSRA CCC",
                array(
                    array(
                        "weather_intens" => "+",
                        "weather_desc" => "TS",
                        "weather_phenom" => array("RA")
                    )
                ),
                "remaining" => "CCC",
            ),
            array(
                "chunk" => "TSRABR DDD",
                array(
                    array(
                        "weather_intens" => "",
                        "weather_desc" => "TS",
                        "weather_phenom" => array("RA", "BR")
                    )
                ),
                "remaining" => "DDD",
            ),
            array(
                "chunk" => "-DZ FG DDD",
                array(
                    array(
                        "weather_intens" => "-",
                        "weather_desc" => "",
                        "weather_phenom" => array("DZ")
                    ),
                    array(
                        "weather_intens" => "",
                        "weather_desc" => "",
                        "weather_phenom" => array("FG")
                    )
                ),
                "remaining" => "DDD",
            ),
            array(
                "chunk"             => "-SN BR DDD",
                "weather_inten"     => "-",
                "weather_desc"      => "",
                "weather_phenom"    => array("SN","BR"),
                "remaining"         => "DDD",
            ),
            array(
                "chunk"             => "-RA BR DDD",
                "weather_inten"     => "-",
                "weather_desc"      => "",
                "weather_phenom"    => array("RA","BR"),
                "remaining"         => "DDD",
            ),
            array(
                "chunk"             => "NSW DDD",
                "weather_inten"     => "",
                "weather_desc"      => "",
                "weather_phenom"    => array("NSW"),
                "remaining"         => "DDD",
            ),
            array(
                "chunk"             => "-DZ FG DDD",
                "weather_inten"     => "-",
                "weather_desc"      => "",
                "weather_phenom"    => array("DZ", "FG"),
                "remaining"         => "DDD",
            ),
        );
    }
}
