<?php namespace POCS\Core;

interface ClientInterface {

	  // user is connecting
    public function connecting();

    // user is now connected
    public function connected($cid, $resource);

    // user is disconnected
    public function disconnected();
    
    // receiving data
    public function receiving();

    // user sending data
    public function sending($payloads);

    // user data sent
    public function sent();

    // user received some data
    public function received();

}