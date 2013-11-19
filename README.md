# Flags

[![Build Status](https://travis-ci.org/donatj/Flags.png?branch=master)](https://travis-ci.org/donatj/Flags)

Flags is an argument parser inspired by the Go-lang [Flag](http://golang.org/pkg/flag/#Parsed) package, taking its methodology but attaching a **GNU-style** flag parser.
   
It supports the following style of parameters:

Long flags `--key=value` / `--key value`  
Short flags `-v`
Multiple of the same short flag `-vvv` 
GNU style multi-short-flag `-Xasd`  

It also features ` -- ` for absolute separation of arguments from options.

## Requirements

- PHP 5.3+

## Installing

Flags is available through Packagist via Composer

```json
{
    "require": { 
        "donatj/flags": "dev-master"
    }
}
```

Here is a simple example.

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

## Documentation

### Class: Flags - `\donatj\Flags`

#### Method: `Flags`->`arg($index)`

Returns the n'th command-line argument. `arg(0)` is the first remaining argument after flags have been processed.  
  


##### Parameters

- ***int*** `$index`


##### Returns

- ***string***


---

#### Method: `Flags`->`args()`

Returns the non-flag command-line arguments.  
  


##### Returns

- ***string[]*** - Array of argument strings


---

#### Method: `Flags`->`shorts()`

Returns an array of short-flag call-counts indexed by character  
`-v` would set the 'v' index to 1, whereas `-vvv` will set the 'v' index to 3  


##### Returns

- ***array***


---

#### Method: `Flags`->`longs()`

Returns an array of long-flag values indexed by flag name  
  


##### Returns

- ***array***


---

#### Method: `Flags`->`short($letter [, $usage = ''])`

Defines a short-flag of specified name, and usage string.  
The return value is a reference to an integer variable that stores the number of times the short-flag was called.  
  
This means the value of the reference for v would be the following.  
  
    -v => 1  
    -vvv => 3  


##### Parameters

- ***string*** `$letter` - The character of the short-flag to define
- ***string*** `$usage` - The usage description


##### Returns

- ***int***


---

#### Method: `Flags`->`bool($name [, $value = null [, $usage = '']])`

Defines a bool long-flag of specified name, default value, and usage string.  
The return value is a reference to a variable that stores the value of the flag.  
  
##### Examples  
  
##### Truth-y  
  
     --mybool=[true|t|1]  
     --mybool [true|t|1]  
     --mybool  
  
##### False-y  
  
     --mybool=[false|f|0]  
     --mybool [false|f|0]  
       [not calling --mybool and having the default false]  


##### Parameters

- ***string*** `$name` - The name of the long-flag to define
- ***mixed*** `$value` - The default value - usually false for bool - which if null marks the flag required
- ***string*** `$usage` - The usage description


##### Returns

- ***mixed*** - A reference to the flags value


---

#### Method: `Flags`->`float($name [, $value = null [, $usage = '']])`

Defines a float long-flag of specified name, default value, and usage string.  
The return value is a reference to a variable that stores the value of the flag.  
  
##### Examples  
  
    --myfloat=1.1  
    --myfloat 1.1  


##### Parameters

- ***string*** `$name` - The name of the long-flag to define
- ***mixed*** `$value` - The default value which if null marks the flag required
- ***string*** `$usage` - The usage description


##### Returns

- ***mixed*** - A reference to the flags value


---

#### Method: `Flags`->`int($name [, $value = null [, $usage = '']])`

Defines an integer long-flag of specified name, default value, and usage string.  
The return value is a reference to a variable that stores the value of the flag.  
  
Note: Float values trigger an error, rather than casting.  
  
##### Examples  
  
    --myinteger=1  
    --myinteger 1  


##### Parameters

- ***string*** `$name` - The name of the long-flag to define
- ***mixed*** `$value` - The default value which if null marks the flag required
- ***string*** `$usage` - The usage description


##### Returns

- ***mixed*** - A reference to the flags value


---

#### Method: `Flags`->`uint($name [, $value = null [, $usage = '']])`

Defines a unsigned integer long-flag of specified name, default value, and usage string.  
The return value is a reference to a variable that stores the value of the flag.  
  
Note: Negative values trigger an error, rather than casting.  
  
##### Examples  
  
    --myinteger=1  
    --myinteger 1  


##### Parameters

- ***string*** `$name` - The name of the long-flag to define
- ***mixed*** `$value` - The default value which if null marks the flag required
- ***string*** `$usage` - The usage description


##### Returns

- ***mixed*** - A reference to the flags value


---

#### Method: `Flags`->`string($name [, $value = null [, $usage = '']])`

Defines a string long-flag of specified name, default value, and usage string.  
The return value is a reference to a variable that stores the value of the flag.  
  
Examples  
  
    --mystring=vermouth  
    --mystring="blind jazz singers"  
    --mystring vermouth  
    --mystring "blind jazz singers"  


##### Parameters

- ***string*** `$name` - The name of the long-flag to define
- ***mixed*** `$value` - The default value which if null marks the flag required
- ***string*** `$usage` - The usage description


##### Returns

- ***mixed*** - A reference to the flags value


---

#### Method: `Flags`->`getDefaults()`

Returns the default values of all defined command-line flags as a formatted string.  
##### Example  
  
              -v   Output in verbose mode  
     --testsuite   [string] Which test suite to run.  
     --bootstrap   [string] A "bootstrap" PHP file that is run before the specs.  
          --help   Display this help message.  
       --version   Display this applications version.  


##### Returns

- ***string***


---

#### Method: `Flags`->`parse([ $args = null [, $ignoreExceptions = false]])`

Parses flag definitions from the argument list, which should include the command name.  
Must be called after all flags are defined and before flags are accessed by the program.  
  
Will throw exceptions on Missing Require Flags, Unknown Flags or Incorrect Flag Types  


##### Parameters

- ***array*** `$args` - The arguments to parse, defaults to $GLOBALS['argv']
- ***bool*** `$ignoreExceptions` - Setting to true causes parsing to continue even after an exception has been thrown.



---

#### Method: `Flags`->`parsed()`

Returns true if the command-line flags have been parsed.  
  


##### Returns

- ***bool***


