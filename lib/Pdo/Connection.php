<?php
/**
 * Version: 1.0
 * Author: Harcharan Singh <artisangang@gmail.com>
 */

namespace POCS\Lib\PDO;


class Connection {

    /**
     * collection of connections
     */ 
    protected $conn = [];

    /**
     * create default connection
     */
    public function __construct() {

       $this->connect(config('connection'));        
    }

    protected function connect($key) {

        $credentials = config("connections.{$key}");

        if (!is_array($credentials)) {
            throw new \Exception("Connection $key doesn't exists.");
        }


        $host = array_get($credentials, 'host', '127.0.0.1');
        $db = array_get($credentials, 'dbname', 'pocs');
        $username = array_get($credentials, 'username', 'root');
        $password = array_get($credentials, 'password', '');

        $this->conn[$key] = new \PDO("mysql:host={$host};dbname={$db}", $username, $password);
        $this->conn[$key]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function exists($key) {
        return isset($this->conn[$key]);
    }

    public function get($key = null) {

        if (is_null($key)) {
            $key = config('connection');
        }

        if (isset($this->conn[$key])) {
            return $this->conn[$key];
        }

        $this->connect($key);

        return $this->conn($key);

    }

    public function purge($key = 'connection') {
        unset($this->conn[$key]);
    }

}