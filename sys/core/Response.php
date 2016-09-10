<?php  namespace POCS\Core;

class Response {

	protected $body;

	protected $meta = [];

	protected $status;

	const CONNECT = 2000;

	const DISCONNECT = 1006;

	public function __construct() {

	}

	

	public function __toString() {

	}
	
}