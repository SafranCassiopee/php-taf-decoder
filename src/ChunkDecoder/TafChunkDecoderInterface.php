<?php

namespace TafDecoder\ChunkDecoder;

interface TafChunkDecoderInterface
{
    /**
     * Get the regular expression that will be used by chunk decoder
     * Each chunk decoder must declare its own
     */
    public function getRegexp();

    /**
     * Decode the chunk targetted by the chunk decoder and returns the
     * decoded information and the remaining taf without this chunk
     */
    public function parse($remaining_taf, $with_cavok);
}
