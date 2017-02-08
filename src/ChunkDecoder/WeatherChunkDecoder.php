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
        '//', "NSW"
    );

    public function getRegexp()
    {
        $desc_regexp = implode(self::$desc_dic, '|');
        $phenom_regexp = implode(self::$phenom_dic, '|');
        $pw_regexp = "([-+]|VC)?($desc_regexp)?($phenom_regexp)?(?:\s)?($desc_regexp)?($phenom_regexp)?";
        
        return "#^($pw_regexp )?()?#";
    }

    public function parse($remaining_taf, $cavok = false)
    {
        $result = $this->consume($remaining_taf);
        $found = $result['found'];
        $new_remaining_taf = $result['remaining'];

        $weatherPhenom = null;
        if (trim($found[1]) != null && $found[4] != '//') {
            $weatherPhenom = new WeatherPhenomenon();
            $weatherPhenom->setChunk(trim($found[1]));
            $weatherPhenom->setIntensityProximity($found[2]);
            $weatherPhenom->setDescriptor($found[3]);
            for ($k = 3; $k <= 5; $k++) {
                if ($found[1+$k] != null) {
                    $weatherPhenom->addPhenomenon($found[1+$k]);
                }
            }
        }
        $result = array(
            'weatherPhenomenon' => $weatherPhenom,
        );

        // return result + remaining taf
        return array(
            'result' => $result,
            'remaining_taf' => $new_remaining_taf,
        );
    }
}
