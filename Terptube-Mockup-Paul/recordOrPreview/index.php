<?php
$title = "Recording/previewing video";
 
if (isset($_GET["feature"]) && $_GET["feature"]!="")
{
	$feature = $_GET["feature"];	
}
else {
	$feature = "record";
}

?>
<!DOCTYPE html>

<html>	
    
    <head>
        <title><?php echo $title ?></title>         
        		
		
		<script type="text/javascript" src="scripts/jquery-1.8.2.js">
		</script>
		<script type="text/javascript" src="scripts/jquery-ui-1.9.0.custom.min.js">
		</script>
		<script type="text/javascript" src="scripts/videoManipulation.js">
		</script>
		<script type="text/javascript" src="scripts/modernizr.js">
		</script>    
		
		<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.9.0.custom.min.css" />
		<link rel="stylesheet" type="text/css" href="css/preview.css" />
    </head>
    <body>
       <script type="text/javascript">
       		loadPlayerFrame();
       </script>
		<div id="playerContent">        
	       	<script type="text/javascript">
	       		<?php
		       		if ($feature =="record")
					{
				?>
					var flashVars = {};
					flashVars.postURL = "preview.php";
                    flashVars.cancelURL = "javascript:history.go(-1)";
                    flashVars.isAjax = "true";
                    flashVars.blurFunction = "setBlur";
                    flashVars.blurFunctionText = "setBlurText";
                    
					var dataSend = {flashVars: flashVars};
					refreshPage("record.php", dataSend,"none")		
				<?php
					}
					else if ($feature =="preview")
					{
					?>
						refreshPage("preview.php","type=record", "none");
					<?php
					}
	       		?>
	       	</script>
	    </div>	
	    <input type="hidden" id="recOrPrevfileName" />
	    <div class="modal" id="modal"></div>
   </body>
</html>
