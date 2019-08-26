# Flags

[![Latest Stable Version](https://poser.pugx.org/donatj/flags/version)](https://packagist.org/packages/donatj/flags)
[![Total Downloads](https://poser.pugx.org/donatj/flags/downloads)](https://packagist.org/packages/donatj/flags)
[![License](https://poser.pugx.org/donatj/flags/license)](https://packagist.org/packages/donatj/flags)
[![Build Status](https://travis-ci.org/donatj/Flags.svg?branch=master)](https://travis-ci.org/donatj/Flags)


Flags is an argument parser inspired by the Go-lang [Flag](http://golang.org/pkg/flag/#Parsed) package, taking its methodology but attaching a **GNU-style** flag parser.

---

Flags supports the following style of parameters:

Long-Flags  
`--key=value` / `--key value`

Short-Flags  
`-v`

GNU Style Multi-Short-Flags  
`-Xasd`

Multiple of the Same Short-Flag  
`-vvv`

As well as the ` -- ` operator for absolute separation of arguments from options.

## Requirements

- **php**: >=5.3.0

## Installing

Install the latest version with:

```bash
composer require 'donatj/flags'
```

## Example

Here is a simple example script:

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

$flags = new donatj\Flags();

$foo     = & $flags->bool('foo', false, 'Enable the foo');
$bar     = & $flags->uint('bar', 10, 'Number of bars');
$baz     = & $flags->string('baz', 'default', 'What to name the baz');
$verbose = & $flags->short('v', 'verbosity');

/**
 * No Default value, making qux is *required*
 */
$qux = & $flags->bool('qux');

try {
	$flags->parse();
} catch( Exception $e ) {
	die($e->getMessage() . PHP_EOL . $flags->getDefaults() . PHP_EOL);
}
```

The by-reference `= &` allows the value to be updated from the *default* to the argument value once the `parse()` method has been triggered. This is inspired by the Go Flag packages use of pointers

```

bash-3.2$ php example/example.php
Expected option --qux missing.
        -v   verbosity
     --foo   Enable the foo
     --bar   [uint] Number of bars
     --baz   [string] What to name the baz
     --qux   <bool>


				
```

## Documentation

### Class: \donatj\Flags

#### Method: Flags->__construct

```php
function __construct([ $args = null [, $skipFirstArgument = true]])
```

Flags constructor.

##### Parameters:

- ***array*** `$args` - The arguments to parse, defaults to $_SERVER['argv']
- ***bool*** `$skipFirstArgument` - Setting to false causes the first argument to be parsed as an parameter rather
    than the command.

---

#### Method: Flags->arg

```php
function arg($index)
```

Returns the n'th command-line argument. `arg(0)` is the first remaining argument after flags have been processed.

##### Parameters:

- ***int*** `$index`

##### Returns:

- ***string***

---

#### Method: Flags->args

```php
function args()
```

Returns the non-flag command-line arguments.

##### Returns:

- ***string[]*** - Array of argument strings

---

#### Method: Flags->shorts

```php
function shorts()
```

Returns an array of short-flag call-counts indexed by character  
`-v` would set the 'v' index to 1, whereas `-vvv` will set the 'v' index to 3

##### Returns:

- ***array***

---

#### Method: Flags->longs

```php
function longs()
```

Returns an array of long-flag values indexed by flag name

##### Returns:

- ***array***

---

#### Method: Flags->short

```php
function short($letter [, $usage = ''])
```

Defines a short-flag of specified name, and usage string.  
The return value is a reference to an integer variable that stores the number of times the short-flag was called.  
  
This means the value of the reference for v would be the following.  
  
    -v => 1  
    -vvv => 3

##### Parameters:

- ***string*** `$letter` - The character of the short-flag to define
- ***string*** `$usage` - The usage description

##### Returns:

- ***int***

---

#### Method: Flags->bool

```php
function bool($name [, $value = null [, $usage = '']])
```

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

##### Parameters:

- ***string*** `$name` - The name of the long-flag to define
- ***mixed*** `$value` - The default value - usually false for bool - which if null marks the flag required
- ***string*** `$usage` - The usage description

##### Returns:

- ***mixed*** - A reference to the flags value

---

#### Method: Flags->float

```php
function float($name [, $value = null [, $usage = '']])
```

Defines a float long-flag of specified name, default value, and usage string.  
The return value is a reference to a variable that stores the value of the flag.  

##### Examples

    --myfloat=1.1  
    --myfloat 1.1

##### Parameters:

- ***string*** `$name` - The name of the long-flag to define
- ***mixed*** `$value` - The default value which if null marks the flag required
- ***string*** `$usage` - The usage description

##### Returns:

- ***mixed*** - A reference to the flags value

---

#### Method: Flags->int

```php
function int($name [, $value = null [, $usage = '']])
```

Defines an integer long-flag of specified name, default value, and usage string.  
The return value is a reference to a variable that stores the value of the flag.  
  
Note: Float values trigger an error, rather than casting.  

##### Examples

    --myinteger=1  
    --myinteger 1

##### Parameters:

- ***string*** `$name` - The name of the long-flag to define
- ***mixed*** `$value` - The default value which if null marks the flag required
- ***string*** `$usage` - The usage description

##### Returns:

- ***mixed*** - A reference to the flags value

---

#### Method: Flags->uint

```php
function uint($name [, $value = null [, $usage = '']])
```

Defines a unsigned integer long-flag of specified name, default value, and usage string.  
The return value is a reference to a variable that stores the value of the flag.  
  
Note: Negative values trigger an error, rather than casting.  

##### Examples

    --myinteger=1  
    --myinteger 1

##### Parameters:

- ***string*** `$name` - The name of the long-flag to define
- ***mixed*** `$value` - The default value which if null marks the flag required
- ***string*** `$usage` - The usage description

##### Returns:

- ***mixed*** - A reference to the flags value

---

#### Method: Flags->string

```php
function string($name [, $value = null [, $usage = '']])
```

Defines a string long-flag of specified name, default value, and usage string.  
The return value is a reference to a variable that stores the value of the flag.  
  
Examples  
  
    --mystring=vermouth  
    --mystring="blind jazz singers"  
    --mystring vermouth  
    --mystring "blind jazz singers"

##### Parameters:

- ***string*** `$name` - The name of the long-flag to define
- ***mixed*** `$value` - The default value which if null marks the flag required
- ***string*** `$usage` - The usage description

##### Returns:

- ***mixed*** - A reference to the flags value

---

#### Method: Flags->getDefaults

```php
function getDefaults()
```

Returns the default values of all defined command-line flags as a formatted string.

##### Example

              -v   Output in verbose mode  
     --testsuite   [string] Which test suite to run.  
     --bootstrap   [string] A "bootstrap" PHP file that is run before the specs.  
          --help   Display this help message.  
       --version   Display this applications version.

##### Returns:

- ***string***

---

#### Method: Flags->parse

```php
function parse([ $args = null [, $ignoreExceptions = false [, $skipFirstArgument = null]]])
```

Parses flag definitions from the argument list, which should include the command name.  
Must be called after all flags are defined and before flags are accessed by the program.  
  
Will throw exceptions on Missing Require Flags, Unknown Flags or Incorrect Flag Types

##### Parameters:

- ***array*** `$args` - The arguments to parse. Defaults to arguments defined in the constructor.
- ***bool*** `$ignoreExceptions` - Setting to true causes parsing to continue even after an exception has been
    thrown.
- ***bool*** `$skipFirstArgument` - Option to parse the first argument as an parameter rather than the command.
    Defaults to constructor value

---

#### Method: Flags->parsed

```php
function parsed()
```

Returns true if the command-line flags have been parsed.

##### Returns:

- ***bool***