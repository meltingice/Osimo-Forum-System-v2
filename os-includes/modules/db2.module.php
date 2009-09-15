<?
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
	
	public function OsimoDB($options=false){
		$this->defaults = array(
			'db_host'=>'localhost',
			'db_user'=>'root',
			'db_pass'=>'password',
			'db_name'=>'database',
			'error_type'=>'error_log',
			'autoconnect'=>true
		);

		$this->parseOptions($options);
		$this->init();
	}
	
	private function init(){
		if($this->autoconnect){
			$this->connect();
		}
	}
	
	public function connect(){
		if(!$this->conn){
			$this->conn = @mysql_connect($this->db_host, $this->db_user, $this->db_pass) or $this->error("Could not connect to database!",'crit');
			$this->conn_db = @mysql_select_db($this->db_name)or $this->error("Could not select database!",'crit');
		}
	}
	
	/*
	 * General escape function
	 * If given an array, will escape every value in the array
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
			if($quotes && !is_numeric($data[$key])){
				return "'".mysql_real_escape_string($data)."'";
			}
			else{
				return mysql_real_escape_string($data);
			}
		}
	}
	
	/* Now starts the tasty stuff... */
	public function select($args=false){
		return new OsimoDBQuery('select',$args);
	}
	
	public function insert($args){
		return new OsimoDBQuery('insert',$args);
	}
}

class OsimoDBQuery{
	private $type;
	private $fields;
	private $tables;
	private $where;
	private $query;
	
	function OsimoDBQuery($type,$args){
		$this->type = $type;
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
			else{
				trigger_error("OsimoDB: Invalid start of SQL query - missing arguments",E_USER_ERROR);
				return NULL;
			}
		}
		elseif(!is_array($args) && is_string($args)){
			if($this->type == 'select' || $this->type == 'delete'){
				$this->fields = $this->trimData(explode(',',$args));
			}
			else{
				$this->tables = $this->trimData(explode(',',$args));
			}
		}
		else{
			if($this->type == 'select' || $this->type == 'delete'){
				$this->fields = $args;
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
	
	/*
	 * Returns a single row from a mysql table
	 * Return example:
	 * Array(
	 *	id => 1,
	 *	username => "user"
	 * )
	 */
	public function row(){
		$result = $this->query();
		if($result && mysql_num_rows($result)>0){
			return mysql_fetch_assoc($result);
		}
		
		return false;
	}
	
	/*
	 * Returns a single value/cell from a mysql table
	 * Return is *not* an array
	 */
	public function cell($query){
		$result = $this->query();
		if($result && mysql_num_rows($result)>0){
			return reset(mysql_fetch_row($result));
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
	public function rows(){
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
	
	public function query($run=true){
		if(!$this->queryValidator()){
			return NULL;
		}
		
		$this->queryBuilder();
		
		if($run){
			return mysql_query($this->query);
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
			
			/* WHERE statement */
			if(is_array($this->where)){
				$query .= ' WHERE '.$this->parseWhere().' ';
			}
		}
		
		$this->query = $query;
		return $this->query;
	}
	
	private function parseWhere(){
		return call_user_func_array(
			'sprintf',
			array_merge(
				(array) $this->where['str'],
				OsimoDB::escape($this->where['vars'],true)
			)
		);
	}
	
	private function queryValidator(){
		if($this->type == 'select'){
			if(!$this->fields || empty($this->fields)){
				trigger_error("OsimoDB: Missing fields for SELECT statement",E_USER_ERROR);
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