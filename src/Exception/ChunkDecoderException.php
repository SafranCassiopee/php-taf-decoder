<?php

namespace TafDecoder\Exception;

class ChunkDecoderException extends \Exception
{
    private $taf_chunk;

    private $remaining_taf;

    private $chunk_decoder_class;

    public function __construct($taf_chunk, $remaining_taf, $message, $chunk_decoder)
    {
        parent::__construct($message);
        $this->taf_chunk = trim($taf_chunk);
        $this->remaining_taf = $remaining_taf;
        $r_class = new \ReflectionClass($chunk_decoder);
        $this->chunk_decoder_class = $r_class->getShortName();
    }

    /**
     * Get the class of the chunk decoder which triggered the exception
     */
    public function getChunkDecoder()
    {
        return $this->chunk_decoder_class;
    }

    /**
     * Get the taf chunk that failed during decoding
     */
    public function getChunk()
    {
        return $this->taf_chunk;
    }

    /**
     * Get the remaining taf after the chunk decoder consumed it
     * In the cases where the exception is triggered because
      * chunk's regexp didn't match, it will be the same.
      * For other cases it won't, and having this information
      * will allow to continue decoding
     */
    public function getRemainingTaf()
    {
        return $this->remaining_taf;
    }
}
