<?php

namespace TwentyFifth\Migrations\Manager;

use \TwentyFifth\Migrations\Exception;

class FileManager
{

	private $directory;

	public function __construct($directory)
	{
		if (!is_dir($directory)) {
			throw new Exception\RuntimeException('Directory does not exist');
		}

		$this->directory = $directory;
	}

	public function getOrderedFileList()
	{
		$directory_content = glob($this->directory . '*');

		foreach ($directory_content as &$file) {
			$file = basename($file);
		}
		unset($file);

		natsort($directory_content);

		$files = array();

		foreach ($directory_content as $file) {
			$files[$file] = $this->directory . $file;
		}

		return $files;
	}
}