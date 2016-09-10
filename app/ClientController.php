<?php namespace POCS\App;

use POCS\Core\ClientInterface;

use POCS\Core\Console;

use POCS\Core\Service;

class ClientController implements ClientInterface {


    // user is connecting
    public function connecting($id) {       
        Console::log("Client connecting with id: {$id}.");
    }

    // user is now connected
    public function connected($cid, $resource) {
         Console::log("Handshake done with #{$cid}");
        

        list(, $user, $id) = explode('/', $resource, 3);

        $user = Service::DB()->from('users')->where('id', $id)->first();
        

        if (!$user) {
          return Response::DISCONNECT;
        }

        Service::DB()->table('users')->where('id', $user->id)->update(['client_id' => $cid]);


        return "Your are connected to server with #[{$cid}]...";
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

        if (isset($payloads['receiver'], $payloads['uid'], $payloads['text'])) {
           
           $receiver = Service::DB()->from('users')->where('id', $payloads['receiver'])->first();
           $sender = Service::DB()->from('users')->where('client_id', $payloads['uid'])->first();

           $data = [
               'sender_id' => $sender->id,
               'receiver_id' => $receiver->id,
               'message' => $payloads['text'],

           ];

           $payloads['from'] = $sender->username;

           $payloads['cid'] = $receiver->client_id;

           unset($payloads['uid']);

           Service::DB()->table('chat_history')->insert($data);
        
           return $payloads;
            
        }

        return false;
    }



} 