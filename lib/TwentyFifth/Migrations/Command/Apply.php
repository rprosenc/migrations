<?php

namespace TwentyFifth\Migrations\Command;

use Symfony\Component\Console;

class Apply
	extends AbstractCommand
{
	public function __construct()
	{
		parent::__construct('apply');

		$this->setDescription('Do ze magic migration stuff (aka. executing SQL scripts)');

		$this->addArgument(
			'what',
			Console\Input\InputArgument::REQUIRED,
			'What can I do for you? ["all", "next", <specific migration name>]',
			null
		);
	}

	public function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
	{
		parent::execute($input, $output);

		$target = strtolower($input->getArgument('what'));

		switch ($target) {
			case 'next':
				$this->migrateNext($output);
				break;
			case 'all':
				$this->migrateAll($output);
				break;
			default:
				$this->migrateByName($output, $target);
				break;
		}
	}

	protected function migrateNext(Console\Output\OutputInterface $output)
	{
		$missing_migrations = $this->getMissingMigrations();

		if (0 === count($missing_migrations)) {
			$this->errors[] = "All migration files are already applied.\nNothing to do";
			$this->outputErrorsAndExit($output);
		}

		// Choose next migration configuration
		$next_migration_short_name = array_shift(array_keys($missing_migrations));
		$next_migration_path = $missing_migrations[$next_migration_short_name];

		$sql = file_get_contents($next_migration_path);
		$this->schema_manager->executeMigration($next_migration_short_name, $sql, $output);
	}

	protected function migrateAll(Console\Output\OutputInterface $output)
	{
		$missing_migrations = $this->getMissingMigrations();

		if (0 === count($missing_migrations)) {
			$this->errors[] = "All migration files are already applied.\nNothing to do";
			$this->outputErrorsAndExit($output);
		}

		foreach ($missing_migrations as $shortname => $path) {
			$sql = file_get_contents($path);
			$result = $this->schema_manager->executeMigration($shortname, $sql, $output);
			if (!$result) {
				return;
			}
		}
	}

	protected function migrateByName(Console\Output\OutputInterface $output, $target)
	{
		$all_migrations = $this->file_manager->getOrderedFileList();

		if (!array_key_exists($target, $all_migrations)) {
			$this->errors[] = "Migration $target was not found";
			$this->outputErrorsAndExit($output);
		}

		$missing_migrations = $this->schema_manager->getNotAppliedMigrations($all_migrations);

		if (!array_key_exists($target, $missing_migrations)) {
			$this->errors[] = "Migration $target exists but is already applied.";
			$this->outputErrorsAndExit($output);
		}

		$sql = file_get_contents($missing_migrations[$target]);
		$this->schema_manager->executeMigration($target, $sql, $output);
	}
}