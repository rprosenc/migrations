<?php

namespace TwentyFifth\Migrations\Command;

use TwentyFifth\Migrations\Manager\SchemaManager;
use TwentyFifth\Migrations\Manager\FileManager;

use Symfony\Component\Console;
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

		$this->config = new \Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);

		$this->bisna_container = new DoctrineContainer($this->config->resources->doctrine->toArray());
	}

	protected function outputErrorsAndExit(Console\Output\OutputInterface $output, $code = 1)
	{
		$output->writeln($this->errors);
		$output->writeln($this->getSynopsis());
		exit($code);
	}

	public function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		try {
			$this->schema_manager = new SchemaManager($this->getConnection());
			$this->file_manager = new FileManager(APPLICATION_PATH . '/../docs/sql/');
		} catch (\Exception $e) {
			$this->errors[] = $e->getMessage();
			$this->outputErrorsAndExit($output, 1);
		}
	}

	/**
	 * @return \Doctrine\DBAL\Connection
	 */
	protected function getConnection()
	{
		return $this->bisna_container->getConnection();
	}

	protected function getMissingMigrations()
	{
		$all_migrations = $this->file_manager->getOrderedFileList();
		return $this->schema_manager->getNotAppliedMigrations($all_migrations);
	}
}