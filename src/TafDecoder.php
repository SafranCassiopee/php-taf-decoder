<?php

namespace TafDecoder;

use TafDecoder\Entity\DecodedTaf;
use TafDecoder\Exception\ChunkDecoderException;
use TafDecoder\ChunkDecoder\ReportTypeChunkDecoder;
use TafDecoder\ChunkDecoder\IcaoChunkDecoder;
use TafDecoder\ChunkDecoder\DatetimeChunkDecoder;
use TafDecoder\ChunkDecoder\ForecastPeriodChunkDecoder;

class TafDecoder
{
    private $decoder_chain;

    private $strict_parsing = false;

    protected $global_strict_parsing = false;

    public function __construct()
    {
        $this->decoder_chain = array(
            new ReportTypeChunkDecoder(),
            new IcaoChunkDecoder(),
            new DatetimeChunkDecoder(),
            new ForecastPeriodChunkDecoder(),
        );
    }

    /**
     * Set global parsing mode (strict/not strict) for the whole object
     */
    public function setStrictParsing($is_strict)
    {
        $this->global_strict_parsing = $is_strict;
    }

    /**
     * Decode a full taf string into a complete taf object
     * while using global strict option
     */
    public function parse($raw_taf)
    {
        return $this->parseWithMode($raw_taf, $this->global_strict_parsing);
    }

    /**
     * Decode a full taf string into a complete taf object
     * with strict option, meaning decoding will stop as soon as
     * a non-compliance is detected
     */
    public function parseStrict($raw_taf)
    {
        return $this->parseWithMode($raw_taf, true);
    }

    /**
     * Decode a full taf string into a complete taf object
     * with strict option disabled, meaning that decoding will
     * continue even if taf is not compliant
     */
    public function parseNotStrict($raw_taf)
    {
        return $this->parseWithMode($raw_taf, false);
    }

    /**
     * Decode a full taf string into a complete taf object
     */
    private function parseWithMode($raw_taf, $strict)
    {
        // prepare decoding inputs/outputs: (trim, remove linefeeds and returns, no more than one space)
        $clean_taf = trim($raw_taf);
        $clean_taf = preg_replace("#\n+#", ' ', $clean_taf);
        $clean_taf = preg_replace("#\r+#", ' ', $clean_taf);
        $clean_taf = preg_replace('#[ ]{2,}#', ' ', $clean_taf) . ' ';
        $clean_taf = strtoupper($clean_taf);
        $remaining_taf = $clean_taf;
        $decoded_taf = new DecodedTaf($clean_taf);
        $with_cavok = false;

        // call each decoder in the chain and use results to populate decoded taf
        foreach ($this->decoder_chain as $chunk_decoder) {
            try {
                // try to parse a chunk with current chunk decoder
                $decoded = $chunk_decoder->parse($remaining_taf, $with_cavok);

                // map obtained fields (if any) to the final decoded object
                $result = $decoded['result'];
                if ($result != null) {
                    foreach ($result as $key => $value) {
                        if ($value !== null) {
                            $setter_name = 'set'.ucfirst($key);
                            $decoded_taf->$setter_name($value);
                        }
                    }
                }

                // update remaining taf for next round
                $remaining_taf = $decoded['remaining_taf'];
            } catch (ChunkDecoderException $cde) {
                // log error in decoded taf and abort decoding if in strict mode
                $decoded_taf->addDecodingException($cde);
                // abort decoding if strict mode is activated, continue otherwise
                if ($strict) {
                    break;
                }
                // update remaining taf for next round
                $remaining_taf = $cde->getRemainingTaf();
            }

            // hook for report status decoder, abort if nil, but decoded taf is valid 
            if ($chunk_decoder instanceof ReportStatusChunkDecoder) {
                if ($decoded_taf->getStatus() == 'NIL') {
                    break;
                }
            }

            // hook for CAVOK decoder, keep CAVOK information in memory
            if ($chunk_decoder instanceof VisibilityChunkDecoder) {
                $with_cavok = $decoded_taf->getCavok();
            }
        }

        return $decoded_taf;
    }
}
