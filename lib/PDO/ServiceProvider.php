<?php namespace POCS\Lib\PDO;

use \POCS\Core\ServiceInterface;

class ServiceProvider implements ServiceInterface {

	public $alias = 'DB';


	public function boot() {

	}

	public function register() {

		$connection = new Connection();	

		return function () use($connection) {
			return new QueryBuilder($connection);
		};

		

	}

}