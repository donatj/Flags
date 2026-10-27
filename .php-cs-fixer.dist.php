<?php

use Corpus\PhpCsFixer\Config;
use PhpCsFixer\Finder;

$config = Config::php7x4();
$config->setFinder(
	Finder::create()->in(__DIR__ . '/src')->in(__DIR__ . '/test')->in(__DIR__ . '/example'),
);
$config->setUnsupportedPhpVersionAllowed(true);

return $config;
