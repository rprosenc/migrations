<?php

require_once 'PHPUnit/Util/Filter.php';

require_once 'PHPUnit/Extensions/Database/DataSet/AbstractDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTableIterator.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTable.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTableMetaData.php';

/**
 * A simple dataset based on an array
 *
 * @category TwentyFifth
 * @package Test
 * @subpackage Dataset
 */
class ArrayDataSet
	extends \PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{
	/**
	 * @var array
	 */
	protected $tables = array();

	/**
	 * @param array $data
	 */
	public function __construct(array $data = array())
	{
		foreach ($data AS $tableName => $rows) {
			$columns = array();
			if (isset($rows[0])) {
				$columns = array_keys($rows[0]);
			}

			$metaData = new \PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($tableName, $columns);
			$table = new \PHPUnit_Extensions_Database_DataSet_DefaultTable($metaData);

			foreach ($rows AS $row) {
				$table->addRow($row);
			}
			$this->tables[$tableName] = $table;
		}
	}

	/**
	 * @param bool $reverse
	 * @return \PHPUnit_Extensions_Database_DataSet_DefaultTableIterator
	 */
	protected function createIterator($reverse = FALSE)
	{
		return new \PHPUnit_Extensions_Database_DataSet_DefaultTableIterator($this->tables, $reverse);
	}

	/**
	 * @throws \TwentyFifth\Exceptions\InvalidArgumentException
	 * @param $tableName
	 * @return \PHPUnit_Extensions_Database_DataSet_DefaultTable
	 */
	public function getTable($tableName)
	{
		if (!isset($this->tables[$tableName])) {
			throw new \TwentyFifth\Exceptions\InvalidArgumentException("$tableName is not a table in the current database.");
		}

		return $this->tables[$tableName];
	}
}
