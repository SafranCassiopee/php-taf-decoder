<?php

namespace TafDecoder\Test\ChunkDecoder;

use TafDecoder\ChunkDecoder\EvolutionChunkDecoder;
use TafDecoder\Entity\DecodedTaf;
use TafDecoder\Entity\ForecastPeriod;
use TafDecoder\Entity\Evolution;
use TafDecoder\Entity\SurfaceWind;
use TafDecoder\Entity\Visibility;
use TafDecoder\Entity\CloudLayer;
use TafDecoder\Entity\Value;

class EvolutionChunkDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    protected function setup()
    {
        $this->decoder = new EvolutionChunkDecoder(true, false);
    }

    /**
     * Test parsing of valid evolution chunks
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
     * @param $remaining
     * @dataProvider getChunk
     */
    public function testParse($strict, $base, $evoChunk, $type, $probability, $fromD, $fromT, $toD, $toT, $elements, $remaining)
    {
        $decoded_taf = $this->getDecodedTaf($base);
        $this->decoder->setStrict($strict);
        $this->decoder->parse($evoChunk . ' END', $decoded_taf);

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
            $weatherPhenomena = $decoded_taf->getWeatherPhenomenon()->getEvolutions();
            $this->assertEquals(
                $elements['weather_intensity'],
                $weatherPhenomena[0]->getEntity()->getIntensityProximity()
            );
            $this->assertEquals($elements['weather_desc'], $weatherPhenomena[0]->getEntity()->getDescriptor());
            $this->assertEquals($elements['weather_phenomena'], $weatherPhenomena[0]->getEntity()->getPhenomena());
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
                "remaining"             => '',
            ),
            array(
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
                "remaining"             => '',
            ),
            array(
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
                "remaining"             => '',
            ),
            array(
                "strict"                => true,
                "base"                  => 'TAF KJFK 080500Z 0806/0910 32010KT BKN025',
                "chunk"                 => 'BECMG 0807/0810 BKN018 PROB40 0808/0809 18020KT 9000',
                "type"                  => 'BECMG',
                "probability"           => 'PROB40',
                "from_day"              => 8,
                "from_time"             => '07:00 UTC',
                "to_day"                => 8,
                "to_time"               => '10:00 UTC',
                "elements"              => array(
                    "wind_dir"              => 180,
                    "wind_speed"            => 20,
                    "visibility"            => 9000,
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
                "remaining"             => '',
            ),
            array(
                "strict"                => true,
                "base"                  => 'TAF KJFK 081009Z 0810/0912 03017G28KT P6SM BKN020 OVC080',
                "chunk"                 => 'FM081200 03018G27KT 5SM -SN OVC015 PROB40 0810/0910 AAA',
                "type"                  => 'FM',
                "probability"           => '',
                "from_day"              => 8,
                "from_time"             => '12:00 UTC',
                "to_day"                => null,
                "to_time"               => '',
                "elements"              => array(
                    "wind_dir"              => 30,
                    "wind_speed"            => 18,
                    "visibility"            => 5,
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
                "remaining"             => '',
            ),
            array(
                "strict"                => false,
                "base"                  => 'TAF KJFK 081009Z 0810/0912 03017G28KT P6SM BKN020 OVC080',
                "chunk"                 => 'FM081200 03018G27KT 5SM -SN OVC015 PROB40 0810/0910 AAA',
                "type"                  => 'FM',
                "probability"           => '',
                "from_day"              => 8,
                "from_time"             => '12:00 UTC',
                "to_day"                => null,
                "to_time"               => '',
                "elements"              => array(
                    "wind_dir"              => 30,
                    "wind_speed"            => 18,
                    "visibility"            => 5,
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
                "remaining"             => '',
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
        /** @var ForecastPeriod $fp */
        $fp = new ForecastPeriod();
        $fp->setFromDay(8);
        $fp->setFromHour(6);
        $fp->setToDay(9);
        $fp->setToHour(10);

        /** @var SurfaceWind $sv */
        $sw = new SurfaceWind();
        $sw->setMeanDirection(new Value(230, 'deg'));
        $sw->setVariableDirection(false);
        $sw->setMeanSpeed(new Value(10, 'kt'));

        /** @var Visibility $visi */
        $visi = new Visibility();
        $visi->setVisibility(new Value(9000, 'm'));

        /** @var CloudLayer $cl */
        $cl = new CloudLayer();
        $cl->setAmount('BKN');
        $cl->setBaseHeight(new Value(2000, 'ft'));

        /** @var DecodedTaf $decoded_taf */
        $decoded_taf = new DecodedTaf($raw_taf);
        $decoded_taf->setType('TAF');
        $decoded_taf->setIcao('LFPO');
        $decoded_taf->setDay(8);
        $decoded_taf->setTime('05:00 UTC');
        $decoded_taf->setForecastPeriod($fp);
        $decoded_taf->setSurfaceWind($sw);
        $decoded_taf->setVisibility($visi);
        $decoded_taf->addCloud($cl);

        return $decoded_taf;
    }
}
