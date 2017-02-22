<?php

namespace TafDecoder\ChunkDecoder;

use TafDecoder\Entity\SurfaceWind;
use TafDecoder\Entity\Visibility;
use TafDecoder\Exception\ChunkDecoderException;
use TafDecoder\Entity\DecodedTaf;
use TafDecoder\Entity\Evolution;
use TafDecoder\Entity\CloudLayer;
use TafDecoder\Entity\WeatherPhenomenon;
use TafDecoder\Entity\Temperature;

/**
 * Chunk decoder for weather evolutions
 */
class EvolutionChunkDecoder extends TafChunkDecoder implements TafChunkDecoderInterface
{
    private $decoder_chain;

    private $strict;

    private $with_cavok;

    private $remaining;


    public function __construct($strict, $with_cavok)
    {
        $this->strict = $strict;
        $this->with_cavok = $with_cavok;
        $this->decoder_chain = array(
            new SurfaceWindChunkDecoder(),
            new VisibilityChunkDecoder(),
            new WeatherChunkDecoder(),
            new CloudChunkDecoder(),
            new TemperatureChunkDecoder(),
        );
    }


    public function setStrict($strict)
    {
        $this->strict = $strict;
    }

    public function getRemaining()
    {
        return $this->remaining;
    }

    public function getRegexp()
    {
        $type = '(BECMG\s+|TEMPO\s+|FM|PROB[034]{2}\s+){1}';
        $period = '([0-9]{4}/[0-9]{4}\s+|[0-9]{6}\s+){1}';
        $rest = '(.*)';

        return "#$type$period$rest#";
    }

    /**
     * @param string $remaining_taf
     * @param DecodedTaf $decoded_taf
     * @return string
     */
    public function parse($remaining_taf, $decoded_taf)
    {
        $result = $this->consume($remaining_taf);
        $found = $result['found'];
        $evo_type = trim($found[1]);
        $evo_period = trim($found[2]);
        $remaining = $found[3];

        if ($found == null) {
            // the first chunk didn't match anything, so we remove it to avoid an infinite loop
            $this->remaining = preg_replace('#(\S+\s+)(.*)#', '', $remaining_taf);

            return;
        }

        $evolution = new Evolution();
        $evolution->setType($evo_type);
        if (strpos($result['remaining'], 'PROB') !== false) {
            // if the line started with PROBnn it won't have been consumed and we'll find it in $remaining
            $evolution->setProbability(trim($result['remaining']));
        }

        // period
        if ($evo_type == 'BECMG' || $evo_type == 'TEMPO') {
            $periodArr = explode('/', $evo_period);
            $evolution->setFromDay(intval(mb_substr($periodArr[0], 0, 2)));
            $evolution->setFromTime(mb_substr($periodArr[0], 2, 2).':00 UTC');
            $evolution->setToDay(intval(mb_substr($periodArr[1], 0, 2)));
            $evolution->setToTime(mb_substr($periodArr[1], 2, 2).':00 UTC');
        } else {
            $evolution->setFromDay(intval(mb_substr($evo_period, 0, 2)));
            $evolution->setFromTime(mb_substr($evo_period, 2, 2).':'.mb_substr($evo_period, 4, 2).' UTC');
        }

        // rest
        $remaining = $this->parseEntitiesChunk($evolution, $remaining, $decoded_taf);

        $this->remaining = $remaining;
    }

    /**
     * Extract the weather elements (surface winds, visibility, etc) between 2 evolution tags (BECMG, TEMPO or FM)
     *
     * @param Evolution $evolution
     * @param string $chunk
     * @param DecodedTaf $decoded_taf
     * @throws ChunkDecoderException
     * @return string
     */
    private function parseEntitiesChunk($evolution, $chunk, $decoded_taf)
    {
        // For each value we detect, we'll clone the $evolution object, complete the clone,
        // and add it to the corresponding entity of the decoded taf

        $remaining_evo = $chunk;
        $tries = 0;

        // call each decoder in the chain and use results to populate the decoded taf
        foreach ($this->decoder_chain as $chunk_decoder) {
            try {
                // we check for probability in each loop, as it can be anywhere
                $remaining_evo = $this->probabilityChunkDecoder($evolution, $remaining_evo, $decoded_taf);

                // reset cavok
                $this->with_cavok = false;

                // try to parse the chunk with the current chunk decoder
                $decoded = $chunk_decoder->parse($remaining_evo, $this->with_cavok);

                // map the obtained fields (if any) to a original entity in the decoded_taf
                $result = $decoded['result'];
                $entity_name = current(array_keys($result));
                if ($entity_name == 'cavok') {
                    if ($result[$entity_name]) {
                        $this->with_cavok = true;
                    }
                    $entity_name = 'visibility';
                }
                $entity = $result[$entity_name];
                if ($entity == null && $entity_name != 'visibility') {
                    // visibility will be null if cavok is true but we still want to add the evolution
                    throw new ChunkDecoderException(
                        $chunk,
                        $remaining_evo,
                        'Bad format for weather evolution',
                        $this
                    );
                }
                if ($entity_name == 'maxTemperature') {
                    $this->addEvolution($decoded_taf, $evolution, $result, 'maxTemperature');
                    $this->addEvolution($decoded_taf, $evolution, $result, 'minTemperature');
                } else {
                    $this->addEvolution($decoded_taf, $evolution, $result, $entity_name);
                }

                // update remaining evo for the next round
                $remaining_evo = $decoded['remaining_taf'];

            } catch (ChunkDecoderException $e) {
                if (++$tries == count($this->decoder_chain)) {
                    if ($this->strict) {
                        throw new ChunkDecoderException(
                            $chunk,
                            $remaining_evo,
                            'Bad format for evolution information',
                            $this
                        );
                    } else {
                        // we tried all the chunk decoders on the first chunk and none of them got a match,
                        // so we drop it
                        $remaining_evo = preg_replace('#(\S+\s+)#', '', $remaining_evo);
                    }
                }
            }
        }

        return $remaining_evo;
    }

