[![Build Status](https://travis-ci.org/SagemCassiopee/php-taf-decoder.svg?branch=master)](https://travis-ci.org/SagemCassiopee/php-taf-decoder)
[![Coverage Status](https://coveralls.io/repos/github/SagemCassiopee/php-taf-decoder/badge.svg?branch=master)](https://coveralls.io/github/SagemCassiopee/php-taf-decoder?branch=master)

PHP TAF decoder
=================

A PHP library to decode TAF (Terminal Aerodrome Forecast) strings. 

Introduction
------------

This piece of software is a library package that provides a parser to decode raw TAF messages.

TAF is a format made for weather information forecast. It is predominantly used by in aviation, during flight preparation.
Raw TAF format is highly standardized through the International Civil Aviation Organization (ICAO).

*    [TAF definition on wikipedia](https://en.wikipedia.org/wiki/Terminal_aerodrome_forecast)
*    [TAF format specification](http://www.wmo.int/pages/prog/www/WMOCodes/WMO306_vI1/VolumeI.1.html)

Tests and coverage
------------------

This library is fully unit tested, and uses [PHPUnit](https://phpunit.de/getting-started.html) to launch the tests.

Travis CI is used for continuous integration, which triggers tests for PHP 5.3, 5.4, 5.5 for each push to the repo.

To run the tests by yourself, you must first install the dev dependencies ([composer](http://getcomposer.org) needed)

```shell
composer install --dev
apt-get install php5-xdebug # only needed if you're interested in code coverage
```

Launch the test suite with the following command:
    
```shell
./vendor/bin/phpunit tests
```

You can also generate an html coverage report by adding the `--coverage-html` option:

```shell
./vendor/bin/phpunit --coverage-html ./report tests
```
