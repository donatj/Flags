<?php

require('vendor/autoload.php');

$flags = new donatj\Flags();

$sponges = & $flags->bool('sponges', 'cat', 'this gives you 10 sponges');
$what    = & $flags->uint('what', 'cat', 'dog');
$cat     = & $flags->string('cat', 'fish', 'dog');
$verbose = & $flags->short('v', 'verbosity');


try {
	$flags->parse();
} catch(Exception $e) {
	die($e->getMessage());
}

drop($sponges, $what, $cat, $verbose, $sponges);


echo PHP_EOL;