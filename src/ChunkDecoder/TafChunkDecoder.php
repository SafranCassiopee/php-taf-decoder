<?php

namespace TafDecoder\ChunkDecoder;

abstract class TafChunkDecoder
{
    /**
     * Extract the corresponding chunk from the remaining taf
     *
     * @param string $remaining_taf
     * @return array of matches if any or null if no match, + updated remaining taf
     */
    public function consume($remaining_taf)
    {
        $chunk_regexp = $this->getRegexp();

        // try to match chunk's regexp on remaining taf
        if (preg_match($chunk_regexp, $remaining_taf, $matches)) {
            $found = $matches;
        } else {
            $found = null;
        }

        // consume what has been previously found with the same regexp
        $new_remaining_taf = preg_replace($chunk_regexp, '', $remaining_taf, 1);

        return array(
            'found' => $found,
            'remaining' => $new_remaining_taf,
        );
    }
}
