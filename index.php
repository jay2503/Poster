<?php 
	require_once 'config.php';
	if(isset($_SESSION['url']) && $_SESSION['url']!=""){
		$parseURL = parse_url($_SESSION['url']);
		
		$keys = re_db_select("`keys`", array("`key`"),"domain = '".$parseURL['host']."'");
		
		$tags = array();
		if(count($keys) > 0){
			foreach ($keys as $key){
				$tags[]="'".$key['key']."'";
			}
		}
	}
	
	$method = "GET";
	if (isset($_SESSION['method']) && $_SESSION['method']!=""){
		$method = $_SESSION['method'];
	}
?>
<!doctype html>
<html lang="us">
<head>
	<meta charset="utf-8">
	<title>Poster App By Jay Mehta</title>
	<link href="JqueryUI/css/smoothness/jquery-ui-1.9.2.custom.css" rel="stylesheet">
	<link href="style.css" rel="stylesheet">
	<script src="JqueryUI/js/jquery-1.8.3.js"></script>
	<script src="shortcut.js"></script>
	<script src="custom.js"></script>
	<script src="JqueryUI/js/jquery-ui-1.9.2.custom.js"></script>
<!--	<script src="JqueryUI/js/jqueryui.widget.combobox.js"></script>-->
<!--	<link href="JqueryUI/css/jqueryui.widget.combobox.css" rel="stylesheet">-->
	
	<script type="text/javascript">
		var isMac = navigator.platform.toUpperCase().indexOf('MAC')!==-1;
		var shortKey = isMac ? "Meta+":"Ctrl+";
		var shortDisp = ""
		if(shortKey == 'Meta+'){
			 shortDisp = 'Cmd+';
		 }else{
			 shortDisp = 'Ctrl+';
		 }
		var keys = <?php echo "[".implode(", ", $tags)."]";?>;

		function autoComplete(){
			$("#tblParams tr").each(function(){
				$(this).find("td:first input").live("focus",function(){
					$(this).autocomplete({
				   		source: keys,
				   		minLength: 0,
					});
				});
			});
		}
		var rowTemplate = '<tr>'
			+'<td><input type="text" name="key[]" style="width:425px;" title="Key"/></td>'
			+'<td><input type="text" name="value[]" style="width:425px;" title="Value"/></td>'
			+'<td>'
			+'<ul class="ui-widget ui-helper-clearfix" id="icons">'
			+'<li class="ui-state-default ui-corner-all" title=".ui-icon-plusthick" action="+"><span class="ui-icon ui-icon-plusthick"></span></li>'
			+'<li class="ui-state-default ui-corner-all" title=".ui-icon-minusthick" action="-"><span class="ui-icon ui-icon-minusthick"></span></li>'
			+'</ul>'
			+'</td>'
			+'</tr>';

		var rowTemplateHeader ='<tr>'
				+'<td>'
				+'<?php echo getHeaderCombo();?>'
				+'</td>'
				+'<td><input type="text" name="header_value[]" style="width:425px;" title="Value" value=""/></td>'
				+'<td>'
				+'<ul class="ui-widget ui-helper-clearfix" id="icons">'
				+'<li class="ui-state-default ui-corner-all" title=".ui-icon-plusthick" action="+" tbl="header"><span class="ui-icon ui-icon-plusthick"></span></li>'
				+'<li class="ui-state-default ui-corner-all" title=".ui-icon-minusthick" action="-" tbl="header"><span class="ui-icon ui-icon-minusthick"></span></li>'
				+'</ul>'
				+'</td>'
				+'</tr>';
			
		$(document).ready(function(){
			$(document).ajaxSend(function(event, request, ajaxOptions) {
				var url = ajaxOptions.url;
				if(url.replace(/^.*\/|\.[^.]*$/g, '') != "getURLs" && url.replace(/^.*\/|\.[^.]*$/g, '') != "getKeys"){
					addLoader();
				}
			});
			$(document).ajaxStop(function() {
				removeLoader();
			});
			
			$("input[name=url]").blur(function(){
					$.get('getKeys.php?url='+$(this).val(),function(data){
						keys = eval(data);
					});
				});
			$("#method").buttonset();
			$("#method").change(function(){
				$("#methodType").html($("input[name=method]:checked").val()+' :&nbsp;');
			})

			autoComplete();	 
		    
			$("#icons li").live("click",function(i){
				var iRow = $(this).parent().parent().parent().index();
				if($(this).attr("action") == "+"){
					if($(this).attr("tbl") == "header"){
						$("#tblHeader tr:eq("+iRow+")").after(rowTemplateHeader);
					}else{
						$("#tblParams tr:eq("+iRow+")").after(rowTemplate);
						autoComplete();
					}
				}else if($(this).attr("action") == "-"){
					if($(this).attr("tbl") == "header"){
						if($("#tblHeader tr").size() > 1){
							$("#tblHeader tr:eq("+iRow+")").remove();
							$("#tblHeader tr:eq("+(iRow-1)+") td:first select").focus();
						}else{
							$("#tblHeader tr:eq("+iRow+") input").each(function(){
								$(this).val("");
							});
						}
					}else{
						if($("#tblParams tr").size() > 1){
							$("#tblParams tr:eq("+iRow+")").remove();
							$("#tblParams tr:eq("+(iRow-1)+") td:first input").focus();
						}else{
							$("#tblParams tr:eq("+iRow+") input").each(function(){
								$(this).val("");
							});
						}
							
					}
					
				}
			});

			$("#tblParams input").live("focus",function(){
				var iIndex = $("#tblParams input:focus").parent().parent().index();
				var iCount = $("#tblParams tr").size();
				
				if(iIndex == (iCount-1)){
					$("#tblParams tr:last td:last ul li:first").click();
					autoComplete();
				}
			});
			shortcut.add(shortKey+"P",function(){
				$("#tblParams tr:last td:last ul li:first").click();
				$("#tblParams tr:last td:first input[type=text]").focus();
				autoComplete();
			});

			shortcut.add(shortKey+"M",function(){
				var iIndex = $("#tblParams input:focus").parent().parent().index();
				if(iIndex>-1){
					$("#tblParams tr:eq("+iIndex+") td:last ul li:last").click();
				}else{
					$("#tblParams tr:last td:last ul li:last").click();
				}
			});
			shortcut.add(shortKey+"R",sendRequest);
			shortcut.add(shortKey+"1",function(){
				$("#tabs").tabs({ active: 0 });
			});
			shortcut.add(shortKey+"2",function(){
				$("#tabs").tabs({ active: 1 });
			});

			shortcut.add(shortKey+"Alt+C",function(){$("#clearAll").click();});

			$("#sendRequest").button().click(sendRequest);

			if("<?php echo $method;?>" == "GET"){
				$("input[name=method]:eq(0)").attr("checked",true);
			}else{
				$("input[name=method]:eq(1)").attr("checked",true);
			}
			$("#methodType").html('<?php echo $method;?> :&nbsp;');
			
			$('#method').buttonset("refresh");

			$("#clearAll").html("Clear All");
			$("#clearAll").after("&nbsp;("+shortDisp+"Alt+C)");
			
			
			$("#clearAll").click(function(){
					$("#tblParams tr").each(function(){
							$(this).find("input").each(function(){
									$(this).val("");
								});
						});
					//Clear Session also
					$.get('clearParam.php',function(){});
				});

			$("#tabs, #hisTabs, #subtabs").tabs();

			$("#tabs").tabs({select: function(event, ui) {
				window.location.replace(ui.tab.hash);
		    },});

			$( "#accordion" ).accordion({
			     collapsible: true,
			     heightStyle: "content"
			 }).find('a.openWithPoster').click(function(ev){
				 	ev.preventDefault();
				    ev.stopPropagation();
				    openWithPoster($(this).attr("hisid"));
				 });

			 // URL Auto
				$("input[name='url']").autocomplete({
			      source: "getURLs.php",
			      minLength: 2,
			      select: function( event, ui ) {
			        
			      }
			    });

				 $("#dialog-confirm").dialog({
				      autoOpen: false,
				 });

				 
				 // shortcutList
				 var shlist = '<li>Add Param ('+shortDisp+'P)</li>'
			  			+'<li>Remove Param ('+shortDisp+'M)</li>'
			  			+'<li>Send Request ('+shortDisp+'R)</li>'
			  			+'<li>Select Poster Tab ('+shortDisp+'1)</li>'
			  			+'<li>Select History Tab ('+shortDisp+'2)</li>';
			  			
			  	$("#shortcutList").html(shlist);

			  	$("#clearHistory").click(function(){
			  		if($("#dialog-confirm").size() == 0){
						$("body").append("<div id = 'dialog-confirm'/>");
					}
					$("#dialog-confirm").html("Are you sure you want to clear history, you cannot be undone after ?");

					$("#dialog-confirm").dialog({
					      resizable: false,
					      height:"auto",
					      width:"auto",
					      modal: true,
					      buttons: {
								"Delete": function() {
					        	$.get('clearHistory.php',function(data){
									$("#accordion").html("");
								});
					        	$( this ).dialog( "close" );
						    },
					        "Cancel": function() {
					          $( this ).dialog( "close" );
					        },
					      }
					});
					$("#dialog-confirm").dialog( "open" );
				});		
		});
		var g;
		function openWithPoster(hisID){
			$.ajax({
				  url: "openWithPoster.php",
				  type: "GET",
				  data: "his="+hisID,
				  dataType: "json",
				  success: function(data){
				  g=data;
					$("input[name='url']").val(data['url']);
					$("#tblParams").html("");
					var t = g.params;
					for (var key in t) {
					  if (t.hasOwnProperty(key)) {
						if($("#tblParams tr").size()==0){
							$("#tblParams").html(rowTemplate);
						}else{
							$("#tblParams tr:last").after(rowTemplate);
						}
						$("#tblParams tr:last input:eq(0)").val(key);
						$("#tblParams tr:last input:eq(1)").val(t[key]);
						}
					}
					if($("#tblParams tr").size()==0){
						$("#tblParams").html(rowTemplate);
					}
					if(data.method == "GET"){
						$("input[name=method]:eq(0)").attr("checked",true);
					}else{
						$("input[name=method]:eq(1)").attr("checked",true);
					}
					$("input[name=method]").change();
					$('#method').buttonset("refresh");
					$("#tabs").tabs({ active: 0 });
					$("input[name=url]").blur();
				  },
				});
		}
		function sendRequest(){
			var error = "";
			if($("input[name='url']").val() == ""){
				error+="&not;Enter URL<br/>";
			}else if(!isURL($("input[name='url']").val())){
				error+="&not;Enter valid URL<br/>";
			}

			if(error!=""){
				showAlert(error);
				return false;
			}else{
				// Loop through params
				
				$.ajax({
					  url: "poster.php",
					  type: $("input[name=method]:checked").val(),
					  data: $("#posterForm").serialize(),
					  dataType: "json",
					  success: function(data){
						showAlertiFrame(data.id);
						// Add to history
						$('#accordion').prepend(data.history).accordion('destroy').accordion({
						     collapsible: true,
						     heightStyle: "content"
						 }).find('a.openWithPoster').click(function(ev){
							 	ev.preventDefault();
							    ev.stopPropagation();
							    openWithPoster($(this).attr("hisid"));
							 });
						$("#hisTabs").tabs();
					  },
					});
			}
		}
		function resizeIframe(obj) {
		    obj.style.height = (obj.contentWindow.document.body.scrollHeight + 60) + 'px';
		    obj.style.width = (obj.contentWindow.document.body.scrollWidth + 60) + 'px';
		    $("#dialog-confirm").parent().css("top",(($(window).height() - $(".ui-dialog").height())/2 < 0)?0:($(window).height() - $(".ui-dialog").height())/2);
		    $("#dialog-confirm").parent().css("left",($(window).width() - $(".ui-dialog").width())/2);
		}
		function showAlertiFrame(data){
			showAlert('<iframe src="printResponse.php?his='+data+'" style="max-height:500px;max-width:800px;border:0px solid #000;" onload="javascript:resizeIframe(this);"></iframe>');
		}
		function showAlert(msg){
			if($("#dialog-confirm").size() == 0){
				$("body").append("<div id = 'dialog-confirm'/>");
			}
			$("#dialog-confirm").html(msg);
			$("#dialog-confirm").dialog({
			      resizable: false,
			      height:"auto",
			      width:"auto",
			      modal: true,
			      buttons: {
			        "Ok": function() {
			          $( this ).dialog( "close" );
			        }
			      }
			});
			$("#dialog-confirm").dialog( "open" );
		}
	</script>
