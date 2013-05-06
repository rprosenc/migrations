<?php
/**
 * Created by JetBrains PhpStorm.
 * User: tsubera
 * Date: 06.05.13
 * Time: 10:05
 * To change this template use File | Settings | File Templates.
 */

namespace TwentyFifth\Migrations\Manager\ConfigManager;


class ZF1Manager implements ConfigInterface {

	private $application_path;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @param $application_path
	 */
	function __construct($application_path)
	{
		$this->application_path = $application_path;

		$zend_config = new \Zend_Config_Ini($application_path . '/configs/application.ini', APPLICATION_ENV);
		$this->setConfig($zend_config);
	}

	/**
	 * initialize config array
	 *
	 * @param \Zend_Config_Ini $zend_config
	 *
	 * @return $this
	 */
	protected function setConfig(\Zend_Config_Ini $zend_config)
	{
		$config = $zend_config->resources->doctrine->toArray();
		$defaultConnection = $config['dbal']['defaultConnection'];
		$this->config = $config['dbal']['connections'][$defaultConnection]['parameters'];
		return $this;
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