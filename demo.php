<?php

// This is a live demonstration on how to use the library

require_once dirname(__FILE__) . '/vendor/autoload.php';
// or if you don't use autoloading:
// require_once dirname(__FILE__) . '/src/TafDecoder.inc.php';

use TafDecoder\TafDecoder;
use TafDecoder\ChunkDecoder\ReportTypeChunkDecoder;

$raw_taf = '2013/11/03 18:54\nTAF TAF LIRU 032244Z 0318/0406 CNL\n';

print('Demo, decoding raw taf "'.$raw_taf.'"'.PHP_EOL);

$decoder = new TafDecoder();
$decoded_taf = $decoder->parse($raw_taf);

print('Report type: '.$decoded_taf->getType().PHP_EOL);

