<?php

namespace TwentyFifth\Migrations\Command;

use TwentyFifth\Migrations\Manager\SchemaManager;
use TwentyFifth\Migrations\Manager\FileManager;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputOption;
use Bisna\Doctrine\Container as DoctrineContainer;

abstract class AbstractCommand
	extends Console\Command\Command
{
	private $bisna_container;

	/* @var \Zend_Config */
	private $config;

	/** @var SchemaManager */
	protected $schema_manager;

	/** @var FileManager */
	protected $file_manager;

	protected $errors = array();

	public function __construct($name = null)
	{
		parent::__construct($name);

		$this->addOption('database',null,InputOption::VALUE_REQUIRED,'Override Database');

		$this->config = new \Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
	}

	protected function outputErrorsAndExit(Console\Output\OutputInterface $output, $code = 1)
	{
		$output->writeln($this->errors);
		$output->writeln($this->getSynopsis());
		exit($code);
	}

	public function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		$database = (string) $input->getOption('database');

		try {
			$this->schema_manager = new SchemaManager($this->getConnection($database));
			$this->file_manager = new FileManager(APPLICATION_PATH . '/../docs/sql/');
		} catch (\Exception $e) {
			$this->errors[] = $e->getMessage();
			$this->outputErrorsAndExit($output, 1);
		}
	}

	/**
	 * get's the config and allows us to override the database name
	 *
	 * @param \Zend_Config $zend_config
	 * @param string $database
	 *
	 * @return array
	 */
	static public function getDoctrineConfig(\Zend_Config $zend_config, $database = '')
	{
		$config = $zend_config->resources->doctrine->toArray();

		if (strlen(trim($database)) == 0) {
			return $config;
		}

		// override database name
		$defaultConnection = $config['dbal']['defaultConnection'];
		$config['dbal']['connections'][$defaultConnection]['parameters']['dbname'] = $database;
		return $config;
	}

	/**
	 * get Config as an Array with the possibility to override the database name
	 *
	 * @param string $database
	 *
	 * @return array
	 */
	protected function getConfig($database = '')
	{
		return self::getDoctrineConfig($this->config, $database);
	}

	/**
	 * @param string $database
	 *
	 * @return \Doctrine\DBAL\Connection
	 */
	protected function getConnection($database)
	{
		if (is_null($this->bisna_container)) {
			$this->bisna_container = new DoctrineContainer($this->getConfig($database));
		}
		return $this->bisna_container->getConnection();
	}

	protected function getMissingMigrations()
	{
		$all_migrations = $this->file_manager->getOrderedFileList();
		return $this->schema_manager->getNotAppliedMigrations($all_migrations);
	}
}