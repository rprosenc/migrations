<?php

namespace TwentyFifth\Migrations\Test\Component;

use \TwentyFifth\Migrations\Manager\SchemaManager;

class SchemaManagerTest
	extends \PHPUnit_Extensions_Database_TestCase
{

	/* @var \PDO */
	private static $pdo;

	/* @var \PHPUnit_Extensions_Database_DB_IDatabaseConnection */
	private static $connection;

	/**
	 * Returns the test database connection.
	 *
	 * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	public function getConnection()
	{
		if (!isset(self::$connection)) {
			if (!isset(self::$pdo)) {
				self::$pdo = new \PDO('pgsql:dbname=migrations_test', 'bountin');
			}

			self::$connection = $this->createDefaultDBConnection(self::$pdo);
		}

		return self::$connection;
	}

	public function getDbalConnection()
	{
		$driver = new \Doctrine\DBAL\Driver\PDOPgSql\Driver();
		$connection = new \Doctrine\DBAL\Connection(array(
				'pdo'=>$this->getConnection()->getConnection(),
				'dbname' => 'migrations_test',
				'user' => 'bountin',
				'password' => '',
				'host' => 'localhost',
				'port' => '5432',
			), $driver
		);
		return $connection;
	}

	/**
	 * Returns the test dataset.
	 *
	 * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet()
	{
		return new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
	}

	/* @var string */
	protected $migrationTableName;

	public function setUp()
	{
		$sm = new SchemaManager($this->getDbalConnection());
		$this->migrationTableName = $sm->getMigrationTableName();

		$this->getConnection()->getConnection()->exec('DROP TABLE IF EXISTS '.$this->migrationTableName);
	}

	public function tearDown()
	{
		$tables = $this->getConnection()->getMetaData()->getTableNames();

		foreach ($tables as $table) {
			$this->getConnection()->getConnection()->exec('DROP TABLE '.$table);
		}
	}

	public function testManagerCreatesMigrationsTable()
	{
		// Just to be sure check if table does not exist
		$tables = $this->getConnection()->getMetaData()->getTableNames();
		$this->assertNotContains($this->migrationTableName, $tables);

		$sm = new SchemaManager($this->getDbalConnection());
		$sm->ensureMigrationsTableExists();

		$tables = $this->getConnection()->getMetaData()->getTableNames();
		$this->assertContains($this->migrationTableName, $tables);
	}

	public function testManagerWorksWithExistingMigrationsTable()
	{
		$sm = new SchemaManager($this->getDbalConnection());
		$sm->ensureMigrationsTableExists();
		$sm->ensureMigrationsTableExists();

		$tables = $this->getConnection()->getMetaData()->getTableNames();
		$this->assertContains($this->migrationTableName, $tables);
	}

	/**
	 * @dataProvider provideListingData
	 */
	public function testManagerListsCorrectUnappliedMigrations($data_to_insert, $migration_list, $expected_list)
	{
		$sm = new SchemaManager($this->getDbalConnection());

		foreach ($data_to_insert as $line) {
			$this->getDbalConnection()->insert($this->migrationTableName, $line);
		}

		$actual_list = $sm->getNotAppliedMigrations($migration_list);
		$this->assertEquals($expected_list, $actual_list);
	}

	public function provideListingData()
	{
		$one_data = array(
			array('mig_title' => '1.sql'),
		);

		$one_list = array('1.sql'=>'');

		$complex_data = array(
			array('mig_title' => '1.sql'),
			array('mig_title' => '2.sql'),
			array('mig_title' => '3.sql'),
		);

		$complex_list = array(
			'2.sql'=>'',
			'4.sql'=>'',
		);

		$complex_result = array(
			'4.sql'=>'',
		);

		return array(
			'all empty'
				=> array(array(), array(), array()),
			'Nothing in DB, one in list'
				=> array(array(), $one_list, $one_list),
			'One in DB, none in list' // Should not happen but test it
				=> array($one_data, array(), array()),
			'One in DB, the same in list'
				=> array($one_data, $one_list, array()),
			'Complex one' // All together
				=> array($complex_data, $complex_list, $complex_result),
		);
	}

	protected function executeMigration($name, $sql)
	{
		$sm = new SchemaManager($this->getDbalConnection());
		return $sm->executeMigration($name, $sql, $this->getMock('Symfony\Component\Console\Output\OutputInterface'));
	}

	protected function getMigrationsDataset()
	{
		$migrations_metadata = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
			'migrations', array('mig_applied', 'mig_title')
		);
		$migrations_table = new \PHPUnit_Extensions_Database_DataSet_DefaultTable($migrations_metadata);
		$migrations_dataset = new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array($migrations_table));

		return $migrations_dataset;
	}

	/**
	 * @expectedException \TwentyFifth\Migrations\Exception\RuntimeException
	 */
	public function testManagerExecutesAVoidMigration()
	{
		$this->executeMigration('void.sql', '');
	}

	public function testManagerExecutesASimpleCreateTable()
	{
		$result = $this->executeMigration('simple.sql', 'CREATE TABLE test (id int);');

		$this->assertTrue($result, 'Expected a success');


		$simple_metadata = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
			'test', array('id')
		);
		$simple_table = new \PHPUnit_Extensions_Database_DataSet_DefaultTable($simple_metadata);
		$simple_dataset = new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array($simple_table));
		$actual_dataset = $this->getActualDataSet(array('test'));
		$this->assertDataSetsEqual(
			$simple_dataset,
			$actual_dataset,
			'Expected test table'
		);
		$this->assertTableRowCount('migrations', 1, 'Expected one migration entry');
	}

	public function testManagerExecutesTwoCreateTableInOneStatement()
	{
		$result = $this->executeMigration('simple.sql', 'CREATE TABLE foo (id int); CREATE TABLE bar (id int);');

		$this->assertTrue($result, 'Expected a success');

		$simple_metadata1 = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
			'foo', array('id')
		);
		$simple_table1 = new \PHPUnit_Extensions_Database_DataSet_DefaultTable($simple_metadata1);
		$simple_metadata2 = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData(
			'bar', array('id')
		);
		$simple_table2 = new \PHPUnit_Extensions_Database_DataSet_DefaultTable($simple_metadata2);
		$simple_dataset = new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array($simple_table1, $simple_table2));
		$actual_dataset = $this->getActualDataSet(array('foo', 'bar'));
		$this->assertDataSetsEqual(
			$simple_dataset,
			$actual_dataset,
			'Expected the two test tables'
		);
		$this->assertTableRowCount('migrations', 1, 'Expected one migration entry');
	}

	public function testExecutingManagerWithInvalidSql()
	{
		$result = $this->executeMigration('simple.sql', 'CREATE TABLE test;');
		$this->assertExecutionFailure($result);
	}

	public function testExecutingManagerWithEmbeddedTransactionFails()
	{
		$result = $this->executeMigration('simple.sql', 'BEGIN; CREATE TABLE test; COMMIT;');
		$this->assertExecutionFailure($result);
	}

	protected function assertExecutionFailure($result)
	{
		$this->assertFalse($result, 'Expected a failure');

		$actual_dataset = $this->getActualDataSet($this->getConnection()->getMetaData()->getTableNames());
		$this->assertDataSetsEqual(
			$this->getMigrationsDataset(),
			$actual_dataset,
			'Expected test table'
		);
		$this->assertTableRowCount('migrations', 0, 'Expected no migration entry (because of failure)');
	}

	/**
	 * @param string|array $tables
	 * @return \PHPUnit_Extensions_Database_DataSet_QueryDataSet
	 */
	public function getActualDataSet($tables)
	{
		$actualDataset = new \PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
		foreach ((array)$tables as $table) {
			$actualDataset->addTable($table);
		}
		return $actualDataset;
	}

	/**
	 * Assert that a given table has a given amount of rows
	 *
	 * @param string $tableName Name of the table
	 * @param int $expected Expected amount of rows in the table
	 * @param string $message Optional message
	 */
	public function assertTableRowCount($tableName, $expected, $message = '')
	{
		$constraint = new \PHPUnit_Extensions_Database_Constraint_TableRowCount($tableName, $expected);
		$actual = $this->getConnection()->getRowCount($tableName);

		self::assertThat($actual, $constraint, $message);
	}
}