<?php

namespace TafDecoder\Test\ChunkDecoder;

use TafDecoder\ChunkDecoder\ReportTypeChunkDecoder;

class ReportTypeChunkDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    protected function setup()
    {
        $this->decoder = new ReportTypeChunkDecoder();
    }

    /**
     * Test parsing of valid report type chunks
     * @param $chunk
     * @param $type
     * @param $remaining
     * @dataProvider getChunk
     */
    public function testParse($chunk, $type, $remaining)
    {
        $decoded = $this->decoder->parse($chunk);
        $this->assertEquals($type, $decoded['result']['type']);
        $this->assertEquals($remaining, $decoded['remaining_taf']);
    }

    public function getChunk()
    {
        return array(
            array(
                "chunk"     => "TAF LFPG",
                "type"      => "TAF",
                "remaining" => "LFPG",
            ),
            array(
                "chunk"     => "TAF TAF LFPG",
                "type"      => "TAF",
                "remaining" => "LFPG",
            ),
            array(
                "chunk"     => "TAF AMD LFPO",
                "type"      => "TAF AMD",
                "remaining" => "LFPO",
            ),
            array(
                "chunk"     => "TA LFPG",
                "type"      => null,
                "remaining" => "TA LFPG",
            ),
            array(
                "chunk"     => "123 LFPO",
                "type"      => null,
                "remaining" => "123 LFPO",
            ),
        );
    }
}
