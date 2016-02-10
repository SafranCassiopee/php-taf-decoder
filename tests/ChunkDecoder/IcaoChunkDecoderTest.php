<?php

namespace TafDecoder\Test\ChunkDecoder;

use TafDecoder\ChunkDecoder\IcaoChunkDecoder;

class IcaoChunkDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    protected function setup()
    {
        $this->decoder = new IcaoChunkDecoder();
    }

    /**
     * Test parsing of valid icao chunks
     * @param string $chunk
     * @param string $icao
     * @param string $remaining
     * @dataProvider getChunk
     */
    public function testParseIcaoChunk($chunk, $icao, $remaining)
    {
        $decoded = $this->decoder->parse($chunk);
        $this->assertEquals($icao, $decoded['result']['icao']);
        $this->assertEquals($remaining, $decoded['remaining_taf']);
    }

    /**
     * Test parsing of invalid icao chunks
     * @param string $chunk
     * @expectedException \TafDecoder\Exception\ChunkDecoderException
     * @dataProvider getInvalidChunk
     */
    public function testParseInvalidIcaoChunk($chunk)
    {
        $this->decoder->parse($chunk);
    }

    public function getChunk()
    {
        return array(
            array(
                "chunk"     => "LFPG AAA",
                "icao"      => "LFPG",
                "remaining" => "AAA",
            ),
            array(
                "chunk"     => "LFPO BBB",
                "icao"      => "LFPO",
                "remaining" => "BBB",
            ),
            array(
                "chunk"     => "LFIO CCC",
                "icao"      => "LFIO",
                "remaining" => "CCC",
            ),
        );
    }

    public function getInvalidChunk()
    {
        return array(
            array("chunk" => "LFA AAA"),
            array("chunk" => "L AAA"),
            array("chunk" => "LFP BBB"),
            array("chunk" => "LF8 CCC"),
        );
    }
}
