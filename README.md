[![Build Status](https://travis-ci.org/SagemCassiopee/php-taf-decoder.svg?branch=master)](https://travis-ci.org/SagemCassiopee/php-taf-decoder)
[![Coverage Status](https://coveralls.io/repos/github/SagemCassiopee/php-taf-decoder/badge.svg?branch=master)](https://coveralls.io/github/SagemCassiopee/php-taf-decoder?branch=master)

PHP TAF decoder
=================

A PHP library to decode TAF (Terminal Aerodrome Forecast) strings, fully unit tested (100% code coverage) 

Try it on the [demo website](https://php-taf-decoder.cassiopee.aero)

Introduction
------------

This piece of software is a library package that provides a parser to decode raw TAF messages.

TAF is a format made for weather information forecast. It is predominantly used by in aviation, during flight preparation.
Raw TAF format is highly standardized through the International Civil Aviation Organization (ICAO).

*    [TAF definition on wikipedia](https://en.wikipedia.org/wiki/Terminal_aerodrome_forecast)
*    [TAF format specification](http://www.wmo.int/pages/prog/www/WMOCodes/WMO306_vI1/VolumeI.1.html)

Requirements
------------

This library package only requires PHP >= 5.3 

It is currently tested automatically for PHP 5.3, 5.4 and 5.5.

If you want to integrate it easily in your project, you should consider installing [composer](http://getcomposer.org) on your system.
It is not mandatory though.

Setup
-----

- With composer *(recommended)*

Add the following line to the `composer.json` of your project

```json
{
    "require": {
        "sagem-cassiopee/php-taf-decoder": "dev-master"
    }
}
```

Launch install from your project root with:

```shell
composer install --no-dev
```

Load the library thanks to composer autoloading:

```php
<?php
require_once 'vendor/autoload.php';

- By hand

Download the latest release from [github](https://github.com/SagemCassiopee/php-taf-decoder/releases)

Extract it wherever you want in your project. The library itself is in the src/ directory, the other directories are not mandatory for the library to work.

Load the library with the static import file:

```php
<?php
require_once 'path/to/TafDecoder/TafDecoder.inc.php';
```

Usage
-----

Instantiate the decoder and launch it on a TAF string.
The returned object is a DecodedTaf object from which you can retrieve all the weather properties that have been decoded.

All values who have a unit are based on the `Value` object which provides the methods `getValue()` and `getUnit()`

*TODO: full documentation of the structure of the DecodedTaf object*

*TODO: Provide a PHP example*

Contribute
----------

If you find a valid TAF that is badly parsed by this library, please open a github issue with all possible details:

- the full TAF causing problem
- the parsing exception returned by the library
- how you expected the decoder to behave
- anything to support your proposal (links to official websites appreciated)

If you want to improve or enrich the test suite, fork the repository and submit your changes with a pull request.

If you have any other idea to improve the library, please use github issues or directly pull requests depending on what you're more comfortable with.

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
