<?php
namespace TwentyFifth\Migrations\Manager\ConfigManager;

/**
 * Plain Old Php Object Manager
 *
 * Class PopoManager
 * @package TwentyFifth\Migrations\Manager\ConfigManager
 */
class PopoManager implements ConfigInterface {
	private $host = "";
	private $port = "";
	private $database = "";
	private $username = "";
	private $password = "";

	/**
	 * @param string $database
	 *
	 * @return PopoManager
	 */
	public function setDatabase($database)
	{
		$this->database = $database;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDatabase()
	{
		return $this->database;
	}

	/**
	 * @param string $host
	 *
	 * @return PopoManager
	 */
	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * @param string $password
	 *
	 * @return PopoManager
	 */
	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $port
	 *
	 * @return PopoManager
	 */
	public function setPort($port)
	{
		$this->port = $port;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * @param string $username
	 *
	 * @return PopoManager
	 */
	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

}