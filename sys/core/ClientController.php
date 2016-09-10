<?php namespace POCS\Core;

class ClientController implements ClientInterface {


    // user is connecting
    public function connecting() {


    }

    // user is now connected
    public function connected($cid, $resource) {
        Console::log("{$cid} has resource {$resource}");
    }

    // user is disconnected
    public function disconnected() {

    }
    
    // receiving data
    public function receiving() {

    }

    // user sending data
    public function sending($payloads) {
        Console::log("sending data : " . json_encode($payloads));
    }

    // user data sent
    public function sent() {

    }

    // user received some data
    public function received() {
    	
    }



} 