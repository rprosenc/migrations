<?php
/**
 * Created by JetBrains PhpStorm.
 * User: tsubera
 * Date: 07.05.13
 * Time: 09:39
 * To change this template use File | Settings | File Templates.
 */

namespace TwentyFifth\Migrations\Manager\ConfigManager;


use Zend\Mvc\Application;
use Zend\ServiceManager\ServiceManager;

class ZF2Manager implements ConfigInterface
{
	private $config;

	function __construct($configuration)
	{
		if (!getenv('APPLICATION_ENV')) {
			die('Please set the APPLICATION_ENV'.PHP_EOL);
		}

		$app = Application::init($configuration);
		$config = $app->getConfig();

		$this->config = $config['doctrine']['connection']['orm_default']['params'];
	}

	/**
	 * @return string Hostname
	 */
	public function getHost()
	{
		return $this->config['host'];
	}

	/**
	 * @return string Port
	 */
	public function getPort()
	{
		return $this->config['port'];
	}

	/**
	 * @return string Database name
	 */
	public function getDatabase()
	{
		return $this->config['dbname'];
	}

	/**
	 * @param string $database
	 *
	 * @return ConfigInterface
	 */
	public function setDatabase($database)
	{
		$this->config['dbname'] = $database;
		return $this;
	}

	/**
	 * @return string Username
	 */
	public function getUsername()
	{
		return $this->config['user'];
	}

	/**
	 * @return string Password
	 */
	public function getPassword()
	{
		return $this->config['password'];
	}
}