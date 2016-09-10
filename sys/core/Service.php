<?php  namespace POCS\Core;

class Service {

	protected static $instance;

	protected $services = [];

	private function __construct(array $services) {

		foreach( $services as $service ) {
			$this->add(new $service());
		}

	}	

	protected function add(ServiceInterface $provider) {
		
		$this->services[$provider->alias] = $provider->register();
			
	}
	
	public static function instance(array $services = []) {

		if (null === static::$instance) {
				static::$instance = new static($services);
		}

		return static::$instance;

	}

	public function get($alias) {

		if (!isset($this->services[$alias])) {
			throw new \RuntimeException("Unknown service $alias");
		}

		return $this->services[$alias];

	}

	public static function __callStatic($method, array $arguments = []) {

		return static::instance()->get($method);

	}

}