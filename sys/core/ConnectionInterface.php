<?php namespace POCS\Core;

use \POCS\Core\Connection;

interface ConnectionInterface {


    // user is now connected
    public function connected(Connection $connection);

    // user is disconnected
    public function disconnected($connection);
    
    // user received some data
    public function received(Connection $connection);
   

}