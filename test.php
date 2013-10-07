<?php

require('vendor/autoload.php');

$flags = new donatj\Flags();

$sponges = & $flags->bool('foo', true, 'enable the foo');
$what    = & $flags->uint('bar', 10, 'number of bars');
$cat     = & $flags->string('cat', null, 'this option is required');
$help    = & $flags->bool('help', false, 'shows this text');
$verbose = & $flags->short('v', 'verbosity');


try {
	$flags->parse();
} catch(Exception $e) {
	die($e->getMessage() . PHP_EOL . $flags->getDefaults() . PHP_EOL);
}

if( $help ) {
	die( $flags->getDefaults() . PHP_EOL );
}

	drop($flags->longs(), $flags->shorts(), $flags->args());


echo PHP_EOL;