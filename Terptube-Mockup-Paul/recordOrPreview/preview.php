<?php
//TODO Add Rewind FF. - Rewind not working (works only on safari)

//FIXME add option to preview an existing video online to edit it. (record and replace the existing video)
require_once("transcodeFunctions.php");
require_once("../setup.php");

?>
<script type="text/javascript" src="<?php echo SITE_BASE ?>js/recordOrPreview/preview2.js"></script>


<?php
$tempDirectory = UPLOAD_DIR . 'temp';
$tempURL = SITE_BASE.'uploads' . DIRECTORY_SEPARATOR. "temp/";
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
		//setControlsEnabled(true);
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
    <video class="record-or-preview" id="video" width="<?php echo $videoWidth ?>px" height="<?php echo $videoHeight ?>px" preload="auto">
        <source id="video-source" src="<?php echo $video ?>" type="<?php echo getVideoType($video)?>">
        Browser cannot play video. 
    </video> 
</div>
<script type="text/javascript">

	var controls = new DensityBar("videoContainer","video");
	controls.options.backFunction= function(){if (confirm("This will delete your current recording. Are you sure?")) {goBack('<?php echo $postType?>');}};
	controls.options.forwardFunction = function (){ transcodeAjax('<?php echo basename($video) ?>', '<?php echo basename($outputVideoFile) ?>', <?php echo $keepVideoFile ?>, controls);};
	controls.options.areaSelectionEnabled = true;
	// controls.options.playHeadImage = "images/feedback_icons/round_plus.png";
	// controls.options.playHeadImageOnClick = function(){ alert("plus");};

	controls.createControls();
</script>
