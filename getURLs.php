<?php
require_once 'config.php';

$urls = re_db_select("urls", array("*"), "`url` like '%".$_GET['term']."%'");
if ($urls){
	foreach ($urls as $url){
		$temp[]=$url['url'];
	}
}

echo json_encode($temp);