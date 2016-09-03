<?php

namespace TwentyFifth\Test\Constraint;

/**
 * Alternate implementation that circumvents the following issue with the original code
 * https://github.com/sebastianbergmann/dbunit/issues/16
 */
class DataSetIsEqual extends \PHPUnit_Extensions_Database_Constraint_DataSetIsEqual {
	protected function matches($other)
	{
		$value = $this->value;
		return $this->matchDatasets($other, $value);
	}

	/**
	 * Taken from PHPUnit_Extensions_Database_DataSet_AbstractDataSet::match
	 */
	private function matchDatasets(\PHPUnit_Extensions_Database_DataSet_IDataSet $other, \PHPUnit_Extensions_Database_DataSet_IDataSet $value)
	{
		$thisTableNames = $value->getTableNames();
		$otherTableNames = $other->getTableNames();

		sort($thisTableNames);
		sort($otherTableNames);

		if ($thisTableNames != $otherTableNames) {
			return false;
		}

		foreach ($thisTableNames as $tableName) {
			$table = $value->getTable($tableName);

			if (!$this->matchTables($table, $other->getTable($tableName))) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Taken from PHPUnit_Extensions_Database_DataSet_AbstractTable::match
	 */
	private function matchTables(\PHPUnit_Extensions_Database_DataSet_ITable $table, \PHPUnit_Extensions_Database_DataSet_ITable $other)
	{
		$thisMetaData  = $table->getTableMetaData();
		$otherMetaData = $other->getTableMetaData();

		if (!$this->matchMetadata($thisMetaData, $otherMetaData) ||
			$table->getRowCount() != $other->getRowCount()) {
			return false;
		}

		$columns  = $thisMetaData->getColumns();
		$rowCount = $table->getRowCount();

		for ($i = 0; $i < $rowCount; $i++) {
			foreach ($columns as $columnName) {
				$thisValue  = $table->getValue($i, $columnName);
				$otherValue = $other->getValue($i, $columnName);
				if (is_numeric($thisValue) && is_numeric($otherValue)) {
					if ($thisValue != $otherValue) {
						return false;
					}
				} elseif ($thisValue !== $otherValue) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Taken from PHPUnit_Extensions_Database_DataSet_AbstractTableMetaData::match
	 */
	private function matchMetadata(\PHPUnit_Extensions_Database_DataSet_ITableMetaData $data, \PHPUnit_Extensions_Database_DataSet_ITableMetaData $other) {
		$dataColumns = $data->getColumns();
		$otherColumns = $other->getColumns();

		// That's the fix
		sort($dataColumns);
		sort($otherColumns);
		// </fix>

		if ($data->getTableName() != $other->getTableName() ||
			$dataColumns != $otherColumns
		) {
			return false;
		}

		return true;
	}
}