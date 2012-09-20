<?php

namespace TwentyFifth\Migrations\Command;

use Symfony\Component\Console;

class Status
	extends AbstractCommand
{
	public function __construct()
	{
		parent::__construct('status');

		$this->setDescription('Displays the current schema version');
	}

	public function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		parent::execute($input, $output);

		$file_list = $this->file_manager->getOrderedFileList();
		$missing_migrations = $this->schema_manager->getNotAppliedMigrations($file_list);

		if (count($missing_migrations)) {
			$output->writeln('The following migrations are not applied:');
		}

		foreach ($missing_migrations as $shortname => $path) {
			$output->writeln("\t - ". $shortname);
		}
	}

}