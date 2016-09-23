<?php namespace POCS\Core;

use \POCS\Core\Console\IO as Log;

class Socket {

    protected $maxBufferSize = 2048;

    protected $conn;

    protected $clients = [];

    protected $connections = [];

    protected $app;
    
    /**
     * @param \POCS\Core\Application $app
     * socket constructor
     */
    public function __construct(Application $app) {

        $this->conn = @stream_socket_server("tcp://{$app->ip()}:{$app->port()}", $errno, $errstr);;
        if (!$this->conn) {           
            throw new \RuntimeException("Unable to create socket: [$errno] $errstr.");
        }

        stream_set_blocking($this->conn, 0);

        $this->app = $app;

    }

    /**
     * @param $resource
     * @return mixed
     * get sonnection by client resource id
     */
    public function connection($resource) {
        return isset($this->connections[$resource]) ? $this->connections[$resource] : false;
    }

    /**
     * @param $resource
     * remove client and its connection
     */
    public function purge($resource) {
        unset($this->connections[$resource]);
        unset($this->clients[$resource]);
    }

    /**
     * @param $resource
     * @return \POCS\Core\Application
     */
    public function app() {
        return $this->app;
    }

    
    public function process() {

  
        if (empty($this->clients)) {
            $this->clients['master'] = $this->conn;
        }

       $read = $this->clients;

       $write = $except = null;

        $stream = @stream_select($read, $write, $except, 1);

        if ($stream) {

            foreach ($read as $client_id => $socket) {

                if ($socket == $this->conn) {
            	   
                   $newSocket = stream_socket_accept($socket);

                	if ($newSocket) {
                		//stream_set_blocking($newSocket, 0);
                        Log::debug('Creating new connection...');
                		
                        $c = new Connection($newSocket, $this);
                        $c->doHandshake();

                        $this->app->controller()->connected($c);

                        $this->clients[$c->resourceId()] = $newSocket;
                        $this->connections[$c->resourceId()] = $c;

                	} else {
                        Log::debug('Failed to accept socket.');
                    }
                } else {
                     
                     if (isset($this->connections[$client_id])) {
                         $c = $this->connections[$client_id];

                         $o = $c->read();
                         if ($o != '') {
                             $this->app->controller()->received($c);
                         }
                    } else {
                        Log::debug('Client does not exists#'.$client_id);
                    }
                }
            }
        }

    	

    }



   
}