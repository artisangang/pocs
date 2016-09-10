<?php

define('BASEDIR', __DIR__);


// get autoloader in
$autoload = require BASEDIR . '/sys/autoloader.php';


// get helpers in
require BASEDIR . '/sys/helpers.php';

$autoload->add('POCS\\Core\\', BASEDIR . '/sys/core/');
$autoload->add('POCS\\Lib\\', BASEDIR . '/lib/');

// register autoloader
$autoload->register();

$service = \POCS\Core\Service::instance([\POCS\Lib\PDO\ServiceProvider::class]);

// create config
$config = \POCS\Core\Config::instance();






 $data = [
               'sender_id' => 1,
               'receiver_id' => 2,
               'message' => "test"
           ];
           \POCS\Core\Service::DB()->table('chat_history')->insert($data);
