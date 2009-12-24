<?
class OsimoCache extends OsimoModule{
	private $memcache;
	protected $enabled;
	protected $prefix;
	protected $cache_addr;
	protected $cache_port;
	protected $allowed_cache;
	protected $cache_time;
	protected $debug;
	
	function OsimoCache($options=false){
		parent::OsimoModule();
		$this->defaults = array(
			'enabled'=>true,
			'prefix'=>'',
			'cache_addr'=>'localhost',
			'cache_port'=>11211,
			'cache_time'=>300,
			'debug'=>false
		);
		
		$this->parseOptions($options);
		
		if(class_exists('Memcache')){
			$this->memcache = new Memcache;
		}
		else{
			$this->enabled = false;
		}
		
		$this->init();
	}
	
	private function init(){
		if($this->enabled && is_array($this->cache_addr)){
			foreach($this->cache_addr as $addr){
				$this->memcache->addServer($addr, $this->cache_port);
			}
		}
		elseif($this->enabled){
			$this->memcache->connect($this->cache_addr,$this->cache_port);
		}
		
		// "db_table_name" => "memcache_obj_name"
		$this->allowed_cache = array(
			"post"=>"osimo_post",
			"thread"=>"osimo_thread",
			"users"=>"osimo_users",
			"forum"=>"osimo_forum",
			"social_siggen"=>"osimo_social_siggen",
			"social_status_updates"=>"osimo_statuses"
		);
	}
	
	public function options($option){
		isset($option['name']) ? $name = $option['name'] : $name = $option[0];
		isset($option['value']) ? $value = $option['value'] : $value = $option[1];
		if(!isset($this->defaults[$name])){ return false; }
		
		$this->$name = $value;
		return true;
	}
	
	public function debug($switch){
		if(is_bool($switch)){ $this->debug = $switch; }
	}
	
	public function sqlquery($query,$expire=false){
		if($query==''){ return false; }
		
		/* If cache is disabled, go straight to DB */
		if(!$this->enabled){
			$db_result = $this->db_query($query,'assoc');
			return $db_result;
		}
		
		$type = $this->parseQuery($query,$sql_type);
		if($cache_name = $this->queryTypeAllowed($type,$sql_type)){
			$this->debugMsg("query allowed, attempting memcache");
			$hash = $this->getHashKey($query,$cache_name);
			
			if(is_object($this->memcache)){
				$result = $this->memcache->get($hash);
			}
			else{ $result = false; }
			
			if(!$result){
				$db_result = $this->db_query($query,'assoc');
				if($db_result){
					if(is_object($this->memcache)){
						$this->cache($hash,$db_result,$expire);
					}
					$this->debugMsg("Returning SQL result");
					return $db_result;
				}
				else{
					$this->debugMsg("SQL query returned no results");
					return false;
				}
			}
			else{
				$this->debugMsg("Returning memcache result");
				return $result->data;
			}
		}
		else{
			$this->debugMsg("query not allowed, doing mysql\n");
			return $this->db_query($query,'assoc');
		}
	}
	
	public function getPosts($where=false,$sort=false,$limit=false){
		$query = "SELECT id FROM post";
		if($where){
			$query .= " WHERE $where";
		}
		if($sort){
			$query .= " ORDER BY $sort";
		}
		if($limit){
			$query .= ' LIMIT '.$limit;
		}
		
		$dbresult = $this->db_query($query,'assoc');
		if($dbresult){
			$posts = array();
			foreach($dbresult as $post){
				$post_id = $post['id'];
				if($this->enabled && is_object($this->memcache)){
					$tmp = $this->memcache->get($this->prefix."post_$post_id");
				}
				if($tmp){
					$posts[] = $tmp->data;
				}
				else{
					$tmp2 = $this->db_query("SELECT * FROM post WHERE id='$post_id' LIMIT 1",'assoc');
					if($tmp2){
						if(is_object($this->memcache)){
							$this->cache("post_$post_id",$tmp2[0]);
						}
						$posts[] = $tmp2[0];
					}
				}
			}
			
			if($sort){
				$this->orderBy($posts,$sort);
			}
			
			return $posts;
		}
		else{
			return false;
		}
	}
	
