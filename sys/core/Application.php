<?php namespace POCS\Core;

use \POCS\Core\Config;
use \POCS\Core\Console\Request;

class Application
{
    protected $ip;

    protected $port;

    protected $request;

    protected $config;

    protected $socket;

    protected $clients;

    protected $connectionController;

     /**
      * @param array  $arguments
      * create application
      */
    public function __construct(array $arguments)
    {

        set_exception_handler([$this, 'exception']);

        $this->request = Request::instance($arguments);

        $this->config = Config::instance();

        $this->ip = $this->request->options('ip', $this->config->get('ip', '0.0.0.0'));
        $this->port = $this->request->options('port', $this->config->get('port', 9000));

         $controllerClass = config('controller');

        $this->connectionController = new $controllerClass();

        if (!$this->connectionController instanceof ConnectionInterface) {
            throw new \RuntimeException('Invalid controller attached.');
        }

     

    }

    /**
     * @return $this->connectionController
     * connection controller is attached in config
     */
    public function controller() {
        return $this->connectionController;
    }

    /**
     * exception handler method
     */
    public function exception($exception) {
        $type = get_class($exception);
        echo "[{$type}] : {$exception->getMessage()}.\n";
    }

    /**
     * @return \POCS\Core\Console\Request
     */
    public function request() {
        return $this->request;
    }


    /**
     * @return mixed
     */
    public function ip() {
        return $this->ip;
    }

    /**
     * @return mixed
     */
    public function port() {
        return $this->port;
    }
   

}