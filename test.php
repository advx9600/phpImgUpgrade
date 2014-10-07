<?php
$archive = new ZipArchive;
$archive->open('G:/one/smdkv210_u-boot.zip');
$archive->extractTo('G:/one');
foreach($archive as $file) {
	echo $file;
};
?>