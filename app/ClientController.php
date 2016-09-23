<?php namespace POCS\App;

use POCS\Core\ConnectionInterface;

use POCS\Core\Console\IO as Log;

use POCS\Core\Service;

use \POCS\Core\Connection;

class ClientController implements ConnectionInterface {


    // user is connecting
    public function connected(Connection $connection) {  
      
    }

   

    // user is disconnected
    public function disconnected($connection) {
         Log::debug("Client #{$connection->resourceId()} disconnected.");
    }
    
   
    // user received some data
    public function received(Connection $connection) {

      $payload = $connection->payloads();


      switch ($payload->intent) {

        case 'authenticate':
          if (!$user = $this->authenticate($payload->uid)) {
            $connection->close();
          }
          Service::DB()->from('users')->where('id', $user->id)->update(['resource' => $connection->resourceId()]);
          $connection->assign('user', $user);
          $connection->write(['alert' => 'User successfully verified.', 'status' => 'VERIFIED']);
        break;

        case 'communicate':
          if (!$user = $connection->get('user', false)) {
            $connection->close();
          }

          $receiver = Service::DB()->from('users')->where('id', $payload->receiver_id)->first();
          $data = ['receiver_id' => $receiver->id, 'sender_id' => $user->id, 'message' => $payload->text];
          Service::DB()->table('chat_history')->insert($data);
          $connection->forward($receiver->resource, ['from' => $user->username, 'resource' => $user->resource, 'text' => $payload->text, 'sender_id' => $user->id]);
        break;

      }

       


    }

    public function authenticate($id) {
      return Service::DB()->from('users')->where('id', $id)->first();
    }



} 