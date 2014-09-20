<?php
	$action = $_GET['action'];
	$type = $_GET['type'];
	
	if ($action == "getImgVer"){
		$arr = array ('a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5);
		echo json_encode($arr);
		echo $type;
	}
?>
