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

Below is a simple example.

```php
<?php

$flags = new donatj\Flags();

$sponges = & $flags->bool('foo', false, 'Enable the foo');
$what    = & $flags->uint('bar', 10, 'Number of bars');
$cat     = & $flags->string('baz', 'Fred', 'What to name the baz');
$verbose = & $flags->short('v', 'verbosity');

try {
	$flags->parse();
} catch(Exception $e) {
	die($e->getMessage() . PHP_EOL . $flags->getDefaults() . PHP_EOL );
}
```
