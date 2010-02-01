<?
/*
 * Osimo v2 Database Class
 * -------------------------------
 * Example usage (in Osimo):
 * 		get('db')->select(*)->from('users')->where('id=%d',3)->row();
 * Standalone usage:
 *		$db = new OsimoDB([$options]);
 *		$db->select(*)->from('users')->where('id=%d',3)->row();
 */
 
class OsimoDB extends OsimoModule{
	protected $db_host;
	protected $db_user;
	protected $db_pass;
	protected $db_name;
	protected $autoconnect;
	private $conn;
	private $conn_db;
	private $error = array();
	protected $error_type;
	protected $log_level;
	private $log;
	private $bench_start,$bench_end;
	
	public function OsimoDB($options=false){
		parent::OsimoModule();
		$this->defaults = array(
			'db_host'=>'localhost',
			'db_user'=>'root',
			'db_pass'=>'password',
			'db_name'=>'database',
			'error_type'=>'error_log',
			'autoconnect'=>true,
			'log_level'=>array(
				'events'=>false,
				'queries'=>false,
				'benchmarking'=>false
			)
		);

		$this->parseOptions($options);
		$this->init();
	}
	
	private function init(){
		if($this->autoconnect){
			$this->connect();
		}
		
		$this->log = array();
	}
	
	public function connect(){
		if(!$this->conn){ // don't open a new connection if one already exists
			$this->log('Opening database connection...','events');
			$this->conn = @mysql_connect($this->db_host, $this->db_user, $this->db_pass) or die("Could not connect to database!");
			$this->conn_db = @mysql_select_db($this->db_name)or die("Could not select database!");
		}
	}
	
	public function log($data,$type){
		if($this->log_level[$type]){
			$this->log[] = ucwords($type).': '.$data;
		}
		
		return true;
	}
	
	public function log_bench($action){
		if(!$this->log_level['benchmarking']){ return true; }
		
		if($action=='start'){
			$this->bench_start = microtime();
		}
		elseif($action=='stop'){
			$this->bench_stop = microtime();
			$diff = $this->bench_stop - $this->bench_start;
			$this->log[] = "Query built and executed in $diff seconds.";
		}
		
		return true;
	}
	
	public function output_log($echo=true){
		if(!$echo){ return $this->log; }
		else{
			foreach($this->log as $log){
				echo $log.'<br />';
			}
		}
	}
	
	/*
	 * General escape function
	 * If given an array, will escape every value in the array
	 * The quotes option will put quotes around each item, useful
	 * for the WHERE clause of a SQL query.
	 * Note: this function is static.
	 */
	public static function escape($data,$quotes=false){
		if(is_array($data)){
			foreach($data as $key=>$var){
				if($quotes && !is_numeric($data[$key])){
					$data[$key] = "'".self::escape($data[$key])."'";
				}
				else{
					$data[$key] = self::escape($data[$key]);
				}
			}
			
			return $data;
		}
		else{
			if($quotes && !is_numeric($data)){
				return "'".mysql_real_escape_string($data)."'";
			}
			else{
				return mysql_real_escape_string($data);
			}
		}
	}
	
	/*
	 * General date formatting function
	 * Will format any date for the DATETIME database fieldtype
	 * Can take both timestamps and pre-formatted dates
	 * Note: this function is static.
	 */
	public static function formatDateForDB($date=false){
		if(!$date){ $date = time(); }
		elseif(!is_numeric($date)){ $date = strtotime($date); }
		return date('Y-m-d H:i:s', $date);
	}
	
	/* 
	 * Now starts the tasty stuff... 
	 * These are the various starts to a SQL query.
	 * The functions return an OsimoDBQuery object.
	 */
	public function select($args=false){
		$this->log("Starting SELECT query.",'events');
		return new OsimoDBQuery('select',$args,$this);
	}
	
	public function insert($args){
		$this->log("Starting INSERT query.",'events');
		return new OsimoDBQuery('insert',$args,$this);
	}
	
	public function update($args){
		$this->log("Starting UPDATE query.",'events');
		return new OsimoDBQuery('update',$args,$this);
	}
	
	public function query($query){
		$this->log("Starting generic query.",'events');
		return new OsimoDBQuery('query',$query,$this);
	}
	
	public function delete(){
		$this->log("Starting DELETE query.",'events');
		return new OsimoDBQuery('delete',false,$this);
	}
}

class OsimoDBQuery{
	private $db;
	private $type;
	private $fields;
	private $tables;
	private $joins;
	private $where;
	private $query;
	
