<?php
/**
 * Osimo v2 Database Class.
 * Used for querying a MySQL database.
 *
 * @author Ryan LeFevre
 * @see OsimoDBQuery
 */
class OsimoDB extends OsimoModule{
	private static $INSTANCE;

	protected $db_host;
	protected $db_user;
	protected $db_pass;
	protected $db_name;
	protected $autoconnect;
	private $conn;
	private $conn_db;

	/**
	 * OsimoDB Constructor.
	 *
	 * @param Array $options (optional)
	 */
	private function OsimoDB() {
		parent::OsimoModule();
	}

	public function init($options=false) {
		$this->defaults = array(
			'db_host'=>'localhost',
			'db_user'=>'root',
			'db_pass'=>'password',
			'db_name'=>'database',
			'error_type'=>'error_log',
			'autoconnect'=>true
		);

		/* Register the databases debugging defaults */
		get('debug')->register('OsimoDB', array(
				'events'=>false,
				'queries'=>false,
				'benchmarking'=>false
			));

		$this->parseOptions($options);
		
		if ($this->autoconnect) {
			$this->connect();
		}
	}

	public static function instance() {
		if(is_null(self::$INSTANCE)) {
			self::$INSTANCE = new OsimoDB();
		}
		
		return self::$INSTANCE;
	}

	/**
	 * Connects to the database specified in the options.
	 * If autoconnect is enabled (which is is by default),
	 * this function will be called upon class instantiation.
	 */
	public function connect() {
		if (!$this->conn) { // don't open a new connection if one already exists
			$this->conn = @mysql_connect($this->db_host, $this->db_user, $this->db_pass) or die("Could not connect to database!");
			$this->conn_db = @mysql_select_db($this->db_name)or die("Could not select database!");
			get('debug')->logMsg('OsimoDB', 'events', 'Opening database connection...');

			$status = explode('  ', mysql_stat());
			get('debug')->logMsg('OsimoDB', 'events', "Current database status:\n".print_r($status, true));
		}
	}

	/**
	 * General escape function
	 * If given an array, will escape every value in the array
	 * The quotes option will put quotes around each item, useful
	 * for the WHERE clause of a SQL query.
	 * Note: this function is static.
	 *
	 * @param mixed $data
	 * @param boolean $quotes (optional)
	 * @return Escaped version of $data
	 */
	public static function escape($data, $quotes=false) {
		if (is_array($data)) {
			foreach ($data as $key=>$var) {
				if ($quotes && !is_numeric($data[$key])) {
					$data[$key] = "'".self::escape($data[$key])."'";
				}
				else {
					$data[$key] = self::escape($data[$key]);
				}
			}

			return $data;
		}
		else {
			if ($quotes && !is_numeric($data)) {
				return "'".mysql_real_escape_string($data)."'";
			}
			else {
				return mysql_real_escape_string($data);
			}
		}
	}

	/**
	 * General date formatting function.
	 * Will format any date for the DATETIME database fieldtype.
	 * Can take both timestamps and pre-formatted dates.  If $date is false,
	 * then it assumes the current time.
	 * Note: this function is static.
	 *
	 * @param mixed $date (optional)
	 * @return The date formatted for MySQL DATETIME format.
	 */
	public static function formatDateForDB($date=false) {
		if (!$date) { $date = time(); }
		elseif (!is_numeric($date)) { $date = strtotime($date); }
		return date('Y-m-d H:i:s', $date);
	}



	/**
	 * Echos any errors reported by MySQL from the 
	 * last executed query.
	 */
	public function get_error() {
		echo mysql_error();
	}

	# Now starts the tasty stuff...
	# These are the various starts to a SQL query.
	# The functions return an OsimoDBQuery object. 

	/**
	 * Begins a SELECT MySQL query chain.
	 *
	 * @param mixed $args (optional)
	 * @return new OsimoDBQuery object
	 */
	public function select($args=false) {
		get('debug')->logMsg('OsimoDB', 'events', "Starting SELECT query.");
		return new OsimoDBQuery('select', $args, $this);
	}

	/**
	 * Begins an INSERT MySQL query chain.
	 *
	 * @param mixed $args
	 * @return new OsimoDBQuery object
	 */
	public function insert($args) {
		get('debug')->logMsg('OsimoDB', 'events', "Starting INSERT query.");
		return new OsimoDBQuery('insert', $args, $this);
	}

