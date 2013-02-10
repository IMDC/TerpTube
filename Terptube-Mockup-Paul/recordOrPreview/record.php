<?php
require_once("../setup.php");
    if (isset($_POST["flashVars"]) && $_POST["flashVars"]!="")
{
	$flashVars = $_POST["flashVars"];	
	$flashVarsParsed = "";
	foreach ($flashVars as $key => $value) {
		$flashVarsParsed.=$key."=".$value."&";
	}
	$flashVarsParsed = substr($flashVarsParsed,0, strlen($flashVarsParsed)-1);
}
else
{
	$flashVarsParsed = "";
}
?>

<script type="text/javascript" src="<?php echo SITE_BASE ?>js/recordOrPreview/densityBar.js"></script>
	<script type="text/javascript">
		$(document).ready()
		{
			flashRecorder = flashembed("flashContent", {
				src:"<?php echo SITE_BASE ?>recordOrPreview/WebcamRecorderClient.swf", 
				id:"flashContentObject",
				quality: "high",
				bgcolor: "#ffffff",
				allowFullScreen: "true",
				wMode: "transparent",
				allowScriptAccess: "sameDomain",
				version: [11, 0],
				flashVars: "<?php echo $flashVarsParsed?>&jsObj=controls"
			});
		}
	</script>
	
<div class="record-or-preview video" id="videoContainer">	
	<div class="record-or-preview" id="flashContent">
    	<p> 
    		Either scripts and active content are not permitted to run or Adobe Flash Player version
    		11.1.0 or greater is not installed.
    	</p>
        <a href="http://www.adobe.com/go/getflashplayer">
            <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash Player" />
        </a>
    </div>
</div>
<script type="text/javascript">
	var controls = new DensityBar("videoContainer","flashContentObject");
	controls.options.backFunction= function(){closeRecorderPopUp('videoRecordingOrPreview')};
	controls.options.forwardFunction = function (){ controls.recording_goToPreviewing()};
	controls.options.volumeControl = false;
	controls.options.audioBar = false;
	controls.options.type = DENSITY_BAR_TYPE_RECORDER;
	controls.createControls();
</script>
