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
	else if ( $extension == "webm" )
	{
		return 'video/webm';	
	}
	else {
		return 'video/unknown';
	}
}
?>

<script type="text/javascript" src="<?php echo SITE_BASE ?>js/recordOrPreview/preview.js"></script>

<?php
$tempDirectory = UPLOAD_DIR . 'temp';$tempURL = SITE_BASE.'uploads' . DIRECTORY_SEPARATOR. "temp/";
//$tempDirectory = 'streams';
$postType = 'type';
$postParam = 'vidfile';
$keepVideoFileParam = 'keepvideofile';
$videosURL = "/home/martin/public_html/webcamrecord/streams/";
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
	$video = $tempDirectory.DIRECTORY_SEPARATOR.$_POST[$postParam];
	$outputVideoFile = tempnam_sfx($tempDirectory, ".webm");
	//$outputVideoFile = basename($outputVideoFile);
//	convertVideoToWEBM($video,$outputVideoFile, 'true', $keepVideoFile); 
	$arguments = "'".$video."', '".$outputVideoFile."', { keepInputFile: '".$keepVideoFile."', keepAudio: 'true', convert: 'yes' }";
	$video = $tempURL.basename($outputVideoFile);
	//Get a new output video File for after the cropping
	$outputVideoFile = tempnam_sfx($tempDirectory, ".webm");
?>
<script type="text/javascript">
	transcodeAjax2(<?php echo $arguments?>, function(data){
		$("#video-source").attr("src","<?php echo $video ?>");
		setBlurText("");
		setBlur(false);
		setControlsEnabled(true);
		$("#video").load();
	}, function(data) {
		alert("Converting of video failed!");
	} );
</script>

<?php
	
	
}
else if ($postType == 'record')
{
	$video = $videosURL . $_POST[$postParam];
	//move file from original location to a temporary file in the temp directory
	$outputVideoFile = tempnam_sfx($tempDirectory, ".webm");
	$outputVideoFile = basename($outputVideoFile);
	moveFile($video,$tempDirectory.DIRECTORY_SEPARATOR.$outputVideoFile); 
	$video = $tempURL.$outputVideoFile;
	//Get a new output video File for after the cropping
	$outputVideoFile = tempnam_sfx($tempDirectory, ".webm");
}
else 
{
	//should not happen
	die("Missing type of preview");	
}
?>


<div class="record-or-preview video" id="videoContainer">
    <video class="record-or-preview" onloadedmetadata="setupVideo()" id="video" width="<?php echo $videoWidth ?>px" height="<?php echo $videoHeight ?>px" controls="controls" preload="auto">
        <source id="video-source" src="<?php echo $video ?>" type="<?php echo getVideoType($video)?>">
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
       		<img class="record-or-preview" id="volumeImage" alt="volume control" src="images/recordOrPreview/audioOn.png"  onClick="javascript:toggleMute()"/>
       		<div class="record-or-preview" id="volumeSlider"></div>
       	</div>
	</div>
    <div class="record-or-preview" id="buttonsBar">
        <div class="record-or-preview" id="backButtons">
        	<button class="record-or-preview preview" id="backButton" type="button" onclick="javascript:goBack('<?php echo $postType?>');"></button>
        </div>
        <div class="record-or-preview" id="videoControls">
            <button class="record-or-preview preview" id="beginButton" type="button" onclick="javascript:jumpTo(0);"></button>
            <button class="record-or-preview preview" id="stepBackwardButton" type="button" onclick="javascript:stepBackward();"></button>
            <button class="record-or-preview preview" id="playButton" type="button" onclick="javascript:playPause();" ></button>
            
            <button class="record-or-preview preview" id="stepForwardButton" type="button" onclick="javascript:stepForward();"></button>
            <button class="record-or-preview preview" id="endButton" type="button" onclick="javascript:jumpTo(1);"></button>
         <!--   <button type="button" onclick="javascript:previewClip();" >Preview</button> -->
        </div>
        <div class="record-or-preview" id="forwardButtons">
   	     	<button class="record-or-preview preview" id="submitButton" type="button" onclick="javascript:transcodeAjax('<?php echo basename($video) ?>', '<?php echo basename($outputVideoFile) ?>', <?php echo $keepVideoFile ?>);"></button>
        </div>
        <div class="record-or-preview" id="audioButtonsBar">
        Remove audio from the video?<br />	<label for="audioOff"><img src="images/recordOrPreview/audioOff.png" width="30px" height="30px" alt="audio enabled" /> </label><input type="radio" name="audioEnabled" value="false" id="audioOff" />
            <label for="audioOn"><img src="images/recordOrPreview/audioOn.png" width="30px" height="30px" alt="audio enabled" /> </label><input type="radio" name="audioEnabled" value="true" class="record-or-preview" id="audioOn" checked="checked" />
             
        </div>
    </div>
</div>
