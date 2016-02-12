<?php

namespace TafDecoder\ChunkDecoder;

use TafDecoder\Exception\ChunkDecoderException;
use TafDecoder\Entity\Visibility;
use TafDecoder\Entity\Value;

/**
 * Chunk decoder for visibility section
 */
class VisibilityChunkDecoder extends TafChunkDecoder implements TafChunkDecoderInterface
{
    public function getRegexp()
    {
        $cavok = "CAVOK";
        $visibility = "([0-9]{4})";
        $us_visibility = "M?(P)?([0-9]{0,2}) ?(([1357])/(2|4|8|16))?SM";
        $no_info = "////";

        return "#^($cavok|$visibility|$us_visibility|$no_info)( )#";
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
                'Bad format for visibility information',
                $this);
        }

        if ($found[1] ==  'CAVOK') { // ceiling and visibility OK
            $cavok = true;
            $visibility = null;
        } elseif ($found[1] == '////') { // information not available
            $cavok = false;
            $visibility = null;
        } else {
            $cavok = false;
            $visibility = new Visibility();
            if ($found[2] != null) { // icao visibility
                $visibility->setVisibility(Value::newIntValue($found[2], Value::METER));
            } else { // us visibility
                $main = intval($found[4]);
                $is_greater = $found[3] === 'P' ? true : false;
                $frac_top = intval($found[6]);
                $frac_bot = intval($found[7]);
                if ($frac_bot != 0) {
                    $vis_value = $main + $frac_top / $frac_bot;
                } else {
                    $vis_value = $main;
                }
                $visibility->setVisibility(Value::newValue($vis_value, Value::STATUTE_MILE));
                $visibility->setGreater($is_greater);
            }
        }

        $result = array(
            'cavok'         => $cavok,
            'visibility'    => $visibility,
        );

        // return result + remaining taf
        return array(
            'result' => $result,
            'remaining_taf' => $new_remaining_taf,
        );
    }
}
