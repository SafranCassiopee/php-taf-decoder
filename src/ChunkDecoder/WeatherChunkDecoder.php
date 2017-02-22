<?php

namespace TafDecoder\ChunkDecoder;

use TafDecoder\Entity\WeatherPhenomenon;

/**
 * Chunk decoder for weather section
 */
class WeatherChunkDecoder extends TafChunkDecoder implements TafChunkDecoderInterface
{
    public static $desc_dic = array(
        'TS','FZ','SH','BL','DR','MI','BC','PR',
    );
    public static $phenom_dic = array(
        'DZ', 'RA', 'SN', 'SG',
        'PL', 'DS', 'GR', 'GS',
        'UP', 'IC', 'FG', 'BR',
        'SA', 'DU', 'HZ', 'FU',
        'VA', 'PY', 'DU', 'PO',
        'SQ', 'FC', 'DS', 'SS',
        '//',
    );

    public function getRegexp()
    {
        $desc_regexp = implode(self::$desc_dic, '|');
        $phenom_regexp = implode(self::$phenom_dic, '|');
        $pw_regexp = "([-+]|VC)?($desc_regexp)?($phenom_regexp)?($phenom_regexp)?($phenom_regexp)?";

        return "#^($pw_regexp )?($pw_regexp )?($pw_regexp )?()?#";
    }

    public function parse($remaining_taf, $cavok = false)
    {
        $result = $this->consume($remaining_taf);
        $found = $result['found'];
        $new_remaining_taf = $result['remaining'];

        $result = array(
            'weatherPhenomenons' => array(),
        );

        for ($i = 1; $i <= 13; $i += 6) {
            if ($found[$i] != null && $found[$i + 3] != '//') {
                $weather = new WeatherPhenomenon();
                $weather->setIntensityProximity($found[$i + 1]);
                $weather->setDescriptor($found[$i + 2]);
                for ($k = 3; $k <= 5; ++$k) {
                    if ($found[$i + $k] != null) {
                        $weather->addPhenomenon($found[$i + $k]);
                    }
                }
                $result['weatherPhenomenons'][] = $weather;
            }
        }

        // return result + remaining taf
        return array(
            'result' => $result,
            'remaining_taf' => $new_remaining_taf,
        );
    }
}
