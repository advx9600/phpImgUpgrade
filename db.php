<?php
class DB{
	private $mySql;
	function __construct(){
		$this->mySql = new MySQL("db_img_update","root","");
	}

	public function  exeSql($sql){
		$this->mySql->executeSQL($sql);
		return  $this->mySql->arrayResult();
	}
}

class UtilA{
	private $r1;
	private $r2;
	private $name;
	public function __construct($r1,$r2){
		$this->r1 = $r1;
		$this->r2 = $r2;
	}
	private function path2url($file_path, $Protocol='http://') {
		$file_path=str_replace('\\','/',$file_path);
		$file_path="/pro/".str_replace(dirname($_SERVER['DOCUMENT_ROOT']),'',$file_path);
		$file_path='http://'.$_SERVER['HTTP_HOST'].$file_path;
		return $file_path;
	}
	public  function  setName($name	){
		$this->name=$name;
		return $name;
	}
	public function  getVal(){
		$seg = $this->name;
		return isset($this->r1[$seg])?$this->r1[$seg]:$this->r2[$seg];
	}
	public  function  getVal2(){
		$file = $this->getVal();
		return $this->path2url($file);
	}
};
?>