	/**
	 * Begins an UPDATE MySQL query chain.
	 *
	 * @param mixed $args
	 * @return new OsimoDBQuery object
	 */
	public function update($args) {
		get('debug')->logMsg('OsimoDB', 'events', "Starting UPDATE query.");
		return new OsimoDBQuery('update', $args, $this);
	}

	/**
	 * Begins a generic MySQL query chain.
	 * This function is primarily used when the developer
	 * wishes to write the whole SQL query as a string instead
	 * of building the query using the chaining functions.
	 *
	 * @param String $query
	 * @return new OsimoDBQuery object
	 */
	public function query($query) {
		get('debug')->logMsg('OsimoDB', 'events', "Starting generic query.");
		return new OsimoDBQuery('query', $query, $this);
	}



	/**
	 * Begins a DELETE MySQL query chain.
	 *
	 * @return new OsimoDBQuery object
	 */
	public function delete() {
		get('debug')->logMsg('OsimoDB', 'events', "Starting DELETE query.");
		return new OsimoDBQuery('delete', false, $this);
	}


}

/**
 * Database query class.
 * Instantiated object represents a SQL query
 * that can be executed to retrieve data from
 * a MySQL database.
 *
 * @author Ryan LeFevre
 * @see OsimoDB
 */
class OsimoDBQuery {
	private $db;
	private $type;
	private $fields;
	private $tables;
	private $joins;
	private $where;
	private $query;
	private $order_by;

