<?php
//TODO Add Rewind FF. - Rewind not working (works only on safari)
//TODO Add volume control on the right of the density bar
//TODO add current time/duration of selected video time on the right of the density bar
//TODO if video is coming from an upload, Convert it first and then display it.
require_once("transcodeFunctions.php");
require_once("../setup.php");
function getExtension($filename)
{
	return end(explode('.', $filename));	
}

function getVideoType($video)
{
	$extension = getExtension($video);	
	if ( $extension == "mp4")
	{
		return 'video/mp4';
	}
	else if ( $extension == "ogv")
	{
		return 'video/ogg';	
	}
	else
	{
		return 'video/webm';	
	}
}
$tempDirectory = 'streams/tempVideos';
//$tempDirectory = 'streams';
$postType = 'type';
$postParam = 'vidfile';
$keepVideoFileParam = 'keepvideofile';
$videosURL = "/~martin/webcamrecord/streams/";
$videoWidth = 640;
$videoHeight = 480;

$keepVideoFile = "";
$video = "";

/*foreach ($_POST as $key=>$value)
{
	echo "$key : $value <br />";
	
}
echo "done showing post information";
 */
if (isset($_POST[$keepVideoFileParam]) && $_POST[$keepVideoFileParam]!= '')
{
	$keepVideoFile = $_POST[$keepVideoFileParam];
}
else
{
	$keepVideoFile = "false";
}
if (!isset($_POST[$postType]) || $_POST[$postType]=='')
{
	//Should not happen
//	die("Cannot access this page directly");
}
$postType = $_POST[$postType];

if (isset($_POST[$postParam]) && $_POST[$postParam]!= '')
{
//	echo "$video";
	$video = $videosURL . $_POST[$postParam];
}
else
{
	//Testing purposes
	$video = "output.webm";
	$postType = "record";
//	echo "No file was specified";
//	die ;
}

if ($postType == 'upload')
{
	//transcode the video and then show it	
	//Convert the video and delete the original
	//FIXME need to make this AJAX
	$outputVideoFile = tempnam_sfx($tempDirectory, ".webm");
	$outputVideoFile = substr($outputVideoFile,strlen($tempDirectory)+1);
	convertVideoToWEBM($video,$_POST[$postParam], 'true', $keepVideoFile); 
	$video = $outputVideoFile;
	//Get a new output video File for after the cropping
	$outputVideoFile = tempnam_sfx($tempDirectory, ".webm");
}
else if ($postType == 'record')
{
	//All is set
}
else 
{
	//should not happen
	die("Missing type of preview");	
}
?>

<script type="text/javascript" src="js/recordOrPreview/preview.js"></script>


<div class="record-or-preview video" id="videoContainer">
    <video class="record-or-preview" onloadedmetadata="setupVideo()" id="video" width="<?php echo $videoWidth ?>px" height="<?php echo $videoHeight ?>px" controls="controls" preload="auto">
        <source src="<?php echo $video ?>" type="<?php echo getVideoType($video)?>">
        Browser cannot play video. 
    </video> 
    <div class="record-or-preview video" id="track">
        <canvas class="record-or-preview track" id="densitybar" >
        </canvas>
        <canvas class="record-or-preview track" id="selectedRegion" >
        </canvas>
        <canvas class="record-or-preview track" id="thumb" onMouseOver="javascript:setVolumeBarVisible(false)">
        </canvas>
      	<div class="record-or-preview" id="timeBox">0:00:00/0:00:00</div>
       	<div class="record-or-preview" id="volume"  onMouseOver="javascript:setVolumeBarVisible(true)">
       		<img class="record-or-preview" id="volumeImage" alt="volume control" src="images/audioOn.png"  onClick="javascript:toggleMute()"/>
       		<div class="record-or-preview" id="volumeSlider"></div>
       	</div>
	</div>
    <div class="record-or-preview" id="buttonsBar">
        <div class="record-or-preview" id="backButtons">
        	<button class="record-or-preview" id="backButton" type="button" onclick="javascript:goBack(<?php echo $postType?>);"></button>
        </div>
        <div class="record-or-preview" id="videoControls">
            <button class="record-or-preview" id="beginButton" type="button" onclick="javascript:jumpTo(0);"></button>
            <button class="record-or-preview" id="stepBackwardButton" type="button" onclick="javascript:stepBackward();"></button>
            <button class="record-or-preview" id="playButton" type="button" onclick="javascript:playPause();" ></button>
            
            <button class="record-or-preview" id="stepForwardButton" type="button" onclick="javascript:stepForward();"></button>
            <button class="record-or-preview" id="endButton" type="button" onclick="javascript:jumpTo(1);"></button>
         <!--   <button type="button" onclick="javascript:previewClip();" >Preview</button> -->
        </div>
        <div class="record-or-preview" id="forwardButtons">
   	     	<button class="record-or-preview" id="submitButton" type="button" onclick="javascript:transcodeAjax(<?php echo $video.",".$keepVideoFile ?>);"></button>
        </div>
        <div class="record-or-preview" id="audioButtonsBar">
        Remove audio from the video?<br />	<label for="audioOff"><img src="images/audioOff.png" width="30px" height="30px" alt="audio enabled" /> </label><input type="radio" name="audioEnabled" value="false" id="audioOff" />
            <label for="audioOn"><img src="images/audioOn.png" width="30px" height="30px" alt="audio enabled" /> </label><input type="radio" name="audioEnabled" value="true" class="record-or-preview" id="audioOn" checked="checked" />
             
        </div>
    </div>
</div>
