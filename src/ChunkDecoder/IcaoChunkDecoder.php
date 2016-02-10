<?php

namespace TafDecoder\ChunkDecoder;

use TafDecoder\Exception\ChunkDecoderException;

/**
 * Chunk decoder for icao section
 */
class IcaoChunkDecoder extends TafChunkDecoder implements TafChunkDecoderInterface
{
    public function getRegexp()
    {
        return '#^([A-Z0-9]{4}) #';
    }

    public function parse($remaining_taf, $cavok = false)
    {
        $result = $this->consume($remaining_taf);
        $found = $result['found'];
        $new_remaining_taf = $result['remaining'];

        // throw error if nothing has been found
        if ($found == null) {
            throw new ChunkDecoderException($remaining_taf,
                $new_remaining_taf,
                'Station ICAO code not found (4 char expected)',
                $this);
        }

        // retrieve found params
        $result = array(
            'icao' => $found[1],
        );

        // return result + remaining taf
        return array(
            'result' => $result,
            'remaining_taf' => $new_remaining_taf,
        );
    }
}
