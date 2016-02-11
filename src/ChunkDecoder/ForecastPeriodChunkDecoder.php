<?php

namespace TafDecoder\ChunkDecoder;

use TafDecoder\Exception\ChunkDecoderException;
use TafDecoder\Entity\ForecastPeriod;

/**
 * Chunk decoder for forecast period section
 */
class ForecastPeriodChunkDecoder extends TafChunkDecoder implements TafChunkDecoderInterface
{
    public function getRegexp()
    {
        return '#^([0-9]{2})([0-9]{2})/([0-9]{2})([0-9]{2}) #';
    }

    public function parse($remaining_taf, $cavok = false)
    {
        $result = $this->consume($remaining_taf);
        $found = $result['found'];
        $new_remaining_taf = $result['remaining'];

        // handle the case where nothing has been found
        if ($found == null) {
            throw new ChunkDecoderException($remaining_taf,
                $new_remaining_taf,
                'Missing or badly formatted forecast period information ("ddhh/ddhh" expected)',
                $this);
        } else {
            // retrieve found params and check them
            /** @var ForecastPeriod $fp */
            $fp = new ForecastPeriod();
            $fp->setFromDay(intval($found[1]));
            $fp->setFromHour(intval($found[2]));
            $fp->setToDay(intval($found[3]));
            $fp->setToHour(intval($found[4]));
            if (!$fp->isValid()) {
                throw new ChunkDecoderException($remaining_taf,
                    $new_remaining_taf,
                    'Invalid values for the forecast period',
                    $this);
            }
        }

        $result = array(
            'forecastPeriod' => $fp,
        );

        // return result + remaining taf
        return array(
            'result' => $result,
            'remaining_taf' => $new_remaining_taf,
        );
    }
}