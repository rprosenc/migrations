#!/usr/bin/env php
<?php

use Symfony\Component\Console;
use TwentyFifth\Migrations\Command;

function includeIfExists($file)
{
	if (file_exists($file)) {
		return include $file;
	}
}

if ((!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
	die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
		'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
		'php composer.phar install'.PHP_EOL);
}

$sqlDirectory = '';

// ZF2
if (is_readable(__DIR__ . '/../config/application.config.php')) {
	chdir(dirname(__DIR__));
	$configManager = new \TwentyFifth\Migrations\Manager\ConfigManager\ZF2Manager(require __DIR__ . '/../config/application.config.php');

	$sqlDirectory = __DIR__ . '/../docs/sql/';
} else if (is_readable(__DIR__ . '/../../../../config/application.config.php')) {
	chdir(dirname(__DIR__) . '/../../../');
	$configManager = new \TwentyFifth\Migrations\Manager\ConfigManager\ZF2Manager(require __DIR__ . '/../../../../config/application.config.php');

	$sqlDirectory = __DIR__ . '/../../../../docs/sql/';

// ZF1
} else if (is_dir(__DIR__ . '/../application/')) {
	$configManager = new \TwentyFifth\Migrations\Manager\ConfigManager\ZF1Manager(__DIR__ . '/../application/');
	$sqlDirectory = __DIR__ . '/../docs/sql/';
} else if (is_dir(__DIR__ . '/../../../../application/')) {
	$configManager = new \TwentyFifth\Migrations\Manager\ConfigManager\ZF1Manager(__DIR__ . '/../../../../application/');
	$sqlDirectory = __DIR__ . '/../../../../docs/sql/';

// Symfony
} else if (is_readable(__DIR__ . '/../app/config/parameters.yml')) {
	$configManager = new \TwentyFifth\Migrations\Manager\ConfigManager\SymfonyManager(__DIR__ . '/../app/config/parameters.yml');
	$sqlDirectory = __DIR__ . '/../sql/';
} else if (is_readable(__DIR__ . '/../../../../app/config/parameters.yml')) {
	$configManager = new \TwentyFifth\Migrations\Manager\ConfigManager\SymfonyManager(__DIR__ . '/../../../../app/config/parameters.yml');
	$sqlDirectory = __DIR__ . '/../../../../sql/';

// UNKNOWN
} else {
	die('Could not set APPLICATION_PATH'.PHP_EOL);
}

$fileManager = new \TwentyFifth\Migrations\Manager\FileManager($sqlDirectory);

$application = new Console\Application('25th Migrations', '0.1.0');

$application->add(new Command\Status($configManager, $fileManager));
$application->add(new Command\Apply($configManager, $fileManager));

$application->run();
