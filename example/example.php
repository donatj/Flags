<?php

require('../vendor/autoload.php');

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