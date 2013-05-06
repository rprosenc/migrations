<?php

namespace TwentyFifth\Migrations\Command;

use TwentyFifth\Migrations\Manager\ConfigManager\ConfigInterface;
use TwentyFifth\Migrations\Manager\SchemaManager;
use TwentyFifth\Migrations\Manager\FileManager;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputOption;
use Bisna\Doctrine\Container as DoctrineContainer;

abstract class AbstractCommand
	extends Console\Command\Command
{
	private $bisna_container;

	/* @var ConfigInterface */
	private $config_manager;

	/** @var SchemaManager */
	protected $schema_manager;

	/** @var FileManager */
	protected $file_manager;

	protected $errors = array();

	public function __construct(ConfigInterface $configManager, FileManager $fileManager, $name = null)
	{
		parent::__construct($name);
		$this->config_manager = $configManager;
		$this->file_manager = $fileManager;

		$this->addOption('database',null,InputOption::VALUE_REQUIRED,'Override Database');
	}

	protected function outputErrorsAndExit(Console\Output\OutputInterface $output, $code = 1)
	{
		$output->writeln($this->errors);
		$output->writeln($this->getSynopsis());
		exit($code);
	}

	public function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		// override database
		$database = (string) $input->getOption('database');
		if (strlen($database) > 0) {
			$this->config_manager->setDatabase($database);
		}

		try {
			$this->schema_manager = new SchemaManager($this->config_manager);
		} catch (\Exception $e) {
			$this->errors[] = $e->getMessage();
			$this->outputErrorsAndExit($output, 1);
		}
	}

	protected function getMissingMigrations()
	{
		$all_migrations = $this->file_manager->getOrderedFileList();
		return $this->schema_manager->getNotAppliedMigrations($all_migrations);
	}
}