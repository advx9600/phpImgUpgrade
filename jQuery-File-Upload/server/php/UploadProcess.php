<?php
include_once '../../../class.MySQL.php';

class UploadProcess{
	private $options ;
	private $db;

	function __construct(){
		$this->options = array(
		'tmp_dir' => dirname(__FILE__).'/files/tmp_',
		'save_path' =>dirname(__FILE__).'/files',
		'save_url' => "files",		
		);

		$this->db = new MySQL();
	}

	function isNeedUpload($file_name){
		if (substr($file_name, -strlen('.zip')) == '.zip'){
			return true;
		}
		return false;
	}

	function getData($board='',$branch='',$isMasterFill=false){
		$data= $this->db->a_getData($board,$branch,$isMasterFill);
		return $data;
	}

	function processTarGzFile($file_name){
		$archive = new PharData($file_name);
		$txtFile = "";
		foreach($archive as $file) {
			if (substr($file, -strlen('.txt')) == '.txt'){
				$txtFile = $file;
				break;
			}
		}

		if (strlen($txtFile)<1) {
			unlink($file_name);
			return false;
		}

		$board = $this->getKey($txtFile, "board");
		$lastVer=$this->getKey($txtFile, "latestVer");
		$imgType = $this->getKey($txtFile, "imgType");
		$gitBranch = $this->getKey($txtFile, "gitBranch");
		$imgFiles = $this->getKey($txtFile, "imgFileName");

		if (empty($board) || empty($lastVer) || empty($imgType)
		|| empty($gitBranch) || empty($imgFiles)){
			unlink($file_name);
			return false;
		}

		$tmp_save_dir = $this->getTmpDir();

		/* extract files */
		if (substr($file_name, -4) == ".zip")
		{
			$zip_archive = new ZipArchive;
			$zip_archive->open($file_name);
			$zip_archive->extractTo($tmp_save_dir);
		}else{
			//$archive->extractTo($tmp_save_dir);	
		}

		$save_path = $this->getSaveFilePath($board,$imgType, $gitBranch);
		$save_url_path=$this->getSaveUrlPath($board,$imgType, $gitBranch);

		$this->moveImgFiles($imgFiles, $tmp_save_dir, $save_path);

		$this->deleteDir($tmp_save_dir);

		$this->db->a_insertOrUpdate($board,$gitBranch,$imgFiles,$lastVer,$save_path,$save_url_path);

		//unlink($file_name);
		return true;
	}

	function moveImgFiles($arg_imgFiles,$oldDir,$moveDir){
		$imgs = explode(" ", $arg_imgFiles);
		for ($i = 0;$i<count($imgs);$i++){
			rename($oldDir."/".$imgs[$i], $moveDir."/".$imgs[$i]);
		}
	}

	public  function  getKey($txtFile,$key){
		$val = "";
		$fd = fopen($txtFile, "r");
		while(!feof($fd)) {
			$row=fgets($fd);
			$isFound=0;
			$a=explode(",", $row);
			for ($i=0;$i<count($a);$i++){
				$b= explode(":", $a[$i]);
				if ($b[0] == $key){
					$val= trim(str_replace("\n", "",$b[1])) ;
					$isFound=1;
					break;
				}
			}
			if ($isFound) break;
		}
		fclose($fd);

		return $val;
	}

	function getTmpDir(){
		$tmp_save_dir=$this->options['tmp_dir'].time();
		if (!is_dir($tmp_save_dir)) {
			mkdir($tmp_save_dir);
		}
		return $tmp_save_dir;
	}

	function getSaveFilePath($board,$imgType,$gitBranch){
		if (strpos($imgType, "u-boot")!==FALSE){
			$imgType = "system";
		}
		$path=$this->options['save_path'].'/'.$board.'_'.$imgType.'_'.$gitBranch;
		if (!is_dir($path)) {
			mkdir($path);
		}
		return $path;
	}
	function getSaveUrlPath($board,$imgType,$gitBranch){
		if (strpos($imgType, "u-boot")!==FALSE){
			$imgType = "system";
		}
		return $this->get_full_url()."/".$this->options['save_url'].'/'.$board.'_'.$imgType.'_'.$gitBranch;
	}

	public static function deleteDir($dirPath) {
		if (! is_dir($dirPath)) {
			throw new InvalidArgumentException("$dirPath must be a directory");
		}
		if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
			$dirPath .= '/';
		}
		$files = glob($dirPath . '*', GLOB_MARK);
		foreach ($files as $file) {
			if (is_dir($file)) {
				self::deleteDir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dirPath);
	}

	public function get_full_url() {
		$https = !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') === 0 ||
		!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
		strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
		return
		($https ? 'https://' : 'http://').
		(!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
		($https && $_SERVER['SERVER_PORT'] === 443 ||
		$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
		substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
	}
}

//$cls = new UploadProcess();
//$cls->processTarGzFile(dirname(__FILE__)."/files/a.zip");
//echo $cls->get_full_url();
//echo substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']));
//echo dirname($_SERVER['SCRIPT_NAME']);
?>