<?php

namespace TwentyFifth\Migrations\Manager;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use TwentyFifth\Migrations\Manager\FileManager;

class FileManagerTest
	extends \PHPUnit_Framework_TestCase
{

	/** @var vfsStreamDirectory */
	protected $root;

	public function setUp()
	{
		$this->root = vfsStream::setup('exampleDir');
	}

	/**
	 * @expectedException \TwentyFifth\Migrations\Exception\RuntimeException
	 */
	public function testFileManagerThrowsExceptionIfDirectoryDoesNotExist()
	{
		$fm = new FileManager('foo');
	}

	public function testFileManagerAcceptsValidDirectory()
	{
		vfsStream::create(array(
			'foo' => array(),
		));

		$fm = new FileManager($this->root->url() . '/foo');

		$this->assertInstanceOf('TwentyFifth\Migrations\Manager\FileManager', $fm);
	}

	public function testListGenerationOmitsNonSqlFilesAndDirectories()
	{
		vfsStream::create(array(
			'foo' => array(
				'.' => array(),
				'..' => array(),
				'bla' => 'empty',
				'foo.php' => 'empty',
				'bar' => array(),
			),
		));

		$fm = new FileManager($this->root->url() . '/foo');
		$result = $fm->getOrderedFileList();

		$this->assertEmpty($result);
	}

	/**
	 * @dataProvider provideListData
	 */
	public function testListGenerationWorks($files)
	{
		$directory_structure = array(
			'testDirectory' => $files,
		);

		$expected = array();
		foreach ($files as $filename => $content) {
			$expected[$filename] = $this->root->url() . '/testDirectory/'.$filename;
		}

		vfsStream::create($directory_structure);

		$fm = new FileManager($this->root->url() . '/testDirectory');

		$this->assertEquals($expected, $fm->getOrderedFileList());
	}

	/**
	 * @dataProvider provideListData
	 */
	public function testListGenerationOrdersCorrectly($files, $expectedOrder)
	{
		$directory_structure = array(
			'testDirectory' => $files,
		);

		vfsStream::create($directory_structure);

		$fm = new FileManager($this->root->url() . '/testDirectory');
		$result = $fm->getOrderedFileList();

		$this->assertEquals($expectedOrder, array_keys($result));
	}

	public function provideListData()
	{
		$datasets = array();

		// Empty dataset
		$datasets[] = array(
			array(),
			array(),
		);

		// Dataset with just one element
		$datasets[] = array(
			array('1_foo.sql' => 'empty'),
			array('1_foo.sql'),
		);

		// Dataset with more elements that have to be sorted
		$datasets[] = array(
			array('15_blub.sql' => 'empty', '1_foo.sql' => 'empty', '2_bar.sql' => 'empty'),
			array('1_foo.sql', '2_bar.sql', '15_blub.sql'),
		);

		// Dataset with story names which changes order
		$datasets[] = array(
			array('55_story15.sql' => 'empty', '55_story3.sql' => 'empty', '55_story001.sql' => 'empty'),
			array('55_story001.sql', '55_story3.sql', '55_story15.sql'),
		);

		return $datasets;
	}
}