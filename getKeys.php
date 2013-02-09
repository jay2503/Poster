<?php
require_once 'config.php';

$parseURL = parse_url($_REQUEST['url']);

$keys = re_db_select("`keys`", array("`key`"), "domain = '".$parseURL['host']."'");

if($keys){
	foreach ($keys as $key){
		$k[]="'".$key['key']."'";
	}
	
	echo "[".implode(", ", $k)."]";
}else{
	echo "[]";
}