	public function updatePost($post_id,$options){
		global $osimo;
		if(!is_numeric($post_id) || !is_array($options)){ return false; }
		$user = $osimo->getLoggedInUser();
		if(!$user){ return false; }
		
		if($this->enabled && is_object($this->memcache)){
			$result = $this->memcache->get($this->prefix."post_$post_id");
		}
		
		if($result){
			$post = $result->data;
			
		}
		else{
			$db_query = "SELECT * FROM post WHERE id='$post_id'";
			if(!$osimo->userIsModerator()&&!$osimo->userIsAdmin()){
				$db_query .= " AND poster_id='{$user['ID']}'";
			}
			$db_query .= " LIMIT 1";
			$db_result = $this->db_query($db_query,'assoc');
			if($db_result){
				$post = $db_result[0];
			}
			else{
				return false;
			}
		}
		
		$update = "UPDATE post SET ";
		foreach($options as $key=>$val){
			$post[$key] = $val;
			$query_opt[] = $key."='".mysql_real_escape_string($val)."'";
		}
		$update .= implode(",",$query_opt);
		$update .= " WHERE id='$post_id' LIMIT 1";
		$db_result = $this->db_query($update);
		if($this->enabled && is_object($this->memcache)){
			$result = $this->cache("post_$post_id",$post);
		}
		
		return $result;
	}
	
	private function cache($key,$data,$expire=false){
		if(!is_object($this->memcache)){ return false; }
		$this->debugMsg("Storing item in cache: $key");
		$tmp = new stdClass;
		$tmp->data = $data;
		if($expire && is_numeric($expire)){
			$expire_time = $expire;
		}
		else{
			$expire_time = $this->cache_time;
		}
		
		if($this->memcache->set($key,$tmp,false,$expire_time)){
			$this->debugMsg("Item stored!");
			return true;
		}
		else{
			$this->debugMsg("Unable to store item in cache.");
			return false;
		}
	}
	
	private function getHashKey($query,$cache_name){
		$hash = $this->prefix.$cache_name ."_" . md5(trim(str_replace(array("\n","\t","\r","\0"),"",strtolower($query))));
		$this->debugMsg("cache hash = $hash");
		return $hash;
	}
	
	private function parseQuery($query,&$sql_type){
		$query = trim(str_replace("\n"," ",$query));
		$sql_type = strtoupper(reset(explode(" ",$query)));
		if($sql_type=='SELECT'||$sql_type=='DELETE'){
			$temp = explode(" ",substr($query,stripos($query,"FROM")));
			$sql_table = $temp[1];
		}
		elseif($sql_type=='INSERT'||$sql_type=='REPLACE'){
			$temp = explode(" ",substr($query,stripos($query,"INTO")));
			$sql_table = $temp[1];
		}
		else{
			$temp = explode(" ",$query);
			$sql_table = $temp[1];
		}
		
		return $sql_table;
	}
	
	private function queryTypeAllowed($type,$sql_type){
		if($sql_type!='SELECT'){ return false; }
		if(isset($this->allowed_cache[$type])){
			return $this->allowed_cache[$type];
		}
		
		return false;
	}
	
	private function db_query($query,$db_return){
		$this->debugMsg("Querying database: $query");
		$result = mysql_query($query);
		if(!$result){
			$this->debugMsg(mysql_error());
			return false;
		}
		
		if($db_return == 'assoc'){
			if(mysql_num_rows($result) == 0){ return false; }
			$dbdata = array();
			while($data = mysql_fetch_assoc($result)){
				$dbdata[] = $data;
			}
			
			$this->debugMsg($dbdata);
			return $dbdata;
		}
		else{
			if(mysql_num_rows($result) > 0){
				return $result;
			}
			else{
				return false;
			}
		}
	}
	
