<?php namespace POCS\Core;

class ClientController implements ClientInterface {


    // user is connecting
    public function connecting($id) {       
        Console::log("Client connecting with id: {$id}.");
    }

    // user is now connected
    public function connected($id, $resource) {
         Console::log("Handshake done with #{$id}");

         return "Your are connected to server with #[{$id}]...";
    }

    // user is disconnected
    public function disconnected($id, $resource) {
         Console::log("Client #{$id} disconnected.");
    }
    
    // receiving data
    public function receiving($id, $payloads) {

    }

    // user sending data
    public function sending($id, $payloads) {
        Console::log("sending data : " . json_encode($payloads));
    }

    // user data sent
    public function sent($id, $payload) {

    }

    // user received some data
    public function received($id, $payloads) {

        Console::log("Recived data : " . json_encode($payloads));

        if (isset($payloads['cid'], $payloads['uid'], $payloads['text'])) {
            return $payloads;
            
        }

        return false;
    }



} 