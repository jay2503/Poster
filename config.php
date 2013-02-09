<?php
session_start();
date_default_timezone_set("Asia/Calcutta");

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

define("DB_HOST","localhost");
define("DB_USER","root");
define("DB_PASS","");
define("DB_NAME","poster");
define("SITE_URL","http://".$_SERVER['HTTP_HOST']."/");
define("SITE_PATH", $_SERVER['DOCUMENT_ROOT']."/");

define("USE_PCONNECT",false);

require_once 'function.php';

// Connct DB
global $db_link;
$db_link = re_db_connect(DB_HOST,DB_USER,DB_PASS,DB_NAME) or die("Could not connect...".mysql_error());