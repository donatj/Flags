<?php

require('vendor/autoload.php');

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

drop($flags->longs(), $flags->shorts(), $flags->args());


echo PHP_EOL;