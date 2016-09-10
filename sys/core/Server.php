<?php namespace POCS\Core;

class Server
{
    protected $ip;

    protected $port;

    protected $console;

    protected $socket;

    protected $clients;

    protected $clientController;

    public function __construct(Console $console)
    {


        $console->info('POCS (PHP Open Chat Server) version: ' . VER);
        $console->info('By World Open Source Development Community');
        $console->info('Author: Harcharan Singh<artisangang@gmail.com>');
        $console->info('Contributors: Nitin Mehra <mb9034215256@gmail.com>');

        $this->ip = $console->options('ip', config('ip', '0.0.0.0'));
        $this->port = $console->options('port', config('port', 9000));

        $controller = config('controller.client', ClientController::class);



        if ($console->options('controller') !=  false) {

            $controllerOpt = $console->options('controller');

            if ($controllerOpt === 'default') {
                $controller = ClientController::class;
            } else {
                $controller = $controllerOpt;
            }



        }

        $this->clientController = new $controller();

        if (!$this->clientController instanceof ClientInterface) {
            throw new \RuntimeException('Client controller is invalid');
        }


        $this->console = $console;
        $this->socket = new Socket($this);

       $this->socket->create();

    }

    public function getClientController() {
        return $this->clientController;
    }


    public function getIp() {
        return $this->ip;
    }

    public function getPort() {
        return $this->port;
    }

    public function setClient(Client $client) {

        $this->clients[$client->getId()] = $client;

    }

    public function getClientById($id) {

        if (isset($this->clients[$id])) {
            return $this->clients[$id];
        }

        return false;
    }

    public function getClientBySocket($socket) {
        foreach($this->clients as $client)
            if($client->getSocket() == $socket) {
                Console::log("Client found: {$client->getId()}");
                return $client;
            }
        return false;
    }

    public function removeClient(Client $client) {
        if (isset($this->clients[$client->getId()])) {
            unset($this->clients[$client->getId()]);
        }
    }



    protected function cycle() {

    }



    public function lift()
    {

        $this->console->info("Listening on {$this->ip}:{$this->port}...");

        while(true) {
            // cycle
            $this->cycle();

            $this->socket->select();

        }

    }

}