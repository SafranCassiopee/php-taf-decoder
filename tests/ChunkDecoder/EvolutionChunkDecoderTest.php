<?php

namespace TafDecoder\Test\ChunkDecoder;

use TafDecoder\TafDecoder;
use TafDecoder\ChunkDecoder\EvolutionChunkDecoder;
use TafDecoder\Entity\DecodedTaf;
use TafDecoder\Entity\Evolution;

class EvolutionChunkDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $taf_decoder;
    private $evo_decoder;

    protected function setup()
    {
        $this->taf_decoder = new TafDecoder();
        $this->evo_decoder = new EvolutionChunkDecoder(true, false);
    }

    /**
     * Test parsing of evolution chunks
     *
     * @param $strict
     * @param $base
     * @param $evoChunk
     * @param $type
     * @param $probability
     * @param $fromD
     * @param $fromT
     * @param $toD
     * @param $toT
     * @param $elements
     * @dataProvider getChunk
     */
    public function testParse($strict, $base, $evoChunk, $type, $probability, $fromD, $fromT, $toD, $toT, $elements)
    {
        $decoded_taf = $this->getDecodedTaf($base);
        $this->evo_decoder->setStrict($strict);
        $this->evo_decoder->parse($evoChunk . ' END', $decoded_taf);

        /** @var array $windEvolutions */
        $windEvolutions = $decoded_taf->getSurfaceWind()->getEvolutions();
        if (count($windEvolutions) == 0) {
            return;
        }
        // global evolution attributes (no point testing them in each evolution as they never change)
        $this->assertEquals($type, $windEvolutions[0]->getType());
        $this->assertEquals($probability, $windEvolutions[0]->getProbability());
        $this->assertEquals($fromD, $windEvolutions[0]->getFromDay());
        $this->assertEquals($fromT, $windEvolutions[0]->getFromTime());
        $this->assertEquals($toD, $windEvolutions[0]->getToDay());
        $this->assertEquals($toT, $windEvolutions[0]->getToTime());
        if ($elements['emb_evolution_type']) {
            // it's embedded in the second evolution
            $emb_evolutions = $windEvolutions[1]->getEvolutions();
            $this->assertEquals($elements['emb_evolution_type'], $emb_evolutions[0]->getType());
        }
        // surfaceWind attributes
        $this->assertEquals($elements['wind_dir'], $windEvolutions[0]->getEntity()->getMeanDirection()->getValue());
        $this->assertEquals($elements['wind_speed'], $windEvolutions[0]->getEntity()->getMeanSpeed()->getValue());

        /** @var array $visiEvolutions */
        $visiEvolutions = $decoded_taf->getVisibility()->getEvolutions();
        $this->assertEquals($elements['cavok'], $visiEvolutions[0]->getCavok());
        if (!$visiEvolutions[0]->getCavok()) {
            // cavok and visibility are mutually exclusive
            $this->assertEquals($elements['visibility'], $visiEvolutions[0]->getEntity()->getVisibility()->getValue());
            $this->assertEquals($elements['greater'], $visiEvolutions[0]->getEntity()->getGreater());
        }

        if ($elements['weather_phenomena']) {
            /** @var array $weatherPhenomena */
            if(!is_null($decoded_taf->getWeatherPhenomenons())){
                $proxyWeatherPhenomena = $decoded_taf->getWeatherPhenomenons();
                $weatherPhenomena = $proxyWeatherPhenomena[0]->getEvolutions();
                $entity = $weatherPhenomena[0]->getEntity();
                $this->assertEquals(
                    $elements['weather_intensity'],
                    $entity[0]->getIntensityProximity()
                );
                $this->assertEquals($elements['weather_desc'], $entity[0]->getDescriptor());
                $this->assertEquals($elements['weather_phenomena'], $entity[0]->getPhenomena());
            }
        }

        /** @var array $clouds */
        $clouds = $decoded_taf->getClouds();
        if ($elements['clouds_base_height']) {
            $cloudsEvolutions = $clouds[1]->getEvolutions(
            ); // 1 instead of 0 because each evo is considered a new layer
            $this->assertEquals($type, $cloudsEvolutions[0]->getType());
            /** @var Evolution $cloudsEvolution */
            $cloudsEvolution = $cloudsEvolutions[0];
            $cloudsLayers    = $cloudsEvolution->getEntity();
            $this->assertEquals($elements['clouds_amount'], $cloudsLayers[0]->getAmount());
            $this->assertEquals($elements['clouds_base_height'], $cloudsLayers[0]->getBaseHeight()->getValue());
        }

        if ($elements['min_temp_val']) {
            /** @var array $minTemps */
            $minTemps = $decoded_taf->getMinTemperature()->getEvolutions();
            /** @var array $maxTemps */
            $maxTemps = $decoded_taf->getMaxTemperature()->getEvolutions();
            $this->assertEquals($elements['min_temp_val'], $minTemps[0]->getEntity()->getTemperature()->getValue());
            $this->assertEquals($elements['max_temp_val'], $maxTemps[0]->getEntity()->getTemperature()->getValue());
        }
    }

    /**
     * All the cases required to have 100% code coverage
     *
     * @return array
     */
    public function getChunk()
    {
        return array(
            array(
                // common cases
                "strict"                => true,
                "base"                  => 'TAF KJFK 080500Z 0806/0910 23010KT 6 1/4SM BKN020',
                "evoChunk"              => 'BECMG 0807/0810 23024KT P6SM +SHRA BKN025 TX08/0910Z TNM01/0904',
                "type"                  => 'BECMG',
                "probability"           => '',
                "from_day"              => 8,
                "from_time"             => '07:00 UTC',
                "to_day"                => 8,
                "to_time"               => '10:00 UTC',
                "elements"              => array(
                    "wind_dir"              => 230,
                    "wind_speed"            => 24,
                    "visibility"            => 6,
                    "cavok"                 => false,
                    "greater"               => true,
                    "weather_phenomena"     => array('RA'),
                    "weather_intensity"     => '+',
                    "weather_desc"          => 'SH',
                    "clouds_amount"         => 'BKN',
                    "clouds_base_height"    => 2500,
                    "min_temp_val"          => -1,
                    "max_temp_val"          => 8,
                    "emb_evolution_type"    => null,
                ),
            ),
            array(
                // line starting with PROB
                "strict"                => true,
                "base"                  => 'TAF KJFK 080500Z 0806/0910 23010KT 6 1/4SM BKN020',
                "chunk"                 => "PROB40 TEMPO 0807/0810 23024KT CAVOK BKN025",
                "type"                  => 'TEMPO',
                "probability"           => 'PROB40',
                "from_day"              => 8,
                "from_time"             => '07:00 UTC',
                "to_day"                => 8,
                "to_time"               => '10:00 UTC',
                "elements"              => array(
                    "wind_dir"              => 230,
                    "wind_speed"            => 24,
                    "visibility"            => null,
                    "cavok"                 => true,
                    "greater"               => false,
                    "weather_phenomena"     => null,
                    "weather_intensity"     => '',
                    "weather_desc"          => '',
                    "clouds_amount"         => 'BKN',
                    "clouds_base_height"    => 2500,
                    "min_temp_val"          => null,
                    "max_temp_val"          => null,
                    "emb_evolution_type"    => null,
                ),
            ),
            array(
                // embedded evolutions
                "strict"                => true,
                "base"                  => 'TAF KJFK 080500Z 0806/0910 23010KT',
                "chunk"                 => "BECMG 0807/0810 23024KT CAVOK -RA PROB40 TEMPO 0808/0809 18020KT",
                "type"                  => 'BECMG',
                "probability"           => '',
                "from_day"              => 8,
                "from_time"             => '07:00 UTC',
                "to_day"                => 8,
                "to_time"               => '10:00 UTC',
                "elements"              => array(
                    "wind_dir"              => 230,
                    "wind_speed"            => 24,
                    "visibility"            => null,
                    "cavok"                 => true,
                    "greater"               => false,
                    "weather_phenomena"     => null,
                    "weather_intensity"     => '',
                    "weather_desc"          => '',
                    "clouds_amount"         => '',
                    "clouds_base_height"    => null,
                    "min_temp_val"          => null,
                    "max_temp_val"          => null,
                    "emb_evolution_type"    => 'TEMPO',
                ),
            ),
            array(
                // surfaceWind and visibility entities
                "strict"                => false,
                "base"                  => 'TAF BAH KJFK 080500Z 0806/0910 TX10/0807Z TN05/0904Z',
                "chunk"                 => 'BECMG 0810/0812 27010KT 4000 -RA BKN025',
                "type"                  => 'BECMG',
                "probability"           => '',
                "from_day"              => 8,
                "from_time"             => '10:00 UTC',
                "to_day"                => 8,
                "to_time"               => '12:00 UTC',
                "elements"              => array(
                    "wind_dir"              => 270,
                    "wind_speed"            => 10,
                    "visibility"            => 4000,
                    "cavok"                 => false,
                    "greater"               => false,
                    "weather_phenomena"     => null,
                    "weather_intensity"     => '',
                    "weather_desc"          => '',
                    "clouds_amount"         => '',
                    "clouds_base_height"    => null,
                    "min_temp_val"          => null,
                    "max_temp_val"          => null,
                    "emb_evolution_type"    => null,
                ),
            ),
            array(
                // drop a chunk that doesn't match with any decoder
                "strict"                => false,
                "base"                  => 'TAF KJFK 081009Z 0810/0912 03017G28KT 9000 BKN020',
                "chunk"                 => 'FM081100 03018G27KT 6000 -SN OVC015 PROB40 0811/0912 AAA',
                "type"                  => 'FM',
                "probability"           => '',
                "from_day"              => 8,
                "from_time"             => '11:00 UTC',
                "to_day"                => null,
                "to_time"               => '',
                "elements"              => array(
                    "wind_dir"              => 30,
                    "wind_speed"            => 18,
                    "visibility"            => 6000,
                    "cavok"                 => false,
                    "greater"               => false,
                    "weather_phenomena"     => array('SN'),
                    "weather_intensity"     => '-',
                    "weather_desc"          => '',
                    "clouds_amount"         => 'OVC',
                    "clouds_base_height"    => 1500,
                    "min_temp_val"          => null,
                    "max_temp_val"          => null,
                    "emb_evolution_type"    => null,
                ),
            ),
            array(
                // trigger a ChunkDecoderException
                "strict"                => true,
                "base"                  => 'TAF KJFK 081009Z 0810/0912 03017G28KT 9000 BKN020',
                "chunk"                 => 'FM081200 03018G27KT 7000 -SN OVC015 PROB40 0810/0910 BK025',
                "type"                  => 'FM',
                "probability"           => '',
                "from_day"              => 8,
                "from_time"             => '12:00 UTC',
                "to_day"                => null,
                "to_time"               => '',
                "elements"              => array(
                    "wind_dir"              => 30,
                    "wind_speed"            => 18,
                    "visibility"            => 7000,
                    "cavok"                 => false,
                    "greater"               => false,
                    "weather_phenomena"     => array('SN'),
                    "weather_intensity"     => '-',
                    "weather_desc"          => '',
                    "clouds_amount"         => 'OVC',
                    "clouds_base_height"    => 1500,
                    "min_temp_val"          => null,
                    "max_temp_val"          => null,
                    "emb_evolution_type"    => null,
                ),
            ),
        );
    }

    /**
     * Initialize and return a decoded_taf
     *
     * @param $raw_taf
     * @return DecodedTaf
     */
    public function getDecodedTaf($raw_taf)
    {
        $decoded_taf = $this->taf_decoder->parseStrict($raw_taf);

        return $decoded_taf;
    }
}
