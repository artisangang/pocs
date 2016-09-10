<?php namespace POCS\Core;


/**
 * Class Config
 * @package POCS\Core
 */
class Config
{

    /**
     * @var
     * Config class instance
     */
    private static $_instance;

    /**
     * @var array
     * config collection
     */
    protected $config = [];

    /**
     * @throws RuntimeException
     * get config in
     */
    private function __construct()
    {

        $connections = require BASEDIR . '/sys/config/connections.php';
        $config = require BASEDIR . '/sys/config/app.php';

        if (!is_array($connections) || !is_array($config)) {
            throw new RuntimeException('Config must be array.');
        }

        $this->config['connections'] = $connections;


        $this->config = array_merge($this->config, $config);



        if (!empty($this->config['env'])) {

            $env = Console::instance()->options('env', array_get($config, 'mode', 'dev'));

            if (!isset($this->config['env'][$env])) {
                throw new \InvalidArgumentException("Invalid environment {$env}");
            }

            $this->config['mode'] = $env;

            // merge with env config
            $this->config = array_merge($this->config, $this->config['env'][$env]);

            // remove env array from config
            unset($this->config['env']);

        }




    }

    /**
     * @return mixed
     * single instance provider
     */
    public static function instance()
    {

        if (null === static::$_instance) {
            static::$_instance = new static;
        }

        return static::$_instance;

    }

    /**
     * @param $key
     * @param null $default
     * @return null
     * get config
     */
    public function get($key, $default = null)
    {
        return array_get($this->config, $key, $default);
    }

}