	function OsimoDBQuery($type,$args,$db){
		$this->db = $db;
		$this->type = $type;
		$this->joins = array();
		
		if($this->processArgs($args)){
			return $this;
		}
		
		return NULL;
	}
	
	private function processArgs($args){
		if(empty($args) || !$args){
			if($this->type == 'select'){
				 $this->fields = array("*");
			}
			elseif($this->type == 'delete'){
				$this->fields = '';
			}
			elseif($this->type == 'update'){
				trigger_error("OsimoDB: Missing table for DB UPDATE query",E_USER_ERROR);
				return NULL;
			}
			else{
				trigger_error("OsimoDB: Invalid start of SQL query - missing arguments",E_USER_ERROR);
				return NULL;
			}
		}
		elseif(!is_array($args) && is_string($args)){
			if($this->type == 'select' || $this->type == 'delete'){
				$this->fields = $this->trimData(explode(',',$args));
			}
			elseif($this->type == 'query'){
				$this->fields = NULL;
				$this->query = $args;
			}
			elseif($this->type == 'update'){
				$this->tables = $this->trimData(explode(',',$args));
			}
			else{
				$this->tables = $this->trimData(explode(',',$args));
			}
		}
		else{
			if($this->type == 'select' || $this->type == 'delete'){
				$this->fields = $args;
			}
			elseif($this->type == 'update'){
				$this->tables = $args;
			}
			else{
				$this->tables = $args;
			}
		}
		
		return true;
	}
	
	public function from($tables){
		if($this->type != 'select' && $this->type != 'delete'){
			trigger_error("OsimoDB: Invalid SQL chain - from() not allowed for ".$this->type." queries",E_USER_ERROR);
			return NULL;
		}
		
		if(!is_array($tables) && is_string($tables)){
			$this->tables = $this->trimData(explode(",",$tables));
		}
		else{
			$this->tables = $tables;
		}
		
		return $this;
	}
	
	public function set($args){
		if(is_array($args)){
			$temp = array();
			foreach($args as $field=>$val){
				$temp[] = $field."='".$val."'";
			}
			
			$this->fields = implode(",",$temp);
		}
		else{
			$this->fields = $args;
		}
		
		return $this;
	}
	
	public function left_join($table,$on,$outer=false){
		$outer ? $type = "LEFT OUTER" : $type = "LEFT";
		$this->save_join($type,$table,$on);
		
		return $this;
	}
	
	public function right_join($table,$on,$outer=false){
		$outer ? $type = "RIGHT OUTER" : $type = "RIGHT";
		$this->save_join($type,$table,$on);
		
		return $this;
	}
	
	public function inner_join($table,$on){
		$this->save_join("INNER",$table,$on);
		
		return $this;
	}
	
	public function outer_join($table,$on){
		$this->save_join("OUTER",$table,$on);
		
		return $this;
	}
	
	private function save_join($type,$table,$on){
		if($this->type != 'select'){
			trigger_error("OsimoDB: Invalid SQL chain - left_join() now allowed for ".$this->type." queries",E_USER_ERROR);
			return NULL;
		}
		
		$this->joins[] = "$type JOIN $table".$this->on($on);
	}
	
	private function on($on){
		if(stripos($on,'=')===false){ //use USING syntax
			return " USING ($on) ";
		}
		else{
			return " ON ($on) ";
		}
	}
	
	public function where(){
		if(!func_num_args()){
			trigger_error("OsimoDB: Missing arguments for where clause - ignoring",E_USER_NOTICE);
			return $this;
		}
		
		$this->where['str'] = func_get_arg(0);
		for($i=1;$i<func_num_args();$i++){
			$this->where['vars'][] = func_get_arg($i);
		}
		
		return $this;
	}
	
	public function limit($start,$num=false){
		$this->limit = $start;
		if($num){
			$this->limit .= ",$num";
		}
		
		return $this;
	}
	
	/*
	 * Returns a single value/cell from a mysql table
	 * Return is *not* an array
	 */
	public function cell($cache=false,$cache_length=300){
		if($cache){
			$this->db->log('Referring to OsimoCache for data with expire time of '.$cache_length.' seconds.','events');
			$data = reset(reset(get('cache')->sqlquery($this->query(false),$cache_length)));
			$this->db->log('Using OsimoCache - '.$this->query,'queries');
			$this->db->log_bench('stop');
			return $data;
		}
		
		$result = $this->query();
		if($result && mysql_num_rows($result)>0){
			return reset(mysql_fetch_row($result));
		}
		
		return false;
	}
	
