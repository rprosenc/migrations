<?php

namespace TwentyFifth\Migrations\Command;

use TwentyFifth\Migrations\Manager\ConfigManager\ConfigInterface;
use TwentyFifth\Migrations\Manager\SchemaManager;
use TwentyFifth\Migrations\Manager\FileManager;

use Symfony\Component\Console;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand
    extends Console\Command\Command
{
    /* @var ConfigInterface */
    private $config_manager;

    /** @var SchemaManager */
    protected $schema_manager;

    /** @var FileManager */
    protected $file_manager;

    protected $errors = array();

    public function __construct(ConfigInterface $configManager, FileManager $fileManager, $name = null)
    {
        parent::__construct($name);
        $this->config_manager = $configManager;
        $this->file_manager = $fileManager;

        $this->addOption('database', null, InputOption::VALUE_REQUIRED, 'Override Database');
        $this->addOption('user', null, InputOption::VALUE_REQUIRED, 'Override User');
    }

    public function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        // override database
        $database = (string)$input->getOption('database');
        if (strlen($database) > 0) {
            $this->config_manager->setDatabase($database);
        }

        $username = (string)$input->getOption('user');
        if (strlen($username) > 0) {
            $this->config_manager->setUserName($username);
        }

        $this->schema_manager = new SchemaManager($this->config_manager);
    }

    protected function getMissingMigrations()
    {
        $all_migrations = $this->file_manager->getOrderedFileList();
        return $this->schema_manager->getNotAppliedMigrations($all_migrations);
    }
}