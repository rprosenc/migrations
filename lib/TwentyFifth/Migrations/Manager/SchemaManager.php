<?php

namespace TwentyFifth\Migrations\Manager;

use Symfony\Component\Console;

class SchemaManager
{
	/** @var \Doctrine\DBAL\Connection */
	private $connection;

	/** @var string */
	private $migration_table_name = 'migrations';

	public function __construct(\Doctrine\DBAL\Connection $connection)
	{
		$this->connection = $connection;

		$this->ensureMigrationsTableExists();
	}

	protected function getMigrationTableName()
	{
		return $this->migration_table_name;
	}

	protected function getConnection()
	{
		return $this->connection;
	}

	public function doesMigrationsTableExist()
	{
		/** @var $stmt \Doctrine\DBAL\Driver\Statement */
		$stmt = $this->getConnection()->prepare(sprintf('SELECT * FROM %s LIMIT 1', $this->getMigrationTableName()));

		try {
			if ($stmt->execute() === false){
				return false;
			} else {
				return true;
			}
		} catch (\Exception $e) {
			return false;
		}
	}

	public function ensureMigrationsTableExists()
	{
		if (!$this->doesMigrationsTableExist()) {
			$create_sql = sprintf(
				'
				CREATE TABLE %s (
					mig_title text NOT NULL PRIMARY KEY,
					mig_applied timestamp NOT NULL DEFAULT NOW()
				);
			', $this->getMigrationTableName());

			$this->getConnection()->query($create_sql);
		}
	}

	public function getNotAppliedMigrations($migration_list)
	{
		$sql = '
			SELECT *
			FROM %s;
		';
		/** @var $stmt \Doctrine\DBAL\Statement */
		$stmt = $this->getConnection()->prepare(sprintf($sql, $this->getMigrationTableName()));
		$stmt->execute();

		while ($line = $stmt->fetch()) {
			unset($migration_list[$line['mig_title']]);
		}

		return $migration_list;
	}

	public function executeMigration($name, $sql, Console\Output\OutputInterface $output)
	{
		$connection = $this->getConnection();

		$output->writeln('Starting '.$name);
		$connection->beginTransaction();
		try {
			$connection->exec($sql);
			$connection->insert($this->getMigrationTableName(), array('mig_title' => $name));
			$connection->commit();
			$output->writeln($name.' is committed');
			return true;
		} catch (\Exception $e) {
			$connection->rollback();
			$output->writeln('Failed: '.$e->getMessage());
			return false;
		}
	}
}