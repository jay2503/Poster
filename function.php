<?php
function debug($value, $exit = true) {
  print '<pre>';
  print_r($value);
  print '</pre>';
  if($exit) exit;
}

function printResponse($data){
  if($_REQUEST['debug'] == 1){
    debug($data,false);
  }
  else {
    echo json_encode($data);
  }
}

function get_ip_address() {
  if (isset($_SERVER)) {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
  } else {
    if (getenv('HTTP_X_FORWARDED_FOR')) {
      $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_CLIENT_IP')) {
      $ip = getenv('HTTP_CLIENT_IP');
    } else {
      $ip = getenv('REMOTE_ADDR');
    }
  }

  return $ip;
}

function re_db_input($string){
  // Stripslashes
  if (get_magic_quotes_gpc()){
    $string = stripslashes($string);
  }
  // Quote if not integer
  if (!is_numeric($string)){
    $string = mysql_real_escape_string($string);
  }
  else{
    $string = intval(''.$string,10);
  }
  return $string;
}



// Start of  Get Password from Email
function getEXT($str){
  $t="";
  $string =$str;
  $tok = strtok($string,".");
 	while($tok) {
 	  $t=$tok;
 	  $tok = strtok(".");
 	}
 	return $t;
}

/**************************************************************/
/* New function for Thumb Generation
 ========== Default values ==============
ini_set('memory_limit', '-1');
$save_to_file = true;
$image_quality = 100;
$image_type = -1;
*/
// generate thumb from image and save it
function GenerateThumbFile_new($from_name, $to_name, $max_x, $max_y="") {
  global $save_to_file, $image_type, $image_quality;

  // if src is URL then download file first
  $temp = false;
  if (substr($from_name,0,7) == 'http://') {
    $tmpfname = tempnam("tmp/", "TmP-");
    $temp = @fopen($tmpfname, "w");
    if ($temp) {
      @fwrite($temp, @file_get_contents($from_name)) or die("Cannot download image");
      @fclose($temp);
      $from_name = $tmpfname;
    }
    else {
      die("Cannot create temp file");
    }
  }

  // get source image size (width/height/type)
  //orig_img_type 1 = GIF, 2 = JPG, 3 = PNG;

  list($orig_x, $orig_y, $orig_img_type, $img_sizes) = @GetImageSize($from_name);

  // should we override thumb image type?
  $image_type = ($image_type != -1 ? $image_type : $orig_img_type);

  // check for allowed image types
  if ($orig_img_type < 1 or $orig_img_type > 3) die("Image type not supported");

  if ($orig_x > $max_x or $orig_y > $max_y) {

    // resize
    $per_x = $orig_x / $max_x;
    $per_y = $orig_y / $max_y;
    if ($per_y > $per_x) {
      $max_x = $orig_x / $per_y;
    }
    else {
      $max_y = $orig_y / $per_x;
    }
    "width=".$max_x;
    "height=".$max_y;

    //$max_y=119;
  }
  else {
    // keep original sizes, i.e. just copy
    if ($save_to_file) {
      @copy($from_name, $to_name);
    }
    else {
      switch ($image_type) {
        case 1:
          header("Content-type: image/gif");
          readfile($from_name);
          break;
        case 2:
          header("Content-type: image/jpeg");
          readfile($from_name);
          break;
        case 3:
          header("Content-type: image/png");
          readfile($from_name);
          break;
      }
    }
    return;
  }

  if ($image_type == 1) {
    // should use this function for gifs (gifs are palette images)
    $ni = imagecreate($max_x, $max_y);
  }
  else {
    // Create a new true color image
    $ni = ImageCreateTrueColor($max_x,$max_y);
  }
  // Fill image with white background (255,255,255)
  $white = imagecolorallocate($ni, 255, 255, 255);
  imagefilledrectangle( $ni, 0, 0, $max_x, $max_y, $white);
  // Create a new image from source file
  $im = ImageCreateFromType($orig_img_type,$from_name);
  // Copy the palette from one image to another
  imagepalettecopy($ni,$im);
  // Copy and resize part of an image with resampling
  imagecopyresampled(
      $ni, $im,             // destination, source
      0, 0, 0, 0,           // dstX, dstY, srcX, srcY
      $max_x, $max_y,       // dstW, dstH
      $orig_x, $orig_y);    // srcW, srcH

  // save thumb file
  SaveImage($image_type, $ni, $to_name, $image_quality, $save_to_file);

  if($temp) {
    unlink($tmpfname); // this removes the file
  }
}
function SaveImage($type, $im, $filename, $quality, $to_file = true) {

  $res = null;

  // ImageGIF is not included into some GD2 releases, so it might not work
  // output png if gifs are not supported
  if(!function_exists('imagegif')) $type = 3;

  switch ($type) {
    case 1:
      if ($to_file) {
        $res = ImageGIF($im,$filename);
      }
      else {
        header("Content-type: image/gif");
        $res = ImageGIF($im);
      }
      break;
    case 2:
      if ($to_file) {
        $res = ImageJPEG($im,$filename,$quality);
      }
      else {
        header("Content-type: image/jpeg");
        $res = ImageJPEG($im, NULL, $quality);
      }
      break;
    case 3:
      if (PHP_VERSION >= '5.1.2') {
        // Convert to PNG quality.
        // PNG quality: 0 (best quality, bigger file) to 9 (worst quality, smaller file)
        $quality = 9 - min( round($quality / 10), 9 );
        if ($to_file) {
          $res = ImagePNG($im, $filename, $quality);
        }
        else {
          header("Content-type: image/png");
          $res = ImagePNG($im, NULL, $quality);
        }
      }
      else {
        if ($to_file) {
          $res = ImagePNG($im, $filename);
        }
        else {
          header("Content-type: image/png");
          $res = ImagePNG($im);
        }
      }
      break;
  }
  return $res;
}

