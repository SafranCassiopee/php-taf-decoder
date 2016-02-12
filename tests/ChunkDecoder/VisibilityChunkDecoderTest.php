<?php

namespace TafDecoder\Test\ChunkDecoder;

use TafDecoder\ChunkDecoder\VisibilityChunkDecoder;
use TafDecoder\Entity\DecodedTaf;
use TafDecoder\Entity\Visibility;

class VisibilityChunkDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    protected function setup()
    {
        $this->decoder = new VisibilityChunkDecoder();
    }

    /**
     * Test parsing of valid visibility chunks
     * @param $chunk
     * @param $cavok
     * @param $is_greater
     * @param $visibility
     * @param $visibility_unit
     * @param $remaining
     * @dataProvider getChunk
     */
    public function testParse($chunk, $cavok, $is_greater, $visibility, $visibility_unit, $remaining)
    {
        $decoded = $this->decoder->parse($chunk);
        if ($cavok) {
            $this->assertTrue($decoded['result']['cavok']);
        } elseif ($visibility == null) {
            $this->assertNull($decoded['result']['visibility']);
            $this->assertFalse($decoded['result']['cavok']);
        } else {
            /** @var Visibility $v */
            $v = $decoded['result']['visibility'];
            $this->assertEquals($visibility, $v->getVisibility()->getValue());
            $this->assertEquals($visibility_unit, $v->getVisibility()->getUnit());
            if ($is_greater) {
                $this->assertTrue($v->getGreater());
            }
        }
        $this->assertEquals($remaining, $decoded['remaining_taf']);
    }

    /**
     * Test parsing of invalid visibility chunks
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
                "chunk"             => "0200 AAA",
                "cavok"             => false,
                "is_greater"        => false,
                "visibility"        => 200,
                "visibility_unit"   => 'm',
                "remaining"         => "AAA",
            ),
            array(
                "chunk"             => "CAVOK BBB",
                "cavok"             => true,
                "is_greater"        => false,
                "visibility"        => null,
                "visibility_unit"   => 'm',
                "remaining"         => "BBB",
            ),
            array(
                "chunk"             => "8000 CCC",
                "cavok"             => false,
                "is_greater"        => false,
                "visibility"        => 8000,
                "visibility_unit"   => 'm',
                "remaining"         => "CCC",
            ),
            array(
                "chunk"             => "P6SM DDD",
                "cavok"             => false,
                "is_greater"        => true,
                "visibility"        => 6,
                "visibility_unit"   => 'SM',
                "remaining"         => "DDD",
            ),
            array(
                "chunk"             => "6 1/4SM EEE",
                "cavok"             => false,
                "is_greater"        => false,
                "visibility"        => 6.25,
                "visibility_unit"   => 'SM',
                "remaining"         => "EEE",
            ),
            array(
                "chunk"             => "P6 1/4SM FFF",
                "cavok"             => false,
                "is_greater"        => true,
                "visibility"        => 6.25,
                "visibility_unit"   => 'SM',
                "remaining"         => "FFF",
            ),
            array(
                "chunk"             => "//// HHH",
                "cavok"             => false,
                "is_greater"        => false,
                "visibility"        => null,
                "visibility_unit"   => null,
                "remaining"         => "HHH",
            ),
        );
    }

    public function getInvalidChunk()
    {
        return array(
            array("chunk" => "CAVO EEE"),
            array("chunk" => "CAVOKO EEE"),
            array("chunk" => "123 EEE"),
            array("chunk" => "12335 EEE"),
            array("chunk" => "SS EEE"),
        );
    }
}
