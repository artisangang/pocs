<?php namespace POCS\Core;

class Client {

    private $id;
    private $socket;
    private $handshake;
    private $isConnected;
    private $headers = [];

    public function __construct($id, $socket) {
        $this->id = $id;
        $this->socket = $socket;
        $this->handshake = false;
        $this->isConnected = true;
    }

    public function getId() {
        return $this->id;
    }

    public function getSocket() {
        return $this->socket;
    }

    public function getHandshake() {
        return $this->handshake;
    }

    public function isConnected() {
        return $this->isConnected;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setHeaders() {

    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setSocket($socket) {
        $this->socket = $socket;
    }

    public function setHandshake($handshake) {
        $this->handshake = $handshake;
    }

    public function setIsConnected($isConnected) {
        $this->isConnected = $isConnected;
    }

}