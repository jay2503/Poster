<?php
	require_once 'config.php';
?>

<div>
  	<div><a href="javascript:void(0);" id="clearHistory">Clear History</a><br/><br/></div>
    <div id="accordion">
    	<?php 
    		$history = re_db_select("history", array("*"),"1=1 order by `time` desc limit 0,20");
    		
    		if ($history){
    			foreach ($history as $his){
    				$str.=getHistoryAcc($his);
    			}
    		}
    		echo $str;
    	?>
  	</div>
  </div>
  
  <script type="text/javascript">
$(document).ready(function(){
	$( "#accordion" ).accordion({
	     collapsible: true,
	     heightStyle: "content"
	 }).find('a.openWithPoster').click(function(ev){
		 	ev.preventDefault();
		    ev.stopPropagation();
		    openWithPoster($(this).attr("hisid"));
		 });

	 $(".hisTabsClass").tabs({
		 activate: function( event, ui ) {
		 	if(ui.newPanel.hasClass("hisTabs-4")){
		 		var jsonData = ui.newPanel.html();
				ui.newPanel.JSONView($.trim(jsonData));	
		 	}
		 }	
	});
});
</script>