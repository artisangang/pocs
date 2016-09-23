<?php  namespace POCS\Core;

class Service {

	protected static $instance;

	protected $services = [];

	/**
     * @param array $services
     * register all service providers
     */
	private function __construct(array $services) {

		foreach( $services as $service ) {

			// main  method to register service provider
			$this->add(new $service());
		}

	}	

	/**
	 * @param \POCS\Core\ServiceInterface $provider
     * register service provider
     */
	protected function add(ServiceInterface $provider) {
		
		$this->services[$provider->alias] = $provider->register();
			
	}
	
	/**
	 * @param array $services
     * single instance provider
     */

	public static function instance(array $services = []) {

		if (null === static::$instance) {
				static::$instance = new static($services);
		}

		return static::$instance;

	}

	/**
	 * @param $alias
     * get service provider by alias
     */
	public function get($alias) {

		if (!isset($this->services[$alias])) {
			throw new \RuntimeException("Unknown service $alias");
		}

		return ($this->services[$alias] instanceof \Closure) ? $this->services[$alias]() : $this->services[$alias];

	}

	/**
	 * @param $method
	 * @param array $arguments
     * calling service provider methods
     */
	public static function __callStatic($method, array $arguments = []) {

		return static::instance()->get($method);

	}

}