<?php namespace POCS\Core;

class Server
{
    protected $ip;

    protected $port;

    protected $console;

    protected $socket;

    protected $clients;

    public function __construct(Console $console)
    {

        $this->ip = config('ip', '0.0.0.0');
        $this->port = config('port', 1300);

        $this->console = $console;
        $this->socket = new Socket($this);

       $this->socket->create();

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

        Console::log("Listening on {$this->ip}:{$this->port}...");

        while(true) {
            // cycle
            $this->cycle();

            $this->socket->select();

        }

    }

}