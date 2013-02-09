<?php
require_once 'config.php';

$history = re_db_select("history", array("url","method","params"),"id=".$_GET['his']);
$his = $history[0];

$his['params']=unserialize($his['params']);
echo json_encode($his);