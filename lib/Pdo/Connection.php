<?php
/**
 * Version: 1.0
 * Author: Harcharan Singh <artisangang@gmail.com>
 */

namespace POCS\Lib\PDO;


class Connection {

    protected $conn;

    public function __construct() {

        $defaultConn = config('connection');
        $credentials = config($defaultConn, []);

        $host = array_get($credentials, 'host', '127.0.0.1');
        $db = array_get($credentials, 'dbname', 'pocs');
        $username = array_get($credentials, 'username', 'root');
        $password = array_get($credentials, 'password', '');

        $this->conn = new \PDO("mysql:host={$host};dbname={$db}", $username, $password);
    }

}