<?php namespace POCS\Lib\PDO;

class QueryBuilder {

	protected $conns;
	
	protected $defaultConn;

	public $table;

	public $select = [];

	public $where = [];

	public $order = [];

	public $group = [];

	public $limit = [];

	protected $data = [];

	protected $query;

	public function __construct(Connection $connection) {
		$this->conns = $connection;
		$this->defaultConn = $connection->get();
	}

	public function on($conn) {
		$this->defaultConn = $connection->get($conn);
	}

	// this function will make async request to database
	public function async(\Closure $callback) {

	}

	public function select() {

		$args = func_get_args();

		if (func_num_args() == 1) {
			if (is_array($args[0])) {
				$this->select = $args[0];
			} elseif (is_string($args[0])) {
				array_push($this->select, $args[0]);
			}
			return $this;
		}

		array_merge($this->select, $args);

		return $this;
	}

	public function table($table) {
		$this->table = $table;
		return $this;
	}

	public function from($table) {
		return $this->table($table);
	}

	public function first() {

		$this->take(1);
		$query = $this->process();

		$stmt = $this->defaultConn->prepare($query);

		$stmt->execute($this->data);

		$rows = $stmt->fetchAll(\PDO::FETCH_CLASS, Model::class);

		return (!empty($rows)) ? reset($rows) : $rows;
	}

	public function where($key, $operator = null, $value = null, $or = false) {

		if ($key instanceof \Closure) {

		}


		if (is_null($operator)) {
			$operator = '=';
		}

		if (!is_null($operator) && is_null($value)) {
			$value = $operator;
			$operator = '=';
		}

		if (is_array($key)) {
			foreach ($key as $field => $val) {
				$this->where($field, $operator, $val, $or);
			}

			return $this;
		}

		array_push($this->where, ['field' => $key, 'operator' => $operator, 'value' => $value, 'glue' => (($or === true) ? 'and' : 'or')]);

		return $this;

	}

	public function nestedWhere() {

	}

	public function take($val) {
		$limit['take'] = $val;
	}

	public function skip($val) {

		$limit['skip'] = $val;
	}

	public function orderBy($field, $order = 'desc') {
		array_push($this->order, ['field' => $field, 'order' => $order]);
		return $this;
	}

	public function all() {
		$query = $this->process();

		$stmt = $this->defaultConn->prepare($query);

		$stmt->execute($this->data);

		return $stmt->fetchAll(\PDO::FETCH_CLASS, Model::class);
	}

	public function query($query, $data) {
		$stmt = $this->defaultConn->prepare($query);
		$stmt->execute($data);
		return $stmt;
	}

	public function insert(array $data) {

		//if (array_keys($data) == array_keys(array_reverse($data))) {
			$fileds = implode(',', array_keys($data));
			$placeholders = implode(',', array_fill(0, count($data), '?'));

			$query = "insert into {$this->table}($fileds) values($placeholders)";
			$stmt = $this->defaultConn->prepare($query);

			$stmt->execute(array_values($data));
		//}
	}

	public function update(array $data) {


		//if (array_keys($data) == array_keys(array_reverse($data))) {

			

			
			$filedsArray = array_map(function ($key) {
				return "$key=?";

			}, array_keys($data));

			$this->data = array_values($data);
			
			$where = $this->processWhere();

			$fileds = implode(',', array_values($filedsArray) );
			
			$query = "update {$this->table} set {$fileds} $where";
			$stmt = $this->defaultConn->prepare($query);

			$stmt->execute($this->data);
		//}
	
	}

	protected function process() {

		if (empty($this->select)) {
			$this->select('*');
		}

		$select = implode(',', $this->select);
		$where = $this->processWhere();
		$order = $this->processOrder();
		$group = '';
		$limit = $this->processLimit();

		return "select {$select} from {$this->table} $where $order $group $limit";

	}

	protected function processLimit() {

		if (empty($this->limit) || empty($limit['take'])) {
			return '';
		}

	
		$skip = !empty($limit['skip']) ? $limit['skip'] : 0;
		$take = $limit['take'];
	

		return "limit {$skip}, {$take}";
	}

	protected function processWhere() {
		
		if (empty($this->where)) {
			return;
		}

		$build = 'where';

		foreach ($this->where as $where) {
			$build .= " {$where['field']} {$where['operator']} ? {$where['glue']}";
			array_push($this->data, $where['value']);
		}

		return trim(trim($build, 'and'), 'or');
	}

	protected function processOrder() {
		if (empty($this->order)) {
			return;
		}

		$build = 'order by';

		foreach ($this->order as $order) {
			$build .= " {$order['field']}  {$order['order']}";
		}

		return $build;
	}

	protected function processInsert() {

	}

	protected function processUpdate() {

	}

}