</head>
<body>
<div id="tabs">
  <ul>
    <li><a href="#tabs-1">Poster</a></li>
    <li><a href="#tabs-2">History</a></li>
  </ul>
  <div id="tabs-1">
  <div style="float:right;">
  			<ul id="shortcutList">
	  			
  			</ul>
    <h1><input type="button" name="send" id="sendRequest" value="Send"/></h1>
    </div>
<div style="float:left;">
	<form name="posterForm" id="posterForm">
	<table>
		<tr>
			<td style="width:100px;">URL</td>
			<td><span style="font-weight:bold" id="methodType"></span><input type="text" name="url" style="width:800px;" value="<?php echo $_SESSION['url'];?>"/></td>
		</tr>
		<tr>
			<td>Method</td>
			<td>
				<div id="method">
				    <input type="radio" id="get" name="method" value="GET" checked="checked"/><label for="get">Get</label>
				    <input type="radio" id="post" name="method"  value="POST" /><label for="post">Post</label>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<div id="subtabs">
					<ul>
					    <li><a href="#subtabs-1">Params</a></li>
					    <li><a href="#subtabs-2">Headers</a></li>
					  </ul>
					  <div id="subtabs-1">
					  	<table>
					  		<tr>
								<td colspan="2">
									To upload file, give full path of your file with prefix "@". e.g <b>Mac/Linux:</b> @/Users/jaym/upload.png <b>Win: </b> @C:\Users\jaym\upload.png 
									<br/><br/>&nbsp; <a href="javascript:void(0)" id="clearAll"></a></td>
							</tr>
							<tr>
								<td colspan="2">
									<table id="tblParams">
										<?php 
										if(count($_SESSION['key_value'])>0){
											foreach ($_SESSION['key_value'] as $key => $value){
												?>
										<tr>
											<td><input type="text" name="key[]" style="width:425px;" title="Key" value="<?php echo $key;?>"/></td>
											<td><input type="text" name="value[]" style="width:425px;" title="Value" value="<?php echo $value;?>"/></td>
											<td>
												<ul class="ui-widget ui-helper-clearfix" id="icons">
													<li class="ui-state-default ui-corner-all" title=".ui-icon-plusthick" action="+"><span class="ui-icon ui-icon-plusthick"></span></li>
													<li class="ui-state-default ui-corner-all" title=".ui-icon-minusthick" action="-"><span class="ui-icon ui-icon-minusthick"></span></li>
												</ul>
											</td>
										</tr>							
												<?php 
											}
										}else{
											?>
											<tr>
												<td><input type="text" name="key[]" style="width:425px;" title="Key"/></td>
												<td><input type="text" name="value[]" style="width:425px;" title="Value"/></td>
												<td>
													<ul class="ui-widget ui-helper-clearfix" id="icons">
														<li class="ui-state-default ui-corner-all" title=".ui-icon-plusthick" action="+"><span class="ui-icon ui-icon-plusthick"></span></li>
														<li class="ui-state-default ui-corner-all" title=".ui-icon-minusthick" action="-"><span class="ui-icon ui-icon-minusthick"></span></li>
													</ul>
												</td>
											</tr>
											<?php 
										}
										?>
									</table>
								</td>
							</tr>
					  	</table>
					  </div>
					  <div id="subtabs-2">
					  	<div><b>Note:</b> By default "Content Type" is set to "multipart/form-data". To change default value give new value explicitly from below list</div>
					  	<table id="tblHeader">
					  	<?php 
					  					if(count($_SESSION['header_key_value'])>0){
											foreach ($_SESSION['header_key_value'] as $key => $value){
												?>
												<tr>
								<td>
									<?php 
									$parts = explode(":", $value);
									echo getHeaderCombo($parts[0]);?>
								</td>
								<td><input type="text" name="header_value[]" style="width:425px;" title="Value" value="<?php echo ltrim($parts[1]);?>"/></td>
								<td>
									<ul class="ui-widget ui-helper-clearfix" id="icons">
										<li class="ui-state-default ui-corner-all" title=".ui-icon-plusthick" action="+" tbl="header"><span class="ui-icon ui-icon-plusthick"></span></li>
										<li class="ui-state-default ui-corner-all" title=".ui-icon-minusthick" action="-" tbl="header"><span class="ui-icon ui-icon-minusthick"></span></li>
									</ul>
								</td>
							</tr>
													<?php 
											}
										}else{
											?>
												<tr>
								<td>
									<?php echo getHeaderCombo();?>
								</td>
								<td><input type="text" name="header_value[]" style="width:425px;" title="Value" value=""/></td>
								<td>
									<ul class="ui-widget ui-helper-clearfix" id="icons">
										<li class="ui-state-default ui-corner-all" title=".ui-icon-plusthick" action="+" tbl="header"><span class="ui-icon ui-icon-plusthick"></span></li>
										<li class="ui-state-default ui-corner-all" title=".ui-icon-minusthick" action="-" tbl="header"><span class="ui-icon ui-icon-minusthick"></span></li>
									</ul>
								</td>
							</tr>
													<?php 
										}
										?>
						  	
					  	</table>
					  </div>
				</div>
			</td>
		</tr>
		
		
	</table>
	</form>
</div>
<div style="clear:both;"></div>
  </div>
  <div id="tabs-2">
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
</div>
<div style="display: none;" id="ajax-loader"><div class="loader-image"></div></div>
<div>
	<h1>Poster App - Developed by <a href="http://www.linkedin.com/pub/jay-mehta/21/934/ab2" target="_blank">Jay Mehta</a></h1>
</div>
<div id = 'dialog-confirm'/>

</body>
</html>
