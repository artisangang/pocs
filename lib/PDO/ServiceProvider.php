<?php namespace POCS\Lib\PDO;

use \POCS\Core\ServiceInterface;

class ServiceProvider implements ServiceInterface {

	public $alias = 'DB';


	public function boot() {

	}

	public function register() {

		$connection = new Connection();	

		$query = new QueryBuilder($connection);

		return $query;

	}

}