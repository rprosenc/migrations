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

if (is_dir(__DIR__ . '/../application/')) {
	define('APPLICATION_PATH', __DIR__ . '/../application/');
} else if (is_dir(__DIR__ . '/../../../../application/')) {
	define('APPLICATION_PATH', __DIR__ . '/../../../../application/');
} else {
	die('Could not set APPLICATION_PATH'.PHP_EOL);
}

if (getenv('APPLICATION_ENV')) {
	define('APPLICATION_ENV', getenv('APPLICATION_ENV'));
} else {
	die('Please set the APPLICATION_ENV'.PHP_EOL);
}

$application = new Console\Application('25th Migrations', '0.1.0');

$application->add(new Command\Status());
$application->add(new Command\Apply());

$application->run();
