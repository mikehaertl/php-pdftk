<?php

declare(strict_types=1);

require_once './vendor-bin/coding-standard/vendor/autoload.php';

use PhpCsFixer\Config;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$config = new Config();
$config
	->setParallelConfig(ParallelConfigFactory::detect())
	->getFinder()
	->ignoreVCSIgnored(true)
	->notPath('vendor')
	->notPath('vendor-bin')
	->in(__DIR__);
return $config;
