<?php
/**
 * Created by JetBrains PhpStorm.
 * User: tsubera
 * Date: 06.05.13
 * Time: 10:10
 * To change this template use File | Settings | File Templates.
 */

namespace TwentyFifth\Migrations\Manager\ConfigManager;


interface ConfigInterface {
	/**
	 * @return string Hostname
	 */
	public function getHost();

	/**
	 * @return string Port
	 */
	public function getPort();

	/**
	 * @return string Database name
	 */
	public function getDatabase();

	/**
	 * @param string $database
	 *
	 * @return ConfigInterface
	 */
	public function setDatabase($database);

	/**
	 * @return string Username
	 */
	public function getUsername();

	/**
	 * @return string Password
	 */
	public function getPassword();
}