<?php
require_once("../setup.php");
$title = "Recording/previewing video";
$video = "";
if (isset($_REQUEST["feature"]) && $_REQUEST["feature"]!="")
{
	$feature = $_REQUEST["feature"];	
}
else {
	$feature = "record";
}

$type = "";
if (isset($_REQUEST["type"]) && $_REQUEST["type"]!="")
{
	$type = $_REQUEST["type"];	
}
else {
	$type = "record";
}
$video = "";
if (isset($_REQUEST["vidfile"]) && $_REQUEST["vidfile"]!="")
{
	$video = $_REQUEST["vidfile"];	
}
else {
	$video = NULL;
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
				$arguments = "type=$type";
				if ($video!=NULL)
					$arguments.= "&vidfile=$video";
			?>
				refreshPage('playerContent', "<?php echo SITE_BASE ?>recordOrPreview/preview.php","<?php echo $arguments ?>", "none");
			<?php
			}
   		?>
   	</script>
</div>	
 