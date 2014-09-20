<?php
class DB{
	private $con;
	public function open(){
		$this->con = mysql_connect("localhost","root","");		
		if (!$this->con)
		{
			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db("db_img_update", $this->con);
	}

	public function close(){
		if ($this->con){
			mysql_close($this->con);
		}
	}
	
	public function query($sql){
		if (!$this->con){
			die( "this is null");
		}
		return mysql_query($sql,$this->con);
	}
}
?>