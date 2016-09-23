<?php namespace POCS\Lib\PDO;

class Model {
	
	protected $attributes = [];

	public function __construct(array $attributes = []) {

	}

	public function __get($key) {

		$func = str_camel($key, false);

		$func = "get{$func}Attribute";
		if (is_callable([$this, $func])) {
			return call_user_func([$this, $func]);
		}

		return $this->getAttribute($key);

	}

	public function getAttribute($key) {
		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}

	public function __set($key, $value) {

		$func = str_camel($key, false);

		$func = "set{$func}Attribute";
		if (is_callable([$this, $func])) {
			return call_user_func_array([$this, $func], [$value]);
		}

		$this->attributes[$key] = $value;

	}

	public function toArray() {
		$array = [];

		foreach (array_keys($this->attributes) as $key) {
			$array[$key] = $this->__get($key);
		}

		return $array;
	}

	public function __toString() {
		return json_encode($this->attributes);
	}

}