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

            $this->config['mode'] = $env;
            if (!empty($this->config['env'][$env])) {

                $this->config = array_merge($this->config, $this->config['env'][$env]);
            }

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
        if (!empty($this->config[$key])) {
            $default = $this->config[$key];
        }
        return $default;
    }

}