    /**
     * Look recursively for probability (PROBnn) attributes and embed a new evolution object one level deeper for each
     *
     * @param Evolution $evolution
     * @param string $chunk
     * @param DecodedTaf $decoded_taf
     * @return string
     */
    private function probabilityChunkDecoder($evolution, $chunk, $decoded_taf)
    {
        $regexp = '#^(PROB[034]{2}\s+){1}(TEMPO\s+){0,1}([0-9]{4}/[0-9]{4}){0,1}(.*)#';

        if (preg_match($regexp, $chunk, $matches)) {
            $found = $matches;
        } else {
            return $chunk;
        }

        $prob = trim($found[1]);
        $type = trim($found[2]);
        $period = trim($found[3]);
        $remaining = trim($found[4]);

        if (strpos($prob, 'PROB') !== false) {
            $evolution->setProbability($prob);
            $embeddedEvolution = new Evolution();
            if ($type) {
                $embeddedEvolution->setType($type);
            } else {
                $embeddedEvolution->setType('probability');
            }
            $periodArr = explode('/', $period);
            $embeddedEvolution->setFromDay(intval(mb_substr($periodArr[0], 0, 2)));
            $embeddedEvolution->setFromTime(mb_substr($periodArr[0], 2, 2).':00 UTC');
            $embeddedEvolution->setToDay(intval(mb_substr($periodArr[1], 0, 2)));
            $embeddedEvolution->setToTime(mb_substr($periodArr[1], 2, 2).':00 UTC');

            $evolution->addEvolution($embeddedEvolution);
            // recurse on the remaining chunk to extract the weather elements it contains
            $chunk = $this->parseEntitiesChunk($evolution, $remaining, $decoded_taf);
        }

        return $chunk;
    }

    /**
     * Add the evolution to the decodedTaf's entity
     *
     * @param DecodedTaf $decoded_taf
     * @param Evolution $evolution
     * @param array $result
     * @param string $entity_name
     */
    private function addEvolution($decoded_taf, $evolution, $result, $entity_name)
    {
        // clone the evolution entity
        /** @var Evolution $newEvolution */
        $new_evolution = clone($evolution);

        // add the new entity to it
        $new_evolution->setEntity($result[$entity_name]);

        // possibly add cavok to it
        if ($entity_name == 'visibility' && $this->with_cavok == true) {
            $new_evolution->setCavok(true);
        }

        // get the original entity from the decoded taf or a new one decoded taf doesn't contain it yet
        $getter_name = 'get'.ucfirst($entity_name);
        $setter_name = 'set'.ucfirst($entity_name);
        $decoded_entity = $decoded_taf->$getter_name();
        if ($decoded_entity == null || $entity_name == 'clouds' || $entity_name == 'weatherPhenomenons') {
            // that entity is not in the decoded_taf yet, or it's a cloud layer which is a special case
            $decoded_entity = $this->instantiateEntity($entity_name);
        }

        // add the new evolution to that entity
        $decoded_entity->addEvolution($new_evolution);

        // update the decoded taf's entity or add the new one to it
        if ($entity_name == 'clouds') {
            $decoded_taf->addCloud($decoded_entity);
        } elseif ($entity_name == 'weatherPhenomenons') {
            $decoded_taf->addWeatherPhenomenon($decoded_entity);
        } else {
            $decoded_taf->$setter_name($decoded_entity);
        }
    }

    /**
     * Instantiate a new entity when an evolution needs one that's not present in decodedTaf already
     *
     * @param $entity_name
     * @return mixed
     */
    private function instantiateEntity($entity_name)
    {
        $entity = null;

        if ($entity_name == 'weatherPhenomenons') {
            $entity = new WeatherPhenomenon();
        } else if ($entity_name == 'maxTemperature') {
            $entity =  new Temperature();
        } else if ($entity_name == 'minTemperature') {
            $entity = new Temperature();
        } else if ($entity_name == 'clouds') {
            $entity = new CloudLayer();
        } else if ($entity_name == 'surfaceWind') {
            $entity = new SurfaceWind();
        } else if ($entity_name = 'visibility') {
            $entity = new Visibility();
        }

        return $entity;
    }
}
