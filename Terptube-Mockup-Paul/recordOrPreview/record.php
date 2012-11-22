<?php
    if (isset($_POST["flashVars"]) && $_POST["flashVars"]!="")
{
	$flashVars = $_POST["flashVars"];	
	$flashVarsParsed = "";
	foreach ($flashVars as $key => $value) {
		$flashVarsParsed.=$key."=".$value."&";
	}
	$flashVarsParsed = substr($flashVarsParsed,0, strlen($flashVarsParsed)-1);
}
?>

	<script type="text/javascript" src="scripts/toolbox.flashembed.min.js"></script>
	<script type="text/javascript">
		$('head').append('<link rel="stylesheet" type="text/css" href="css/record.css" />');
		$(document).ready()
		{
			flashRecorder = flashembed("flashContent", {
				src:"WebcamRecorderClient.swf", 
				id:"flashContentObject",
				quality: "high",
				bgcolor: "#ffffff",
				allowFullScreen: "true",
				wMode: "transparent",
				allowScriptAccess: "sameDomain",
				version: [11, 0],
				flashVars: "<?php echo $flashVarsParsed?>"
			});
		}
	</script>
	<script type="text/javascript" src="scripts/record.js"></script>
	
	
	<div id="flashContent">
    	<p> 
    		Either scripts and active content are not permitted to run or Adobe Flash Player version
    		11.1.0 or greater is not installed.
    	</p>
        <a href="http://www.adobe.com/go/getflashplayer">
            <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash Player" />
        </a>
    </div>
	<div class="video" id="track" height="40px">
        <canvas class="track" id="densitybar" width="480px">
        </canvas>
        <canvas class="track" id="selectedRegion" class="recording" width="480px">
        </canvas>
        <canvas class="track" id="thumb" class="recording" width="480px">
        </canvas>
      	<div id="timeBox">0:00:00/0:00:00</div>
	</div>
	<div id="uploadProgress"></div>
    <div id="buttonsBar">
        <div id="backButtons">
        	<button id="backButton" type="button" onclick="javascript:recording_goBack();" ></button>
        </div>
        <div id="videoControls">
            <button id="recordButton" type="button" onclick="javascript:recording_toggleRecording();" ></button>
        </div>
        <div id="forwardButtons">
   	     	<button id="submitButton" type="button" onclick="javascript:recording_goToPreviewing();"></button>
        </div>
    </div>
