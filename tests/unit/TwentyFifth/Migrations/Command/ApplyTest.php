<?php

namespace TwentyFifth\Migrations\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use TwentyFifth\Migrations\Exception\RuntimeException;
use TwentyFifth\Migrations\Manager\ConfigManager\ConfigInterface;
use TwentyFifth\Migrations\Manager\FileManager;

class ApplyTest extends \PHPUnit_Framework_TestCase
{
	/** @var ConfigInterface */
	private $cm;
	/** @var FileManager */
	private $fm;

	/** @var Apply */
	private $command;

	protected function setUp()
	{
		$this->cm = $this->createMock('TwentyFifth\Migrations\Manager\ConfigManager\ConfigInterface');
		$this->fm = $this->createMock('TwentyFifth\Migrations\Manager\FileManager');

		$this->command = new Apply($this->cm, $this->fm);
	}

	public function testErroneousExitCodeWithRuntimeException() {
		$input = new ArrayInput(['what' => 'next'], $this->command->getDefinition());
		$output = new ConsoleOutput();
		$this->cm->method('getHost')
			->will($this->throwException(new RuntimeException()));

		$exitCode = $this->command->execute($input, $output);

		$this->assertGreaterThan(0, $exitCode);
	}

}