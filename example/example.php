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