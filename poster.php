<?php 
	require_once 'config.php';
	
	$_SESSION['url']=$_REQUEST['url'];
	addURL($_REQUEST['url']);
	
	$_SESSION['method']=$_REQUEST['method'];
	
	$parseURL = parse_url($_REQUEST['url']);
	
	$keyValue = array();
	foreach ($_REQUEST['key'] as $k=>$key){
		if (isset($key) && $key != ""){
			$keyValue[$key]=$_REQUEST['value'][$k];
			addKey($key,$parseURL['host']);
		}
	}
	
	$_SESSION['key_value']=$keyValue;
	
	$headerKeyValue = array();
	$isContentTypeSet = FALSE;
	foreach ($_REQUEST['header_value'] as $k=>$value){
		if (isset($value) && $value != ""){
			if ($_REQUEST['header_key'][$k] == "Content-Type"){
				$isContentTypeSet = TRUE;
			}
			$headerKeyValue[]=$_REQUEST['header_key'][$k].": ".$value;
		}
	}
	
	$_SESSION['header_key_value']=$headerKeyValue;
	if(!$isContentTypeSet){
		$headerKeyValue[]='Content-Type: multipart/form-data';
	}
	
	$headerKeyValue[]='Expect:';
	
	$ch = curl_init();
	
	
	$url = ($_REQUEST['method'] == "POST") ? $_REQUEST['url'] : $_REQUEST['url'].(strpos($_REQUEST['url'], "?")===false?"?":"&").http_build_query($keyValue);
	
	curl_setopt($ch, CURLOPT_URL,            $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_POST,           ($_REQUEST['method'] == "POST"));
	curl_setopt($ch, CURLOPT_HTTPGET,        ($_REQUEST['method'] == "GET"));
	curl_setopt($ch, CURLOPT_POSTFIELDS,     ($_REQUEST['method'] == "POST") ? $keyValue : null); 
	curl_setopt($ch, CURLOPT_HTTPHEADER,     $headerKeyValue);
//	curl_setopt($ch, CURLOPT_HEADER,     true); 
	
	$result = curl_exec ($ch);
	
	if ($result === false){
		$result = "<div style='color:red;'>".curl_error($ch)."</div>";
	}
	
	$insertData = array(
		'url' => $_REQUEST['url'],
		'params' => serialize($keyValue),
		'method' => $_REQUEST['method'],
		'response' => $result,
		'time' => time(),
	);
	
	$id = re_db_insert("history", $insertData);
	
	$history = re_db_select("history", array("*"),"id = $id");
	$his = $history[0];
			  	
	$return = array("id"=>$id,"history"=>getHistoryAcc($his));
	echo json_encode($return);
	exit;
	
	
?>