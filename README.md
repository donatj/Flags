# Flags

[![Build Status](https://travis-ci.org/donatj/Flags.png?branch=master)](https://travis-ci.org/donatj/Flags)

A Go-lang `Flag` package inspired argument parser supporting `--key=value` `--key value` `-v` `-vvv` `-Xasd` style arguments as well as ` -- ` separation of arguments from options.

## Requirements

- PHP 5.3

## Installing

Flags is available through Packagist via Composer

```json
{
    "require": { 
        "donatj/flags": "dev-master"
    }
}
```

```php
<?php

$flags = new donatj\Flags();

$sponges = & $flags->bool('sponges', 'cat', 'this gives you 10 sponges');
$what    = & $flags->uint('what', 'cat', 'What to do what to');
$cat     = & $flags->string('cat', 'fish', 'This controls the number of cats one is given');
$pie     = & $flags->string('pie', null, 'This gives you the mad pies');
$verbose = & $flags->short('v', 'verbosity');


try {
	$flags->parse();
} catch(Exception $e) {
	die($e->getMessage() . PHP_EOL . $flags->getDefaults() . PHP_EOL );
}