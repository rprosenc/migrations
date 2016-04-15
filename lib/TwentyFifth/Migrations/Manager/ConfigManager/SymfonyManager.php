<?php
/**
 * Created by IntelliJ IDEA.
 * User: rknoll
 * Date: 30/03/16
 * Time: 14:18
 */

namespace TwentyFifth\Migrations\Manager\ConfigManager;


use Symfony\Component\Yaml\Yaml;

class SymfonyManager implements ConfigInterface
{

    private $parameters;

    /**
     * SymfonyManager constructor.
     * @param $application_path
     */
    public function __construct($application_path)
    {
        $this->parameters = Yaml::parse(file_get_contents($application_path))['parameters'];
    }

    /**
     * @return string Hostname
     */
    public function getHost()
    {
        return $this->parameters['database_host'];
    }

    /**
     * @return string Port
     */
    public function getPort()
    {
        return $this->parameters['database_port'];
    }

    /**
     * @return string Database name
     */
    public function getDatabase()
    {
        return $this->parameters['database_name'];
    }

    /**
     * @param string $database
     *
     * @return ConfigInterface
     */
    public function setDatabase($database)
    {
        $this->parameters['database_name'] = $database;
    }

    /**
     * @return string Username
     */
    public function getUsername()
    {
        return $this->parameters['database_user'];
    }

    /**
     * @return string Password
     */
    public function getPassword()
    {
        return $this->parameters['database_password'];
    }
}