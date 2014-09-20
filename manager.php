<?php
include_once 'class.MySQL.php';
$db = new MySQL("db_img_update","root","");
$action = "";
if (count($_GET) > 0){
	$action = $_GET['action'];
}
?>
<html>
<head>
<script language="javascript" src="jquery-1.9.1.min.js"></script>
</head>

<body>

	<table>
		<tr>
		<?php
		$db->select("tb_img_type");
		$len = $db->records;
		$result = $db->arrayedResult;
		if ($len == 1){
			$result = array($result);
		}


		for ($i = 0;$i<$len;$i++){
			$type =$result[$i]['name'];
			echo "<td><table border=1>";
			echo "<tr><td colspan='2'>$type</td></tr>";

			$db->select("tb_img",array("id" => $result[$i]['img_id']), '','', false, 'AND','*', array(0=>"int"));
			$result2 = $db->arrayedResult;
			$keys =array_keys($result2);
			if ($db->records >0)
			for ($j=1;$j<count($result2)-1;$j++){
				echo "<tr><td>$keys[$j]</td><td>{$result2[$keys[$j]]}</td></tr>";
			}

			echo "</table></td>";
		}

		?>
		</tr>
	</table>
	<br/><br/><br/>
	<form action="" method="POST" enctype="multipart/form-data">
		<table>
			<tr>
				<td><input type="file" name="u-boot" id="txtFile" /></td>
			</tr>

		</table>
	</form>
	<script type='text/javascript'>
	$('#txtFile').bind('change', function() {
  alert(this.files[0].name);
  var reader = new FileReader();
  alert(reader.readAsText(this.files[0])); 
});
	</script>
</body>
</html>

		<?php
		$db->closeConnection();
		?>