	private function debugMsg($msg){
		if($this->debug){
			echo "Debug: ";
			print_r($msg);
			echo "\n";
		}
	}
	
	public function memcacheTest(){
		$version = $this->memcache->getVersion();
		echo "Server's version: ".$version."<br/>\n";
		
		$result = $this->memcache->get($this->prefix.'osimo_posts');
		if(!$result){
			$result = new stdClass;
		}
		
		$result->data[1234] = array("content"=>"ohai lol","post_time"=>1234567);
		
		$this->memcache->set($this->prefix.'osimo_posts', $result, false, 60) or die ("Failed to save data at the server");
		echo "Store data in the cache (data will expire in 10 seconds)<br/>\n";
		
		$get_result = $this->memcache->get($this->prefix.'osimo_posts');
		echo "Data from the cache:<br/>\n";
		
		print_r($get_result);
	}
	
	public function memcacheInfo(){
		if(is_array($this->cache_addr)){
			$serv_status = $this->memcache->getServerStatus($this->cache_addr[0],$this->cache_port);
		}
		else{
			$serv_status = $this->memcache->getServerStatus($this->cache_addr,$this->cache_port);
		}
		
		?>
		<style>
			body{
				background-color: #383838;
			}
			h4{
				font-family: Georgia, sans-serif;
				color: #f2f2f2;
				font-size: 24px;
				text-align: center;
				margin: 10px 0 0 0;
			}
			#mem_usage{
				text-align: center;
				color: #f2f2f2;
				margin: 0;
			}
			.memcache_host{
				text-align: center;
				color: #f2f2f2;
				margin: 10px 0;
				font-family: Georgia, sans-serif;
			}
			#memcacheinfo_table{
				margin: 0 auto;
				border-collapse: collapse;
			}
			#memcacheinfo_table td{
				color: #f2f2f2;
				padding: 4px 5px 4px 5px;
				border: 1px #666666 solid;
			}
			#memcache_footer{
				text-align: center;
				font-size: 13px;
				color: #e6e6e6;
			}
			#memcache_footer a{ color: #e6e6e6; }
		</style>
		<?
		echo "<h4>OsimoCache Server Status: ";
		if($serv_status){ echo "<span style=\"color:green\">Online :)</span>"; }
		else{ echo "<span style=\"color:red\">Offline :(</span>"; exit; }
		echo "</h4>\n";
		$memUsage = exec('ps -o rss,command | grep \'memcached -d\'  | awk \'{print $0}{sum+=$1} END {print "\n", sum/1024, "MB"}\'');
		echo "<p id='mem_usage'>memory usage for this server (if any): $memUsage</p>";
		$statuses = $this->memcache->getExtendedStats();
		foreach($statuses as $host=>$status){
			$uptime_hour = round($status['uptime'] / 60/60,2);
			echo "<p class=\"memcache_host\">server $host</p>";
			echo "<table id=\"memcacheinfo_table\">";
        	echo "<tr><td>Memcache Server version:</td><td> ".$status ["version"]."</td></tr>";
        	echo "<tr><td>Process id of this server process </td><td>".$status ["pid"]."</td></tr>";
        	echo "<tr><td>Server uptime:</td><td>".number_format($status ["uptime"])." seconds ($uptime_hour hours)</td></tr>";
        	echo "<tr><td>Accumulated user time for this process </td><td>".$status ["rusage_user"]." seconds</td></tr>";
        	echo "<tr><td>Accumulated system time for this process </td><td>".$status ["rusage_system"]." seconds</td></tr>";
        	echo "<tr><td>Total number of items stored by this server ever since it started </td><td>".number_format($status["total_items"])."</td></tr>";
        	echo "<tr><td>Number of open connections </td><td>".$status ["curr_connections"]."</td></tr>";
        	echo "<tr><td>Total number of connections opened since the server started running </td><td>".number_format($status["total_connections"])."</td></tr>";
        	echo "<tr><td>Number of connection structures allocated by the server </td><td>".$status["connection_structures"]."</td></tr>";
        	echo "<tr><td>Cumulative number of retrieval requests </td><td>".number_format($status ["cmd_get"])."</td></tr>";
        	echo "<tr><td> Cumulative number of storage requests </td><td>".number_format($status ["cmd_set"])."</td></tr>";
			
        	$percCacheHit=((real)$status ["get_hits"]/ (real)$status ["cmd_get"] *100);
        	$percCacheHit=round($percCacheHit,3);
        	$percCacheMiss=100-$percCacheHit;
			
        	echo "<tr><td>Number of keys that have been requested and found present </td><td>".number_format($status ["get_hits"])." ($percCacheHit%)</td></tr>";
        	echo "<tr><td>Number of items that have been requested and not found </td><td>".number_format($status ["get_misses"])." ($percCacheMiss%)</td></tr>";
			
        	$MBRead= (real)$status["bytes_read"]/(1024*1024);
			
        	echo "<tr><td>Total number of bytes read by this server from network </td><td>".$MBRead." Mega Bytes</td></tr>";
        	$MBWrite=(real) $status["bytes_written"]/(1024*1024) ;
        	echo "<tr><td>Total number of bytes sent by this server to network </td><td>".$MBWrite." Mega Bytes</td></tr>";
        	$MBSize=(real) $status["limit_maxbytes"]/(1024*1024) ;
        	echo "<tr><td>Number of bytes this server is allowed to use for storage.</td><td>".$MBSize." Mega Bytes</td></tr>";
        	echo "<tr><td>Number of valid items removed from cache to free memory for new items.</td><td>".$status ["evictions"]."</td></tr>";
			
			echo "</table>";
		}
		
		echo "<p id=\"memcache_footer\">osimocache is powered by <a href=\"http://www.danga.com/memcached/\">memcached</a></p>";
	}
	
	public static function orderBy(&$ary, $clause, $ascending = true) {
        $clause = str_ireplace('order by', '', $clause);
        $clause = preg_replace('/\s+/', ' ', $clause);
        $keys = explode(',', $clause);
        $dirMap = array('desc' => 1, 'asc' => -1);
        $def = $ascending ? -1 : 1;

        $keyAry = array();
        $dirAry = array();
        foreach($keys as $key) {
            $key = explode(' ', trim($key));
            $keyAry[] = trim($key[0]);
            if(isset($key[1])) {
                $dir = strtolower(trim($key[1]));
                $dirAry[] = $dirMap[$dir] ? $dirMap[$dir] : $def;
            } else {
                $dirAry[] = $def;
            }
        }

        $fnBody = '';
        for($i = count($keyAry) - 1; $i >= 0; $i--) {
            $k = $keyAry[$i];
            $t = $dirAry[$i];
            $f = -1 * $t;
            $aStr = '$a[\''.$k.'\']';
            $bStr = '$b[\''.$k.'\']';
            if(strpos($k, '(') !== false) {
                $aStr = '$a->'.$k;
                $bStr = '$b->'.$k;
            }

            if($fnBody == '') {
                $fnBody .= "if({$aStr} == {$bStr}) { return 0; }\n";
                $fnBody .= "return ({$aStr} < {$bStr}) ? {$t} : {$f};\n";               
            } else {
                $fnBody = "if({$aStr} == {$bStr}) {\n" . $fnBody;
                $fnBody .= "}\n";
                $fnBody .= "return ({$aStr} < {$bStr}) ? {$t} : {$f};\n";
            }
        }

        if($fnBody) {
            $sortFn = create_function('$a,$b', $fnBody);
            usort($ary, $sortFn);       
        }
    }
}
?>