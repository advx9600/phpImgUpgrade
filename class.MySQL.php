<?php
/*
 *  Copyright (C) 2012
 *     Ed Rackham (http://github.com/a1phanumeric/PHP-MySQL-Class)
 *  Changes to Version 0.8.1 copyright (C) 2013
 *	Christopher Harms (http://github.com/neurotroph)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// MySQL Class v0.8.1
class MySQL {

	// Base variables
	var $lastError;         // Holds the last error
	var $lastQuery;         // Holds the last query
	var $result;            // Holds the MySQL query result
	var $records;           // Holds the total number of records returned
	var $affected;          // Holds the total number of records affected
	var $rawResults;        // Holds raw 'arrayed' results
	var $arrayedResult;     // Holds an array of the result

	var $hostname;          // MySQL Hostname
	var $username;          // MySQL Username
	var $password;          // MySQL Password
	var $database;          // MySQL Database

	var $databaseLink;      // Database Connection Link



	/* *******************
	 * Class Constructor *
	 * *******************/

	function __construct($database='db_img_update', $username='root', $password='', $hostname='localhost', $port=3306){
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;
		$this->hostname = $hostname.':'.$port;

		if (!$this->Connect()){
			echo $this->lastError;
		}
		mysqli_query($this->databaseLink,"SET NAMES 'UTF8'");
	}



	/* *******************
	 * Private Functions *
	 * *******************/

	// Connects class to database
	// $persistant (boolean) - Use persistant connection?
	private function Connect($persistant = false){
		$this->CloseConnection();

		if($persistant){
			$this->databaseLink = mysql_pconnect($this->hostname, $this->username, $this->password);
		}else{
			$this->databaseLink = mysqli_connect($this->hostname, $this->username, $this->password,$this->database);
		}

		if(!$this->databaseLink){
			$this->lastError = 'Could not connect to server: ' . mysql_error($this->databaseLink);
			return false;
		}

		return true;
	}


	// Performs a 'mysql_real_escape_string' on the entire array/string
	private function SecureData($data, $types){
		if(is_array($data)){
			$i = 0;
			foreach($data as $key=>$val){
				if(!is_array($data[$key])){
					$this->CleanData($data[$key], $types[$i]);
					$data[$key] = mysqli_real_escape_string( $this->databaseLink,$data[$key]);
					$i++;
				}
			}
		}else{
			$this->CleanData($data, $types);
			$data = mysqli_real_escape_string($this->databaseLink,$data);
		}
		return $data;
	}

	// clean the variable with given types
	// possible types: none, str, int, float, bool, datetime, ts2dt (given timestamp convert to mysql datetime)
	// bonus types: hexcolor, email
	private function CleanData(&$data, $type = ''){
		switch($type) {
			case 'none':
				$data = $data;
				break;
			case 'str':
				settype( $data, 'string');
				break;
			case 'int':
				settype( $data, 'integer');
				break;
			case 'float':
				settype( $data, 'float');
				break;
			case 'bool':
				settype( $data, 'boolean');
				break;
				// Y-m-d H:i:s
				// 2014-01-01 12:30:30
			case 'datetime':
				$data = trim( $data );
				$data = preg_replace('/[^\d\-: ]/i', '', $data);
				preg_match( '/^([\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2})$/', $data, $matches );
				$data = $matches[1];
				break;
			case 'ts2dt':
				settype( $data, 'integer');
				$data = date('Y-m-d H:i:s', $data);
				break;

				// bonus types
			case 'hexcolor':
				preg_match( '/(#[0-9abcdef]{6})/i', $data, $matches );
				$data = $matches[1];
				break;
			case 'email':
				$data = filter_var($data, FILTER_VALIDATE_EMAIL);
				break;
			default:
				$data = '';
				break;
		}
		return $data;
	}



	/* ******************
	 * Public Functions *
	 * ******************/

	// Executes MySQL query
	public function executeSQL($query){
		$this->lastQuery = $query;		
		if($this->result = mysqli_query( $this->databaseLink,$query)){
			if (gettype($this->result) === 'object') {
				$this->records  = @mysqli_num_rows($this->result);
				$this->affected = @mysqli_affected_rows($this->databaseLink);
			} else {
				$this->records  = 0;
				$this->affected = 0;
			}

			if($this->records > 0){
				$this->arrayResults();
				return $this->arrayedResult;
			}else{
				return true;
			}

		}else{
			$this->lastError = mysql_error($this->databaseLink);
			echo $this->lastError;
			return false;
		}
	}


	// Adds a record to the database based on the array key names
	public function insert($table, $vars, $datatypes,$exclude = ''){

		// Catch Exclusions
		if($exclude == ''){
			$exclude = array();
		}

		array_push($exclude, 'MAX_FILE_SIZE'); // Automatically exclude this one

		// Prepare Variables
		$vars = $this->SecureData($vars, $datatypes);

		$query = "INSERT INTO `{$table}` SET ";
		foreach($vars as $key=>$value){
			if(in_array($key, $exclude)){
				continue;
			}
			$query .= "`{$key}` = '{$value}', ";
		}

		$query = trim($query, ', ');

		return $this->executeSQL($query);
	}

	// Deletes a record from the database
	public function delete($table, $where='', $limit='', $like=false, $wheretypes){
		$query = "DELETE FROM `{$table}` WHERE ";
		if(is_array($where) && $where != ''){
			// Prepare Variables
			$where = $this->SecureData($where, $wheretypes);

			foreach($where as $key=>$value){
				if($like){
					$query .= "`{$key}` LIKE '%{$value}%' AND ";
				}else{
					$query .= "`{$key}` = '{$value}' AND ";
				}
			}

			$query = substr($query, 0, -5);
		}

		if($limit != ''){
			$query .= ' LIMIT ' . $limit;
		}

		return $this->executeSQL($query);
	}


	// Gets a single row from $from where $where is true
	public function select($from, $where='',$wheretypes='', $orderBy='', $limit='', $like=false, $operand='AND',$cols='*'){
		// Catch Exceptions
		if(trim($from) == ''){
			return false;
		}

		$query = "SELECT {$cols} FROM `{$from}` WHERE ";

		if(is_array($where) && $where != ''){
			// Prepare Variables
			$where = $this->SecureData($where, $wheretypes);

			foreach($where as $key=>$value){
				if($like){
					$query .= "`{$key}` LIKE '%{$value}%' {$operand} ";
				}else{
					$query .= "`{$key}` = '{$value}' {$operand} ";
				}
			}

			$query = substr($query, 0, -(strlen($operand)+2));

		}else{
			$query = substr($query, 0, -6);
		}

		if($orderBy != ''){
			$query .= ' ORDER BY ' . $orderBy;
		}

		if($limit != ''){
			$query .= ' LIMIT ' . $limit;
		}

		return $this->executeSQL($query);

	}

	// Updates a record in the database based on WHERE
	public function update($table, $set, $datatypes, $where,  $wheretypes,$exclude = ''){
		// Catch Exceptions
		if(trim($table) == '' || !is_array($set) || !is_array($where)){
			return false;
		}
		if($exclude == ''){
			$exclude = array();
		}

		array_push($exclude, 'MAX_FILE_SIZE'); // Automatically exclude this one

		$set 	= $this->SecureData($set, $datatypes);
		$where 	= $this->SecureData($where,$wheretypes);

		// SET

		$query = "UPDATE `{$table}` SET ";

		foreach($set as $key=>$value){
			if(in_array($key, $exclude)){
				continue;
			}
			$query .= "`{$key}` = '{$value}', ";
		}

		$query = substr($query, 0, -2);

		// WHERE

		$query .= ' WHERE ';

		foreach($where as $key=>$value){
			$query .= "`{$key}` = '{$value}' AND ";
		}

		$query = substr($query, 0, -5);

		return $this->executeSQL($query);
	}

	// 'Arrays' a single result
	public function arrayResult(){
		$this->arrayedResult = mysqli_fetch_assoc($this->result) or die (mysql_error($this->databaseLink));
		return $this->arrayedResult;
	}

	// 'Arrays' multiple result
	public function arrayResults(){

		if($this->records == 1){
			return $this->arrayResult();
		}

		$this->arrayedResult = array();
		while ($data = mysqli_fetch_assoc($this->result)){
			$this->arrayedResult[] = $data;
		}
		return $this->arrayedResult;
	}

	// 'Arrays' multiple results with a key
	public function arrayResultsWithKey($key='id'){
		if(isset($this->arrayedResult)){
			unset($this->arrayedResult);
		}
		$this->arrayedResult = array();
		while($row = mysqli_fetch_assoc($this->result)){
			foreach($row as $theKey => $theValue){
				$this->arrayedResult[$row[$key]][$theKey] = $theValue;
			}
		}
		return $this->arrayedResult;
	}

	// Returns last insert ID
	public function lastInsertID(){
		return mysqli_insert_id();
	}

	// Return number of rows
	public function countRows($from, $where=''){
		$result = $this->select($from, $where, '', '', false, 'AND','count(*)');
		return $result["count(*)"];
	}

	// Closes the connections
	public function closeConnection(){
		if($this->databaseLink){
			mysqli_close($this->databaseLink);
		}
	}

	private static $tb_img= 'tb_img';

	private function a_insertOrUpdate_a($board,$gitBranch,$lastVer,$imgType,$save_path,$save_url_path,$imgFile){
		$tb = MySQL::$tb_img;
		$this->select($tb,array('board'=>$board,'git_branch'=>$gitBranch),array(0=>'str',1=>'str'));
		if ($this->records <1){
			$this->insert($tb,
			array('board'=>$board,'git_branch'=>$gitBranch,'save_path'=>$save_path,$imgType=>$lastVer,'save_url_path'=>"$save_url_path",$imgType."_file_name"=>"$imgFile"),
			array(0=>"str",1=>"str",2=>"str",3=>"str",4=>"str",5=>"str"));
		}else{
			$this->update($tb, array("$imgType"=>$lastVer,"{$imgType}_file_name"=>"$imgFile"), array(0=>'str',1=>'str'),
			array("board"=>$board,'git_branch'=>$gitBranch), array(0=>'str',1=>'str'));
		}
	}
	public function a_insertOrUpdate($board,$gitBranch,$imgFiles,$lastVer,$save_path,$save_url_path){
		$imgFiles=explode(" ", $imgFiles);		
		for ($i=0;$i<count($imgFiles);$i++)
		{
			$imgType=$this->getImgTypeByImgName($imgFiles[$i]);
			
			$this->a_insertOrUpdate_a($board,$gitBranch,$lastVer,$imgType,$save_path,$save_url_path,$imgFiles[$i]);
		}
	}
	
	public function getImgTypeByImgName($name){
		$name=explode(".", $name);
		$name=$name[0];
		if (strpos($name, "ramdisk")!==FALSE){
			$name="ramdisk";
		}
		return $name;
	}
	public function a_getData($board='',$branch='',$isMasterFill=false){
		$where= array();
		$whereType = array();
		$iAt=0;
		if (strlen($board)>0){
			$where['board']=$board;
			$whereType[$iAt++] = 'str';
		}
		if (strlen($branch) >0){
			$where['git_branch']=$branch;
			$whereType[$iAt++] = 'str';
		}

		$this->select(MySQL::$tb_img,$where,$whereType);
		$result = $this->arrayedResult;
		if ($this->records==1){
			$result=array($result);
		}


		$data= array();
		$k=0;
		for ($i= 0;$i<count($result);$i++)
		{
			for($j=0;$j<5;$j++)
			{
				$name="";
				switch($j){
					case 0:$name='u-boot';break;
					case 1:$name='kernel';break;
					case 2:$name='ramdisk';break;
					case 3:$name='userdata';break;
					case 4:$name='system';break;
				}
				$filename=$result[$i]['save_path']."/".$result[$i]["{$name}_file_name"];				
				$file_size = filesize($filename);
				if ((!is_dir($filename) && $file_size> 0) || ($isMasterFill && $file_size<1)){
					$a_board = "";
					$a_gitBranch= "";
					$a_fileName="";
					$a_savePath="";
					$a_name="";
					$a_saveUrlPath = "";
					if ($file_size>0){
						$a_board=$result[$i]["board"];
						$a_gitBranch=$result[$i]["git_branch"];
						$a_fileName = $result[$i][$name.'_file_name'];
						$a_name=$result[$i]["$name"];
						$a_savePath=$result[$i]['save_path'];
						$a_saveUrlPath =$result[$i]["save_url_path"];
					}else{
						$where['git_branch']='master';
						$this->select(MySQL::$tb_img,$where,$whereType);
						$b_result = $this->arrayedResult;
						
						$a_board=$b_result["board"];
						$a_gitBranch=$b_result["git_branch"];
						$a_fileName = $b_result[$name.'_file_name'];
						$a_name=$b_result["$name"];
						$a_savePath=$b_result['save_path'];
						$a_saveUrlPath =$b_result["save_url_path"];
					}
						
					$data[$k]['board'] = $a_board;
					$data[$k]['git_branch'] = $a_gitBranch;
					$data[$k]['filename'] = $a_fileName;
					$data[$k]['name'] = $data[$k]['board'].
					"---------".$data[$k]['git_branch'].
					"---------".$a_fileName.
					"---------".$a_name;
					//var_dump($result[$i]['save_path']."/".$result[$i]["{$name}_file_name"]);
					$data[$k]['size']= filesize($a_savePath."/".$a_fileName);
					$data[$k]['version'] = $a_name;
					$data[$k]['url']=$a_saveUrlPath."/".$a_fileName;
					$k++;
				}
			}
		}


		return $data;
	}

}