	/**
	 * Constructor for OsimoDBQuery class.
	 * This should strictly be called from the OsimoDB class.
	 *
	 * @param String $type
	 * @param mixed $args
	 *		Can be Array or String with comma separated values
	 * @param OsimoDB $db
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	function OsimoDBQuery($type, $args, $db) {
		$this->db = $db;
		$this->type = $type;
		$this->joins = array();
		$this->order_by = array();

		if ($this->processArgs($args)) {
			return $this;
		}

		return NULL;
	}

	private function processArgs($args) {
		if (empty($args) || !$args) {
			if ($this->type == 'select') {
				$this->fields = array("*");
			}
			elseif ($this->type == 'delete') {
				$this->fields = '';
			}
			elseif ($this->type == 'update') {
				trigger_error("OsimoDB: Missing table for DB UPDATE query", E_USER_ERROR);
				return NULL;
			}
			elseif ($this->type == 'insert') {
				trigger_error("OsimoDB: Missing field info for DB INSERT query", E_USER_ERROR);
			}
			else {
				trigger_error("OsimoDB: Invalid start of SQL query - missing arguments", E_USER_ERROR);
				return NULL;
			}
		}
		elseif (!is_array($args) && is_string($args)) {
			if ($this->type == 'select' || $this->type == 'delete' || $this->type == 'insert') {
				$this->fields = $this->trimData(explode(',', $args));
			}
			elseif ($this->type == 'query') {
				$this->fields = NULL;
				$this->query = $args;
			}
			else {
				$this->tables = $this->trimData(explode(',', $args));
			}
		}
		else {
			if ($this->type == 'select' || $this->type == 'delete' || $this->type == 'insert') {
				$this->fields = $args;
			}
			elseif ($this->type == 'update') {
				$this->tables = $args;
			}
			else {
				$this->tables = $args;
			}
		}

		return true;
	}

	/**
	 * Adds FROM clause to MySQL query.
	 *
	 * @param mixed $tables
	 *		Can be Array or String with comma separated values
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function from($tables) {
		if ($this->type != 'select' && $this->type != 'delete') {
			trigger_error("OsimoDB: Invalid SQL chain - from() not allowed for ".$this->type." queries", E_USER_ERROR);
			return NULL;
		}

		if (!is_array($tables) && is_string($tables)) {
			$this->tables = $this->trimData(explode(",", $tables));
		}
		else {
			$this->tables = $tables;
		}

		return $this;
	}

	/**
	 * Adds a SET clause to an UPDATE MySQL query.
	 *
	 * @param mixed $args
	 *		Can be Array or String of comma separated values.
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function set($args) {
		if($this->type != 'update'){
			trigger_error("OsimoDB: Invalid SQL chain - set() not allowed for ".$this->type." queries", E_USER_ERROR);
			return NULL;
		}
		
		if (is_array($args)) {
			$temp = array();
			foreach ($args as $field=>$val) {
				$temp[] = $field."=".$val."";
			}

			$this->fields = implode(",", $temp);
		}
		else {
			$this->fields = $args;
		}

		return $this;
	}

	/**
	 * Adds an INTO clause to an INSERT MySQL query.
	 *
	 * @param String $table
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function into($table) {
		if ($this->type != 'insert') {
			trigger_error("OsimoDB: Invalid SQL chain - into() not allowed for ".$this->type." queries", E_USER_ERROR);
			return NULL;
		}

		if (is_array($table)) {
			trigger_error("OsimoDB: Invalid datatype - into() can only take a string as an argument", E_USER_ERROR);
			return NULL;
		}

		$this->tables = $table;

		return $this;
	}

	/**
	 * Adds a LEFT [OUTER] JOIN clause to a SELECT MySQL query.
	 *
	 * @param String $table
	 *		The table to join on
	 * @param String $on
	 *		The join condition, aka the ON part of the JOIN clause
	 * @param boolean $outer (optional)
	 *		Should the join be an OUTER JOIN or not?
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function left_join($table, $on, $outer=false) {
		$outer ? $type = "LEFT OUTER" : $type = "LEFT";
		$this->save_join($type, $table, $on);

		return $this;
	}

	/**
	 * Adds a RIGHT [OUTER] JOIN clause to a SELECT MySQL query.
	 *
	 * @param String $table
	 *		The table to join on
	 * @param String $on
	 *		The join condition, aka the ON part of the JOIN clause
	 * @param boolean $outer (optional)
	 *		Should the join be an OUTER JOIN or not?
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function right_join($table, $on, $outer=false) {
		$outer ? $type = "RIGHT OUTER" : $type = "RIGHT";
		$this->save_join($type, $table, $on);

		return $this;
	}

	/**
	 * Adds a INNER JOIN clause to a SELECT MySQL query.
	 *
	 * @param String $table
	 *		The table to join on
	 * @param String $on
	 *		The join condition, aka the ON part of the JOIN clause
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function inner_join($table, $on) {
		$this->save_join("INNER", $table, $on);

		return $this;
	}

	/**
	 * Adds a OUTER JOIN clause to a SELECT MySQL query.
	 *
	 * @param String $table
	 *		The table to join on
	 * @param String $on
	 *		The join condition, aka the ON part of the JOIN clause
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function outer_join($table, $on) {
		$this->save_join("OUTER", $table, $on);

		return $this;
	}

	private function save_join($type, $table, $on) {
		if ($this->type != 'select') {
			trigger_error("OsimoDB: Invalid SQL chain - left_join() now allowed for ".$this->type." queries", E_USER_ERROR);
			return NULL;
		}

		$this->joins[] = "$type JOIN $table".$this->on($on);
	}

	private function on($on) {
		if (stripos($on, '=')===false) { //use USING syntax
			return " USING ($on) ";
		}
		else {
			return " ON ($on) ";
		}
	}

	/**
	 * Adds a WHERE clause to a MySQL query.
	 * This function takes a variable number of arguments,
	 * and works exactly like printf() or scanf().
	 *
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function where() {
		if (!func_num_args()) {
			trigger_error("OsimoDB: Missing arguments for where clause - ignoring", E_USER_NOTICE);
			return $this;
		}

		$this->where['str'] = func_get_arg(0);
		for ($i=1;$i<func_num_args();$i++) {
			$this->where['vars'][] = func_get_arg($i);
		}

		return $this;
	}

	/**
	 * Adds a LIMIT clause to a MySQL query.
	 *
	 * @param int $start
	 * @param mixed $num  (optional)
	 *		If $num isn't false, then the output is: LIMIT $start,$num
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function limit($start, $num=false) {
		$this->limit = $start;
		if ($num) {
			$this->limit .= ",$num";
		}

		return $this;
	}

	/**
	 * Adds an ORDER BY clause to a MySQL query.
	 * Can be of format: order_by(Array("col_name"=>"ASC"));
	 * or: order_by("col_name","ASC");
	 *
	 * @param mixed $cols
	 *		If type is Array, then $dir will be ignored.
	 * @param String $dir  (optional)
	 *		Can only be ASC or DESC.
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function order_by($cols, $dir=false) {
		if (is_array($cols)) {
			$this->order_by = $cols;
		}
		else {
			if ($dir == false) {
				trigger_error("OsimoDB: Missing ORDER BY direction for SQL query", E_USER_ERROR);
				return NULL;
			}
			else {
				$this->order_by[$cols] = $dir;
			}
		}

		return $this;
	}

	/**
	 *  Adds a VALUES clause to an INSERT MySQL query.
	 *
	 * @TODO This function should work similar to the where() function so that
	 * data can be automatically escaped.
	 *
	 * @param Array $args
	 * @return the current OsimoDBQuery object (allows chainability)
	 */
	public function values($args) {
		if ($this->type != 'insert') {
			trigger_error("OsimoDB: Invalid SQL chain - values() now allowed for ".$this->type." queries", E_USER_ERROR);
			return NULL;
		}
		if (count($args) % count($this->fields) != 0) {
			trigger_error("OsimoDB: Invalid SQL data - incorrect number of entries for values()", E_USER_ERROR);
			return NULL;
		}

		$this->values = $args;

		return $this;
	}

