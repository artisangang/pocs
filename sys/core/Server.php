<?php namespace POCS\Core;

use \POCS\Core\Console\IO as Log;

class Server
{
  
    protected $app;

    protected $socket;

    protected $connectionController;

    /**
     * @param \POCS\Core\Application $app
     * server constructor
     */
    public function __construct(Application $app)
    {

        $this->app = $app;
        
        $this->socket = new Socket($app);
      
   
    }

 
    /**
     * start listening connections
     */
    public function lift()
    {

        Log::info('POCS (PHP Open Chat Server) version: ' . VER);
        Log::info('By World Open Source Development Community');
        Log::info('Author: Harcharan Singh<artisangang@gmail.com>');
        Log::info('Contributors: Nitin Mehra <mb9034215256@gmail.com>');
        
        Log::info("Listening on {$this->app->ip()}:{$this->app->port()}...");

        while(true) {

            // process sockets
            $this->socket->process();
           
        }

    }

}