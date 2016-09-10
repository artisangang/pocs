<?php namespace POCS\Core;

interface ClientInterface {

	  // user is connecting
    public function connecting($cid);

    // user is now connected
    public function connected($cid, $resource);

    // user is disconnected
    public function disconnected($cid, $resource);
    
    // receiving data
    public function receiving($cid, $payloads);

    // user sending data
    public function sending($cid, $payloads);

    // user data sent
    public function sent($cid, $payloads);

    // user received some data
    public function received($id, $payloads);

}