	/**
	 * Returns a single value/cell from a MySQL table.
	 *
	 * @param boolean $cache        (optional)
	 *		Should this value be cached or not?
	 * @param int $cache_length (optional)
	 *		If caching is enabled, how long should it be cached for?
	 * @return String of data returned from the MySQL query.
	 */
	public function cell($cache=false, $cache_length=300) {
		if ($cache) {
			get('debug')->logMsg('OsimoDB', 'events', 'Referring to OsimoCache for data with expire time of '.$cache_length.' seconds.');
			$data = reset(reset(get('cache')->sqlquery($this->query(false), $cache_length)));
			return $data;
		}

		$result = $this->query();
		if ($result && mysql_num_rows($result)>0) {
			return reset(mysql_fetch_row($result));
		}

		return false;
	}

	/**
	 * Returns a single row/tuple from a MySQL table.
	 *
	 * @param boolean $cache (optional)
	 *		Should this data be cached or not?
	 * @param int $cache_length (optional)
	 *		If caching is enabled, how long should it be cached for?
	 * @return One-dimensional array of data returned from the MySQL query.
	 */
	public function row($cache=false, $cache_length=300) {
		if (!isset($this->limit)) {
			$this->limit(1);
		}

		if ($cache) {
			get('debug')->logMsg('OsimoDB', 'events', 'Referring to OsimoCache for data with expire time of '.$cache_length.' seconds.');
			$data = reset(get('cache')->sqlquery($this->query(false), $cache_length));
			get('debug')->logMsg('OsimoDB', 'events', 'Using OsimoCache - '.$this->query);
			return $data;
		}

		$result = $this->query();
		if ($result && mysql_num_rows($result)>0) {
			return mysql_fetch_assoc($result);
		}

		return false;
	}

	/**
	 * Returns multiple rows/tuples from a MySQL table.
	 *
	 * @param boolean $cache (optional)
	 *		Should this data be cached or not?
	 * @param int $cache_length (optional)
	 *		If caching is enabled, how long should it be cached for?
	 * @return Two-dimensional array of data returned from the MySQL query.
	 */
	public function rows($cache=false, $cache_length=300) {
		if ($cache) {
			get('debug')->logMsg('OsimoDB', 'events', 'Referring to OsimoCache for data with expire time of '.$cache_length.' seconds.');
			$data = get('cache')->sqlquery($this->query(false), $cache_length);
			get('debug')->logMsg('OsimoDB', 'events', 'Using OsimoCache - '.$this->query);
			return $data;
		}

		$result = $this->query();
		if ($result && mysql_num_rows($result)>0) {
			$return = array();
			while ($data = mysql_fetch_assoc($result)) {
				$return[] = $data;
			}

			return $return;
		}

		return false;
	}

	/**
	 * Executes a MySQL INSERT query and places the last insert ID
	 * into the parameter $insertID.
	 *
	 * @param int $insertID (reference)
	 *		The last insert ID returned by mysql_insert_id();
	 * @return Boolean reflecting the query success.
	 */
	public function insert(&$insertID=false) {
		$result = $this->query();
		if ($result) {
			$insertID = mysql_insert_id();
			return true;
		}

		return false;
	}

