<?php

use Corpus\PhpCsFixer\Config;
use PhpCsFixer\Finder;

$config = Config::php7x4();
$config->setRules(array_merge($config->getRules(), [
	'@PHPUnit9x1Migration:risky' => true,
]));
$config->setFinder(
	Finder::create()->in(__DIR__ . '/src')->in(__DIR__ . '/test')->in(__DIR__ . '/example'),
);
$config->setUnsupportedPhpVersionAllowed(true);

return $config;
