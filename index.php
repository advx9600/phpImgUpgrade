<?php
include_once 'class.MySQL.php';
include_once 'db.php';

define("SEG_UBOOT", "uboot");
define("SEG_UBOOT_PATH", "ubootPath");
define("SEG_KERNEL", "kernel");
define("SEG_KERNEL_PATH", "kernelPath");
define("SEG_SYSTEM", "system");
define("SEG_SYSTEM_PATH", "systemPath");

$mySql=new MySQL();
$board = isset($_REQUEST['board'])?$_REQUEST['board']:"" ;
$gitBranch= isset($_REQUEST['gitBranch'])?$_REQUEST['gitBranch']:"";

$mySql->executeSQL("select * from tb_img  where board='$board' and gitBranch='$gitBranch'");
$result1=$mySql->arrayedResult;
$mySql->executeSQL("select * from tb_img  where board='$board' and gitBranch='master'");
$result2 = $mySql->arrayedResult;

$pro= new UtilA($result1, $result2);
$result[$pro->setName(SEG_UBOOT)]=$pro->getVal();
$result[$pro->setName(SEG_KERNEL)]=$pro->getVal();
$result[$pro->setName(SEG_SYSTEM)]=$pro->getVal();

$result[$pro->setName(SEG_UBOOT_PATH)]=$pro->getVal2();
$result[$pro->setName(SEG_KERNEL_PATH)]=$pro->getVal2();
$result[$pro->setName(SEG_SYSTEM_PATH)]=$pro->getVal2();

echo json_encode($result);
?>