	/**
	 * Executes a MySQL UPDATE query.
	 *
	 * @return Boolean reflecting the query success.
	 */
	public function update() {
		$result = $this->query();
		if ($result) {
			return true;
		}

		return false;
	}
	
	public function delete(&$num) {
		$result = $this->query();
		if($result) {
			$num = mysql_num_rows();
			return true;
		}
	
		return false;
	}

	/**
	 * Executes a generic MySQL query, and also executes
	 * the queries for the helper functions above.
	 *
	 * @param boolean $run (optional)
	 *		Should the function execute the query or simply return the SQL String?
	 * @param int $cache (optional)
	 *		Should this data be cached or not?
	 * @param int $cache_length (optional)
	 *		If caching is enabled, how long should it be cached for?
	 * @return If $run is true, will return the MySQL result resource,
	 * otherwise it will return the SQL query without executing it.
	 */
	public function query($run=true, $cache=false, $cache_length=300) {
		$starttime = microtime(true);
		get('debug')->timerStart('OsimoDB', $starttime);

		if (!$this->queryValidator()) {
			return NULL;
		}

		if ($this->type != 'query') {
			$this->queryBuilder();
		}

		if ($run && $cache && CACHE_TYPE == 'memcache') {
			return get('cache')->sqlquery($this->query, $cache_length);
		}

		if ($run) {
			$result = mysql_query($this->query);
			get('debug')->logMsg('OsimoDB', 'queries', $this->query);
			get('debug')->timerEnd('OsimoDB', $starttime, "Query built and executed in ");
			return $result;
		}

		return $this->query;
	}

	private function queryBuilder() {
		if ($this->type == 'select' || $this->type == 'delete') {
			/* SELECT/DELETE fields */
			$query = strtoupper($this->type) . ' ';
			if (is_array($this->fields)) {
				$this->fields = OsimoDB::escape($this->fields);
				$query .= implode(',', $this->fields);
			}

			/* FROM tables */
			$query .= ' FROM ';
			$query .= implode(',', $this->tables);

			/* Table joins */
			if (count($this->joins) > 0) {
				$query .= ' '.implode(" ", $this->joins);
			}

			/* WHERE statement */
			if (is_array($this->where)) {
				$query .= ' WHERE '.$this->parseWhere().' ';
			}

			if ($this->type == 'select' && count($this->order_by) > 0) {
				$query .= ' ORDER BY ';
				foreach ($this->order_by as $col=>$dir) {
					$temp[] = "$col $dir";
				}
				$query .= implode(",", $temp);
			}

			/* LIMIT statement */
			if (isset($this->limit)) {
				$query .= ' LIMIT '.$this->limit;
			}
		}
		elseif ($this->type == 'update') {
			/* UPDATE tables */
			$query = strtoupper($this->type) . ' ';
			$query .= implode(",", $this->tables);

			/* SET fields */
			$query .= ' SET ';
			$query .= $this->fields;

			/* WHERE statement */
			if (is_array($this->where)) {
				$query .= ' WHERE '.$this->parseWhere().' ';
			}

			/* LIMIT statement */
			if (isset($this->limit)) {
				$query .= ' LIMIT '.$this->limit;
			}
		}

		$this->query = $query;
		return $this->query;
	}

	private function parseWhere() {
		if (isset($this->where['vars'])) {
			return call_user_func_array(
				'sprintf',
				array_merge(
					(array) $this->where['str'],
					OsimoDB::escape($this->where['vars'], true)
				)
			);
		}

		return $this->where['str'];
	}

	private function queryValidator() {
		if ($this->type == 'query') { return true; }
		if ($this->type == 'select' || $this->type == 'update') {
			if (!$this->fields || empty($this->fields)) {
				trigger_error("OsimoDB: Missing fields for query statement", E_USER_ERROR);
				return false;
			}
		}

		if (!$this->tables || empty($this->tables)) {
			trigger_error("OsimoDB: Missing table declaration", E_USER_ERROR);
			return false;
		}

		return true;
	}

	private function trimData($data) {
		if (is_array($data)) {
			foreach ($data as $key=>$val) {
				$data[$key] = $this->trimData($data[$key]);
			}

			return $data;
		}

		return trim($data);
	}

	/**
	 * Echoing this class (without executing the query)
	 * will output the built SQL query itself.
	 */
	public function __toString() {
		return $this->query(false);
	}
}


?>