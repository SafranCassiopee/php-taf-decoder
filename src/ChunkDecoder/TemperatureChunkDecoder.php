<?php

namespace TafDecoder\ChunkDecoder;

use TafDecoder\Entity\Value;
use TafDecoder\Entity\Temperature;
use TafDecoder\Exception\ChunkDecoderException;

/**
 * Chunk decoder for air and dew point temperature
 */
class TemperatureChunkDecoder extends TafChunkDecoder implements TafChunkDecoderInterface
{
    public function getRegexp()
    {
        $temp_regex = '(TX|TN){1}(M?[0-9]{2})/([0-9]{2})([0-9]{2})Z';

        return "#^$temp_regex $temp_regex?( )?#";
    }

    public function parse($remaining_taf, $cavok = false)
    {
        $result = $this->consume($remaining_taf);
        $found = $result['found'];
        $new_remaining_taf = $result['remaining'];

		// temperatures are so often missing from forecasts we consider them as optional
		$max_temp = null;
		$min_temp = null;
		
		if ($found != null && trim($found[2]) != null && trim($found[6]) != null) {
			// retrieve found params
			$max_temp = new Temperature();
			$max_temp->setType($found[1]);
			$max_temp->setTemperature(Value::newIntValue($found[2], Value::DEGREE_CELSIUS));
			$max_temp->setDay(intval($found[3]));
			$max_temp->setHour(intval($found[4]));

			$min_temp = null;
			if (trim($found[5]) != null) {
				$min_temp = new Temperature();
				$min_temp->setType($found[5]);
				$min_temp->setTemperature(Value::newIntValue($found[6], Value::DEGREE_CELSIUS));
				$min_temp->setDay(intval($found[7]));
				$min_temp->setHour(intval($found[8]));
			}

			// handle the case where min and max temperatures are inconsistent
			if ($min_temp->getTemperature()->getValue() > $max_temp->getTemperature()->getValue()) {
				throw new ChunkDecoderException($remaining_taf,
					$new_remaining_taf,
					'Inconsistent values for temperature information',
					$this);
			}
		}
		
		$result = array(
			'maxTemperature' => $max_temp,
			'minTemperature' => $min_temp,
		);

        // return result + remaining taf
        return array(
            'result' => $result,
            'remaining_taf' => $new_remaining_taf,
        );
    }
}
