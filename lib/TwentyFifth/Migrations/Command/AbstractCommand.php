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
			$this->schema_manager = new SchemaManager($this->getConfig($database));
			$this->file_manager = new FileManager(APPLICATION_PATH . '/../docs/sql/');
		} catch (\Exception $e) {
			$this->errors[] = $e->getMessage();
			$this->outputErrorsAndExit($output, 1);
		}
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
		$config = $this->config->resources->doctrine->toArray();
		$defaultConnection = $config['dbal']['defaultConnection'];
		return $config['dbal']['connections'][$defaultConnection]['parameters'];
	}

	protected function getMissingMigrations()
	{
		$all_migrations = $this->file_manager->getOrderedFileList();
		return $this->schema_manager->getNotAppliedMigrations($all_migrations);
	}
}