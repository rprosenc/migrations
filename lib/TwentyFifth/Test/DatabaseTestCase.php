<?php

namespace TwentyFifth\Test;

use TwentyFifth\Test\Constraint\DataSetIsEqual;

abstract class DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase   {

	public static function assertDataSetsEqual(\PHPUnit_Extensions_Database_DataSet_IDataSet $expected, \PHPUnit_Extensions_Database_DataSet_IDataSet $actual, $message = '')
	{
		$constraint = new DataSetIsEqual($expected);

		self::assertThat($actual, $constraint, $message);
	}
}