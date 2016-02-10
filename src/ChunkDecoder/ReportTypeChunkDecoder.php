<?php

namespace TafDecoder\ChunkDecoder;

/**
 * Chunk decoder for report type section
 */
class ReportTypeChunkDecoder extends TafChunkDecoder implements TafChunkDecoderInterface
{
    public function getRegexp()
    {
        return '#^((TAF)( TAF)*( AMD){0,1}) #';
    }

    public function parse($remaining_taf, $cavok = false)
    {
        $result = $this->consume($remaining_taf);
        $found = $result['found'];
        $new_remaining_taf = $result['remaining'];

        // handle the case where nothing has been found
        if ($found == null) {
            $result = null;
        } else {
            // retrieve found params
            $result = array(
                'type' => str_replace('TAF TAF', 'TAF', $found[1]), // 'TAF' sometimes happens to be duplicated
            );
        }

        // return result + remaining taf
        return array(
            'result' => $result,
            'remaining_taf' => $new_remaining_taf,
        );
    }
}