	/*
	 * Returns a single row from a mysql table
	 * Return example:
	 * Array(
	 *	id => 1,
	 *	username => "user"
	 * )
	 */
	public function row($cache=false,$cache_length=300){
		if(!isset($this->limit)){
			$this->limit(1);
		}
		
		if($cache){
			$this->db->log('Referring to OsimoCache for data with expire time of '.$cache_length.' seconds.','events');
			$data = reset(get('cache')->sqlquery($this->query(false),$cache_length));
			$this->db->log('Using OsimoCache - '.$this->query,'queries');
			$this->db->log_bench('stop');
			return $data;
		}
		
		$result = $this->query();
		if($result && mysql_num_rows($result)>0){
			return mysql_fetch_assoc($result);
		}
		
		return false;
	}
	
	/*
	 * Returns multiple rows from a mysql table
	 * Return example:
	 * Array(
	 *	0 => Array(
	 *		id => 1,
	 *		username => "user"
	 *		),
	 *	1 => Array(
	 *		id => 2,
	 *		username => "user2"
	 *		)
	 *	)
	 */
	public function rows($cache=false,$cache_length=300){
		if($cache){
			return get('cache')->sqlquery($this->query(false),$cache_length);
		}
		
		$result = $this->query();
		if($result && mysql_num_rows($result)>0){
			$return = array();
			while($data = mysql_fetch_assoc($result)){
				$return[] = $data;
			}
			
			return $return;
		}
		
		return false;
	}
	
	public function insert(&$insertID){
		$result = $this->query();
		if($result && mysql_num_rows($result)>0){
			$insertID = mysql_insert_id();
			return true;
		}
		
		return false;
	}
	
	public function update(){
		$result = $this->query();
		if($result){
			return true;
		}
		
		return false;
	}
	
	public function query($run=true,$cache=false,$cache_length=300){
		if($run){ $this->db->log_bench('start'); }
		
		if(!$this->queryValidator()){
			return NULL;
		}
		
		if($this->type != 'query'){
			$this->queryBuilder();
		}
		
		if($run && $cache){
			return get('cache')->sqlquery($this->query,$cache_length);
		}
		
		if($run){
			$result = mysql_query($this->query);
			$this->db->log($this->query,'queries');
			$this->db->log_bench('stop');
			return $result;
		}
		
		return $this->query;
	}
	
	private function queryBuilder(){
		if($this->type == 'select' || $this->type == 'delete'){
			/* SELECT/DELETE fields */
			$query = strtoupper($this->type) . ' ';
			if(is_array($this->fields)){
				$this->fields = OsimoDB::escape($this->fields);
				$query .= implode(',',$this->fields);
			}
			
			/* FROM tables */
			$query .= ' FROM ';
			$query .= implode(',',$this->tables);
			
			/* Table joins */
			if(count($this->joins) > 0){
				$query .= ' '.implode(" ",$this->joins);
			}
			
			/* WHERE statement */
			if(is_array($this->where)){
				$query .= ' WHERE '.$this->parseWhere().' ';
			}
			
			/* LIMIT statement */
			if(isset($this->limit)){
				$query .= ' LIMIT '.$this->limit;
			}
		}
		elseif($this->type == 'update'){
			/* UPDATE tables */
			$query = strtoupper($this->type) . ' ';
			$query .= implode(",",$this->tables);
			
			/* SET fields */
			$query .= ' SET ';
			$query .= $this->fields;
			
			/* WHERE statement */
			if(is_array($this->where)){
				$query .= ' WHERE '.$this->parseWhere().' ';
			}
			
			/* LIMIT statement */
			if(isset($this->limit)){
				$query .= ' LIMIT '.$this->limit;
			}
		}
		
		$this->query = $query;
		return $this->query;
	}
	
	private function parseWhere(){
		if(isset($this->where['vars'])){
			return call_user_func_array(
				'sprintf',
				array_merge(
					(array) $this->where['str'],
					OsimoDB::escape($this->where['vars'],true)
				)
			);
		}
		
		return $this->where['str'];
	}
	
	private function queryValidator(){
		if($this->type == 'query'){ return true; }
		if($this->type == 'select' || $this->type == 'update'){
			if(!$this->fields || empty($this->fields)){
				trigger_error("OsimoDB: Missing fields for query statement",E_USER_ERROR);
				return false;
			}
		}
		
		if(!$this->tables || empty($this->tables)){
			trigger_error("OsimoDB: Missing table declaration",E_USER_ERROR);
			return false;
		}
		
		return true;
	}
	
	private function trimData($data){
		if(is_array($data)){
			foreach($data as $key=>$val){
				$data[$key] = $this->trimData($data[$key]);
			}
			
			return $data;
		}
		
		return trim($data);
	}
	
	public function __toString(){
		return $this->query(false);
	}
}
?>