<?php
require_once("../setup.php");
$title = "Recording/previewing video";
$feature = "";
if (isset($_REQUEST["feature"]) && $_REQUEST["feature"]!="")
{
	$feature = $_REQUEST["feature"];	
}
else {
	$feature = "record";
}

$type = "";
if (isset($_REQUEST["$type"]) && $_REQUEST["$type"]!="")
{
	$type = $_REQUEST["$type"];	
}
else {
	$type = "record";
}



?>

<div class="record-or-preview" id="playerContent">        
   	<script type="text/javascript">
   		<?php
       		if ($feature =="record")
			{
		?>
			var flashVars = {};
//			flashVars.postURL = "<?php echo SITE_BASE ?>recordOrPreview/preview.php";
//           flashVars.cancelURL = "javascript:history.go(-1)";
//            flashVars.isAjax = "true";
//            flashVars.blurFunction = "setBlur";
//            flashVars.blurFunctionText = "setBlurText";
            
			var dataSend = {flashVars: flashVars};
			refreshPage('playerContent', "<?php echo SITE_BASE ?>recordOrPreview/record.php", dataSend,"none")		
		<?php
			}
			else if ($feature =="preview")
			{
			?>
				refreshPage('playerContent', "<?php echo SITE_BASE ?>recordOrPreview/preview.php","type=<?php echo $type ?>", "none");
			<?php
			}
   		?>
   	</script>
</div>	
<div class="record-or-preview modal" id="modal"></div>
 