/**
 * @param unknown_type $type
 * @param unknown_type $filename
 * @return Ambigous <NULL, resource>
 */
function ImageCreateFromType($type,$filename) {
  $im = null;
  switch ($type) {
    case 1:
      $im = ImageCreateFromGif($filename);
      break;
    case 2:
      $im = ImageCreateFromJpeg($filename);
      break;
    case 3:
      $im = ImageCreateFromPNG($filename);
      break;
  }
  return $im;
}

/**************************************************************/
function re_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link'){
  global $$link;

  if (USE_PCONNECT == 'true') {
    $$link = mysql_pconnect($server, $username, $password);
  } else {
    $$link = mysql_connect($server, $username, $password);
  }

  if ($$link) mysql_select_db($database);

  return $$link;
}

function re_db_close($link = 'db_link') {
  global $$link;

  return mysql_close($$link);
}

function re_db_error($query, $errno, $error) {
  die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[RE STOP]</font></small><br><br></b></font>');
}

function re_db_query($query, $link = 'db_link') {
  global $$link,$lastQuery;
	

  if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {
    error_log('QUERY ' . $query . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
  }
   
  $_start = explode(' ', microtime());
  $lastQuery['query'] = $query;
  $lastQuery['time'] = date('Y-m-d H:i:s.u');
  
  $result = mysql_query($query, $$link) or re_db_error($query, mysql_errno(), mysql_error());
  $_end = explode(' ', microtime());
  $_time = number_format(($_end[1] + $_end[0] - ($_start[1] + $_start[0])), 8);

  if ( defined('EXPLAIN_QUERIES') && (EXPLAIN_QUERIES == 'true') )
  {
    /* Initially set to store every query */
    $explain_this_query = true;
    /* If the include filter is true just explain queries for those scripts */
    if ( defined('EXPLAIN_USE_INCLUDE') && (EXPLAIN_USE_INCLUDE == 'true') )
    {
      $explain_this_query = ( ( stripos( EXPLAIN_INCLUDE_FILES, basename($_SERVER['PHP_SELF']) ) ) === false ? false : true );
    }
    /* If the exclude filter is true just explain queries for those that are not listed */
    if ( defined('EXPLAIN_USE_EXCLUDE') && (EXPLAIN_USE_EXCLUDE == 'true') )
    {
      $explain_this_query = ( ( stripos( EXPLAIN_EXCLUDE_FILES, basename($_SERVER['PHP_SELF']) ) ) === false ? true : false );
    }
    /* If it still true after running it through the filters store it */
    if ($explain_this_query) re_explain_query($query, $_time);
  } # End if EXPLAIN_QUERIES

  if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true'))
  {
    $result_error = mysql_error();
    error_log('RESULT ' . $result . ' ' . $result_error . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
  }
  return $result;
}

function re_explain_query($query, $_time, $link = 'db_link') {
  global $$link;
  /* Makes sure it's a select query and it's not for a session */
  if ( stristr($query, 'select') && !stristr($query, 'sessions') ) {
    /* Add the EXPLAIN to the query */
    $explain_query = 'EXPLAIN ' . $query;
    $_query = array('explain_id' => '', # Leave blank to get an autoincrement
        'md5query' => md5($query), # MD5() the query to get a unique that can be indexed
        'query' => $query, # Actual query
        'time' => $_time*1000, # Multiply by 1000 to get milliseconds
        'script' => basename($_SERVER['PHP_SELF']), # Script name
        'request_string' => $_SERVER['QUERY_STRING'] # Query string since some pages are constructed from parameters
    );
    /* Merge the _query and explain arrays */
    $container = array_merge($_query, mysql_fetch_assoc(mysql_query($explain_query)));
    /* Break the array into components so elements can be wrapped */
    foreach($container as $column => $value){
      $columns[] = $column;
      $values[] = $value;
    }
    /* Wrap the columns and values */
    wrap($columns, '`');
    wrap($values);
    /* Implode the columns so they can be used for the insert query below */
    $_columns = implode(', ', $columns);
    $_values = implode(', ', $values);
    /* Insert the data */
    $explain_insert = "INSERT into `explain_queries` ($_columns) VALUES ($_values)";
    mysql_query($explain_insert) or re_db_error($explain_insert, mysql_errno(), mysql_error());
    /* unset some variables...clean as we go */
    unset( $_query, $container, $columns, $values, $_columns, $_values );
  }
}


function re_db_fetch_array($db_query) {

  return mysql_fetch_array($db_query, MYSQL_ASSOC);
}

function re_db_num_rows($db_query) {
  return mysql_num_rows($db_query);
}

function re_db_affected_rows($db_query) {
  return mysql_affected_rows($db_query);
}
function re_db_data_seek($db_query, $row_number) {
  return mysql_data_seek($db_query, $row_number);
}

function re_db_insert_id() {
  return mysql_insert_id();
}

function re_db_free_result($db_query) {
  return mysql_free_result($db_query);
}

function re_db_fetch_fields($db_query) {
  return mysql_fetch_field($db_query);
}

function re_db_output($string) {
  $string= stripslashes($string);
  return $string;//htmlspecialchars($string);
}



function re_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
  reset($data);
  if ($action == 'insert') {
    $query = 'insert into ' . $table . ' (';
    while (list($columns, ) = each($data)) {
      $query .= $columns . ', ';
    }
    $query = substr($query, 0, -2) . ') values (';
    reset($data);
    while (list(, $value) = each($data)) {
      switch ((string)$value) {
        case 'now()':
          $query .= 'now(), ';
          break;
        case 'CURRENT_DATE()':
          $query .= 'CURRENT_DATE(), ';
          break;
        case 'null':
          $query .= 'null, ';
          break;
        default:
          if(substr(re_db_input($value),0,23)=="date_add(CURRENT_DATE()")
          {
            $query .= re_db_input($value) .', ';
          }
          else{
            $query .= '\'' . re_db_input($value) . '\', ';
          }
          break;
      }
    }
    $query = substr($query, 0, -2) . ')';
  } elseif ($action == 'update') {
    $query = 'update ' . $table . ' set ';
    while (list($columns, $value) = each($data)) {
      switch ((string)$value) {
        case 'now()':
          $query .= $columns . ' = now(), ';
          break;
        case 'CURRENT_DATE()':
          $query .= 'CURRENT_DATE(), ';
          break;
        case 'null':
          $query .= $columns .= ' = null, ';
          break;
        default:
          if(substr(re_db_input($value),0,23)=="date_add(CURRENT_DATE()")
          {
            $query .= re_db_input($value) .', ';
          }
          else{
            $query .= $columns . ' = \'' . re_db_input($value) . '\', ';
          }
          break;
      }
    }
    $query = substr($query, 0, -2) . ' where ' . $parameters;
  }
  //echo $query."<br>";
  //die();
  return re_db_query($query, $link);
}

function re_get_all_get_params($exclude_array = '')
{
  if(!is_array($exclude_array)) $exclude_array = array();

  $get_url = '';
  if (is_array($_GET) && (sizeof($_GET) > 0))
  {
    reset($_GET);
    while (list($key, $value) = each($_GET))
    {
      if ( (strlen($value) > 0) && ($key != re_session_name()) && ($key != 'error') && (!in_array($key, $exclude_array)) && ($key != 'x') && ($key != 'y') )
      {
        $get_url .=  '&' .$key . '=' . rawurlencode(stripslashes($value)) ;
      }
    }
  }
  return $get_url;
}

function re_session_name($name = '') {
  if (!empty($name)) {
    return session_name($name);
  } else {
    return session_name();
  }
}

function re_redirect($url)
{
  header("Location:".$url);
  exit;
}
function re_redirect_js($url)
{
  $str='<script language="javascript" type="text/javascript">';
  $str.='window.location=\''.$url.'\';';
  $str.='</script>';
  echo $str;
  exit;

}
function get_users($status="1")
{
  $rec=re_db_query("select * from `ems_users` where `status`='$status' order by name");
  while($res=re_db_fetch_array($rec))
  {
    $final[]=$res;
  }

  return $final;
}
function get_between_users($eid)
{
  //	echo "select * from `ems_expense_to_users` e2u,`ems_users` u where e2u.user_id=u.id and e2u.expense_id='".$eid."' order by u.name";
  $rec=re_db_query("select *,u.id as uid from `ems_expense_to_users` e2u,`ems_users` u where e2u.user_id=u.id and e2u.expense_id='".$eid."' order by u.name");
  while($res=re_db_fetch_array($rec))
  {
    $final['name'][]=$res['name'];
    $final['uid'][]=$res['uid'];
    $final['email'][]=$res['email'];
    $final['name_phe'][]=$res['name']." (".$res['phe'].")";
  }
  return $final;
}
function undo_expense($eid,$type="update")
{
  $users=get_between_users($eid);
  $total_users=count($users['uid']);
  $rec=re_db_query("select * from `ems_expense` where id='$eid'");
  if($res=re_db_fetch_array($rec))
  {
    $per_head=round((float)$res[amount]/(int)$total_users,2);
    re_db_query("UPDATE  `ems_account` SET  `amount` = `amount` - '$res[amount]' WHERE  `ems_account`.`id` ='".$res['paid_by']."' LIMIT 1");
    foreach($users['uid'] as $k=>$v)
    {
      re_db_query("UPDATE  `ems_account` SET  `amount` = `amount` + '$per_head' WHERE  `ems_account`.`id` ='".$v."' LIMIT 1");
    }
    re_db_query("delete from `ems_expense_to_users` where `expense_id` = '$eid'");
  }
  if($type=="delete")
  {
    re_db_query("delete from `ems_expense` where `id` = '$eid'");
  }
}
function send_email($to,$to_name,$from,$from_name,$subject,$content)
{
  require_once(INCLUDE_PATH."class.phpmailer.php");

  $mail = new PHPMailer(true);
 
  if(is_array($to))
  {
    foreach($to as $k=>$v)
    {
      $mail->AddAddress($v, $to_name[$k]);
    }
  }
  else
  {
    $mail->AddAddress($to, $to_name);
  }

  $mail->SetFrom($from, $from_name);
  
  $mail->Subject = $subject;
  $mail->IsHTML(true);
  $mail->MsgHTML($content);
  
  $mail->Send();
}


/**
 * Function callback to insert data in database.
 * @param unknown_type $tb
 * @param unknown_type $data
 * @return number
 */
function re_db_insert($tb,$data){
  if(is_array($data) && count($data)>0)
  {
    $tbinfo=re_db_query("SHOW FIELDS FROM `$tb`");
    while ($res=mysql_fetch_array($tbinfo))
    {
      $tbfieldtype[$res['Field']]=$res['Type'];
    }
    foreach ($data as $k=>$v)
    {

      if(strpos($tbfieldtype[$k], "double")===false ){
        $fields[]="`".$k."`";
        if(!is_numeric($v))
          $values[]="'".re_db_input($v)."'";
        else
          $values[]="'".$v."'";
      }else if(is_numeric($v)){
        $fields[]="`".$k."`";
        $values[]=$v;
      }
    }
    $query.="INSERT INTO `".$tb."`
    (".implode(",", $fields).")
    VALUES
    (".implode(",", $values).")";
    re_db_query($query);
    return re_db_insert_id();
  }
}
/**
 * function callback to update record in database.
 * @param unknown_type $tb
 * @param unknown_type $data
 * @param unknown_type $where
 */
function re_db_update($tb,$data,$where="")
{
	
  if(is_array($data) && count($data)>0)
  {
    $tbinfo=re_db_query("SHOW FIELDS FROM `$tb`");
    while ($res=mysql_fetch_array($tbinfo))
    {
      $tbfieldtype[$res['Field']]=$res['Type'];
    }
    foreach ($data as $k=>$v)
    {
      if(strpos($tbfieldtype[$k], "double")===false ){
        if(!is_numeric($v))
          $fields[]="`".$k."`='".re_db_input($v)."'";
        else
          $fields[]="`".$k."`='".$v."'";
      }else {
        $fields[]="`".$k."`=".$v;
      }
    }
    $query.="UPDATE `".$tb."` SET ".implode(",", $fields)." ";

    if(isset($where) && $where!="")
      $query.="WHERE ".$where;

      
    re_db_query($query);
  }
}

function re_db_select($tb,$fields,$where=""){
	global $lastQuery;	
  if(is_array($fields) && count($fields)>0){
    $query.="SELECT ".implode(",", $fields)." FROM ".$tb." ";
    if(isset($where) && $where!="")
      $query.="WHERE ".$where;
      
      $lastQuery['query']=$query;
      $lastQuery['time']=date('Y-m-d H:i:s.u');
      
    $rec = mysql_query($query);
// echo $query."<br/>";
    if(re_db_num_rows($rec)>0){
      while ($res=re_db_fetch_array($rec)){
        $result[]=$res;
      }
      return $result;
    }
    else {
      return false;
    }
  }
}

function re_db_delete($tb,$where=""){
  $query.="DELETE FROM ".$tb." ";
  if(isset($where) && $where!="")
    $query.="WHERE ".$where;
  $rec = re_db_query($query);
}

function printJson($return, $exit=true){
	echo json_encode($return);
	if ($exit){
		exit;
	}
}

function re_db_last_query($exit=false){
	global $lastQuery;
	return $lastQuery;
}
function addKey($key, $domain){
		$keys = re_db_select("`keys`", array("`key`"), "`domain` = '".re_db_input($domain)."' and `key` = '".re_db_input($key)."'");
		if (!$keys){
			re_db_insert("keys", array("key"=>re_db_input($key),"domain"=>re_db_input($domain)));
		}
	}
	
function addURL($domain){
		$urls = re_db_select("`urls`", array("`url`"), "`url` = '".re_db_input($domain)."'");
		if (!$urls){
			re_db_insert("urls", array("url"=>re_db_input($domain)));
		}
	}
	
function getHistoryAcc($his){
	$isJson = false;
	if(isJson($his['response'])){
		$isJson = true;
	}
	$str.="<h3><b>".$his['method']."</b> : ".$his['url']." [".date('Y-m-d H:i:s',$his['time'])."] <b><a class='openWithPoster' hisid='".$his['id']."' style='color:blue;'>Open with Poster</a></b></h3>";
    				$str.='<div>
    		<div id="hisTabs" class="hisTabsClass his_'.$his['id'].'">
			  <ul>
			    <li><a href="#hisTabs-1">Params</a></li>
			    <li><a href="#hisTabs-2">Raw</a></li>';
					
    				if(!$isJson){
    					$str.='<li><a href="#hisTabs-3">HTML</a></li>';
    				}
			    
    				if($isJson){
    					$str.='<li><a href="#hisTabs-4">JSON Viewer</a></li>';
    				}
			  $str.='</ul>
			  <div id="hisTabs-1">
			  	<table>';
    				$keyValues = unserialize($his['params']);
    				if ($keyValues){
    					foreach ($keyValues as $k=>$v){
    						$str.='<tr>
						  			<td><b>'.$k.'</b></td>
						  			<td>'.$v.'</td>
						  		</tr>';
    					}
    				} 
    				
			  	$str.='</table>
			  </div>
			  
			  <div id="hisTabs-2">
			  		<code>'.htmlentities($his['response']).'</code>
			  </div>';
					if(!$isJson){
    					$str.='<div id="hisTabs-3">
							 <iframe src="printHTML.php?his='.$his['id'].'" style="width:100%;border:1px solid #000;"></iframe>
						</div>';
    				}
			  
					if($isJson){
						$str.='<div id="hisTabs-4" class="hisTabs-4">
						  		'.$his['response'].'
						  </div>';    					
    				}
			$str.='</div> 
  		</div>';
return $str;	
}
function getHeaderCombo($sel=""){
	$str.='<select style="width:425px;" name="header_key[]">';
	$headers = re_db_select("header_key", array("id","header_type"));
	foreach ($headers as $k=>$v){
		$str.='<option value="'.$v['header_type'].'"';
		if($sel == $v['header_type']){
			$str.=' selected = "selected"';
		}
		$str.=' >'.$v['header_type'].'</option>';
	}
	$str.="</select>";
	return $str;
}

function isJson($string) {
 json_decode($string);
 return (json_last_error() == JSON_ERROR_NONE);
}
function has_in_history($data){
	$res = re_db_select("history", array("id"), "url='".$data['url'] . "' AND method='".$data['method']."' AND params='".$data['params']."'");
	if($res !== false){
		return $res[0]['id'];
	}else{
		return false;
	}
}
?>