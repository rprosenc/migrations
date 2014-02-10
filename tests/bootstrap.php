<?php

function includeIfExists($file)
{
	if (file_exists($file)) {
		return include $file;
	}
}

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));


if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
	die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
		'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
		'php composer.phar install'.PHP_EOL);
}

//require_once APPLICATION_PATH . '/../vendor/autoload.php';

require_once 'ArrayDataSet.php';

//if (getenv('APPLICATION_ENV')) {
//	define('APPLICATION_ENV', getenv('APPLICATION_ENV'));
//} else {
//	die('Please set the APPLICATION_ENV'.PHP_EOL);
//}



//
//// Define path to application directory
//defined('APPLICATION_PATH')
//|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
//
//// Define application environment
//defined('APPLICATION_ENV')
//|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));
//
//// Ensure library/ is on include_path
//set_include_path(implode(PATH_SEPARATOR, array(
//	realpath(APPLICATION_PATH . '/../library'),
//	get_include_path(),
//)));
//
//require_once APPLICATION_PATH . '/../vendor/autoload.php';
//
//require_once 'Zend/Loader/Autoloader.php';
//Zend_Loader_Autoloader::getInstance();
//
//// LaLinea specific includes
//require 'ControllerTestCase.php';
//
//$application = new Zend_Application(
//	APPLICATION_ENV,
//	APPLICATION_PATH . '/configs/application.ini'
//);
//
//// $application->bootstrap();