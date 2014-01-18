<?php
require_once 'config.php';

$his = re_db_select("history", array("id","response","params"),"id=".$_GET['his']);

?>
<link href="JqueryUI/css/smoothness/jquery-ui-1.9.2.custom.css" rel="stylesheet">
	<link href="style.css" rel="stylesheet">
	<link href="JSONViewer/jquery.jsonview.css" rel="stylesheet">
	<script src="JqueryUI/js/jquery-1.8.3.js"></script>
	<script src="JSONViewer/jquery.jsonview.js"></script>
	<script src="shortcut.js"></script>
	<script src="custom.js"></script>
	<script src="JqueryUI/js/jquery-ui-1.9.2.custom.js"></script>
	<style>
body{
	min-width: 700px;
}
</style>
<div id="responseTabs" class="responseTabs">
	<ul>
		<li><a href="#hisTabs-2">Raw</a></li>
		<?php 
		$isJson = false;
		if(isJson($his[0]['response'])){
			$isJson = true;
			
		}
			if($isJson){
    			echo '<li><a href="#hisTabs-4">JSON Viewer</a></li>';
    		}
		?>
		<?php 
		if(!$isJson){
    		echo '<li><a href="#hisTabs-3">HTML</a></li>';
    	}
		?>
		<li><a href="#hisTabs-1">Params</a></li>
	</ul>
	
	<div id="hisTabs-2">
		<code><?php echo htmlentities($his[0]['response']);?></code>		
	</div>
	
	<?php 
	if(!$isJson){
    	echo '<div id="hisTabs-3">
			 <iframe src="printHTML.php?his='.$his[0]['id'].'" style="width:100%;border:1px solid #000;"></iframe>
		</div>';
    }
	?>
	<?php 
		if($isJson){
    		?>
    		<div id="hisTabs-4" class="hisTabs-4">
				<?php echo $his[0]['response'];?>		
			</div>
    		<?php 
    	}		
	?>
	<div id="hisTabs-1">
		<?php echo '<table style="font-size: 12px;">';
    				$keyValues = unserialize($his[0]['params']);
    				if ($keyValues){
    					foreach ($keyValues as $k=>$v){
    						$str.='<tr>
						  			<td><b>'.$k.'</b></td>
						  			<td>'.$v.'</td>
						  		</tr>';
    					}
    				} 
    				
			  	$str.='</table>';
			  	
			  	echo $str;
		?>		
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	
	 $(".responseTabs").tabs({
		 activate: function( event, ui ) {
		 	if(ui.newPanel.hasClass("hisTabs-4")){
		 		var jsonData = ui.newPanel.html();
		 		if(isJson(jsonData)){
		 			ui.newPanel.JSONView($.trim(jsonData));
		 		}
		 	}
		 },create: function( event, ui ) {
			 if(ui.panel.hasClass("hisTabs-4")){
			 	var jsonData = ui.panel.html();
			 	if(isJson(jsonData)){
			 		ui.panel.JSONView($.trim(jsonData));
			 	}
			}
		}
	});
});
</script>