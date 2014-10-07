<?php
class DB{
	private $mySql;
	function __construct(){
		$this->mySql = new MySQL("db_img_update","root","");
	}

	function updateOrInsert(){
		 
	}
}
?>