<?php

namespace TafDecoder\Test;

use TafDecoder\TafDecoder;

class TafDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $decoder;

    public function __construct()
    {
        $this->decoder = new TafDecoder();
    }

    public function testConstruct()
    {
        $d = new TafDecoder();
    }

	public function testParse()
	{
		$raw_taf = "2013/11/03 18:54\nTAF TAF LIRU 032244Z 0318/0406 CNL\n";
		$d = $this->decoder->parseStrict($raw_taf);
		
		$this->assertTrue($d->isValid());
        $this->assertEquals("TAF TAF LIRU 032244Z 0318/0406 CNL", $d->getRawTaf());
        $this->assertEquals('TAF', $d->getType());

	}
}
