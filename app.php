<?php
include_once 'class.MySQL.php';
include_once 'db.php';
$mySql=new MySQL();
$boardId = isset($_REQUEST['boardId'])?$_REQUEST['boardId']:"36";//默认的通用ID
$sql = "select * from tb_1_app where boardId=$boardId";
$mySql->executeSQL($sql);
$result=$mySql->arrayedResult;

$mySql->executeSQL("select * from tb_1_board");
$boards =$mySql->arrayedResult;
?>
<!DOCTYPE unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta charset="utf-8">
<style type="text/css">
</style>
</head>
<body>
	<script type="text/javascript">
function doBoard(val){
	location.href="?boardId="+val;
}
</script>
	<label>Select Type</label>
	<select onchange="doBoard(this.value)">
	<?php for ($i=0;$i<count($boards);$i++){
		$selected="";
		if ($boardId==$boards[$i]['id'] ) $selected="selected=\"selected\"";
		echo "<option value='{$boards[$i]['id']}' $selected>{$boards[$i]['name']}</option>";
	}?>
	</select>
	<br />
	<table id="t01" border="0" style="width: 100%">
	<?php
	$pro= new UtilA($result, $result);
	for ($i=0;$i<count($result);$i++){
		$style=$i%2==0?"":"background: #eee";
		$savePath = $pro->path2url($result[$i]['savePath']);
		?>
		<tr style="<?php echo $style?>">
			<td><?php			
			echo "<a href='$savePath' target='blank'><img width=60 height=60 src='{$pro->path2url($result[$i]['iconPath'])}' /></a>";
			?>
			</td>
			<td>
				<table border="0">
					<tr>
						<td><?php echo "<a href='$savePath' target='blank'>".$result[$i]['title']."</a>"; ?>
						</td>
					</tr>
					<tr>
						<td><?php echo $result[$i]['verCode'].".".$result[$i]['verName']?>
						</td>
					</tr>
					<tr>
						<td><?php echo $result[$i]['note'];?></td>
					</tr>
				</table>
			</td>
		</tr>
		<?php
	}
	?>
	</table>

</body>
</html>
