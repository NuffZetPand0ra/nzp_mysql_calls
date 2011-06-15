<?php
//todo: lav order by funktionalitet på select methods
//todo: flere fejlmeddelelser på select methods



// Released under Creative Commons license. Project startet by NuffZetPandora.
// ver 0.1.2
//
// changelog:
// 0.1
//	initial release.
// 0.1.1
//	!IMPORTANT THIS VERSION IS DEPRECATED DUE TO BUGS. FIXED IN 0.1.2.
//	added support for arrays as possible data input in inputQuery() for insertion of multiple rows.
//	added join support in buildSelectQuery(), selectQuery() and selectArray().
// 0.1.2
//	minor bug fixes :)
// 0.1.3
//	added method selectRow.
//	added method buildInsertQuery.

class db{
	private $con;
	function __construct($dbHost="localhost", $dbUser="root", $dbPassword="", $dbDatabase=""){
		$this->con = mysql_pconnect($dbHost, $dbUser, $dbPassword) or die("Couldn't find host! ".mysql_error());
		mysql_select_db($dbDatabase, $this->con) or die("Couldn't select database! ".mysql_error());
	}
	function close(){mysql_close($this->con);}
	function query($query){
		$result = mysql_query($query, $this->con) or die("'$query'<br>The query couldn't be excuted! ".mysql_error());
		return $result;
	}
	function buildInsertQuery($tableAndColumns, $data){		
		//data can be array
		if(is_array($data)){
			foreach($data as $row){
				if(is_array($row)){
					foreach($row as $field){
						$rowstring .= $field.", ";
					}
					$rowstring = "(".substr($rowstring, 0, -2)."), ";
					$datastring .= $rowstring;
				}elseif(is_string($row)){
					$datastring = "(".$row.") ,";
				}
			}
			$datastring = substr($datastring, 0, -2);
		}elseif(is_string($data)){
			$datastring = "(".$data.")";
		}
		$query = "INSERT INTO $tableAndColumns VALUES $datastring";
		return $query;
	}
	function insertQuery($tableAndColumns, $data){
		$query = $this->buildInsertQuery($tableAndColumns, $data);
		return $this->query($query);
	}
	// methods for selecting
	function buildSelectQuery($table, $fields="*", $limiter="", $joins=""){
		//$joins needs array(table, type, key) or array(array(table, type, key), array(table, type, key))
		$query = "SELECT $fields FROM $table";
		if(is_array($joins)){
			if(is_array($joins[0])){
				foreach($joins as $join){
					$join_string .= " ".strtoupper($join[1])." JOIN ".$join[0]." ON ".$join[2];
				}
			}else{
				$join_string .= " ".strtoupper($joins[1])." JOIN ".$joins[0]." ON ".$joins[2];
			}
		}
		$query .= $join_string;
		if(strlen($limiter) > 0){
			$query .= " WHERE $limiter";
		}
		return $query;
	}
	function selectQuery($table, $fields="*", $limiter="", $joins=""){
		$query = $this->buildSelectQuery($table, $fields, $limiter, $joins);
		$result = $this->query($query);
		return $result;
	}
	function selectArray($table, $fields="*", $limiter="", $joins="", $type="assoc"){
		$result = $this->selectQuery($table, $fields, $limiter, $joins);
		switch ($type) {
			default: $type_format = MYSQL_ASSOC; break;
			case "num": $type_format = MYSQL_NUM; break;
		}
		while($row = mysql_fetch_array($result, $type_format)){
			$return[] = $row;
		}
		return $return;
	}
	function selectRow($table, $fields="*", $limiter="", $joins="", $type="assoc"){
		$limiter .= " LIMIT 0,1";
		$result = $this->selectArray($table, $fields, $limiter, $joins, $type);
		return $result[0];
	}
	function updateQuery($table, $set, $where = ""){
		$query = "UPDATE $table SET $set";
		if(strlen($where)>0){
			$query .= " WHERE $where";
		}
		return ($this->query($query) != false) ? true : mysql_error($this->con)." ".$query;
	}
	function deleteQuery($table, $where = ""){
		$query = "DELETE FROM $table";
		if(strlen($where)>0){
			$query .= " WHERE $where";
		}
		return ($this->query($query) != false) ? true : mysql_error($this->con);
	}
	function lastId($table = ""){
		if(strlen($table)>0){
			$result = $this->selectArray("SELECT LAST_INSERT_ID() AS id FROM $table", "num");
			return $result[0][0];
		}
		return mysql_insert_id($this->con);
	}
	public static function resultCount($query){
		$result = $this->query($query);
		$count = mysql_num_rows($result);
		return $count;
	}
}
?>