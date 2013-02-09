<?php
require_once 'config.php';

$his = re_db_select("history", array("response"),"id=".$_GET['his']);
echo $his[0]['response'];