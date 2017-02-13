<?php

namespace TafDecoder\Test\ChunkDecoder;

use TafDecoder\ChunkDecoder\WeatherChunkDecoder;
use TafDecoder\Entity\WeatherPhenomenon;

class WeatherChunkDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    /**
     * Test parsing of valid weather chunks
     * @param $chunk
     * @param $weather_phenoms
     * @param $remaining
     * @dataProvider getChunk
     */
    public function testParse($chunk, $weather_phenoms, $remaining)
    {
        $decoded = $this->decoder->parse($chunk);
        /** @var WeatherPhenomenon $weather */
        $weather = $decoded['result']['weatherPhenomenon'];
        for($i = 0; $i < count($weather); $i++){
            $this->assertEquals($weather_phenoms[$i]['weather_intens'], $weather[$i]->getIntensityProximity());
            $this->assertEquals($weather_phenoms[$i]['weather_desc'], $weather[$i]->getDescriptor());
            $this->assertEquals($weather_phenoms[$i]['weather_phenom'], $weather[$i]->getPhenomena());
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
                "chunk" => "-FZDZ FG DDD",
                array(
                    array(
                        "weather_intens" => "-",
                        "weather_desc" => "FZ",
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
        );
    }

    protected function setup()
    {
        $this->decoder = new WeatherChunkDecoder();
    }
}
