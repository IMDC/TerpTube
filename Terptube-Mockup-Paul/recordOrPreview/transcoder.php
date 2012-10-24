<?php
require_once("transcodeFunctions.php");	

$inputVideoDirectory= '/home/martin/public_html/webcamrecord/streams/';
$outputVideoDirectory= '/home/martin/public_html/webcamrecord/streams/tempVideos/';
$trim = 'trim';
$inputVideoFile = 'inputVidFile';
//$outputVideoFile = 'outputVidFile';
$outputVideoFile = tempnam_sfx($outputVideoDirectory, ".webm");
$startTime = 'startTime';
$endTime = 'endTime';
$keepInputFile = 'keepInputFile';

$convert = 'convert';
$keepAudio = 'keepAudio';

if (isset($_POST[$trim]) && $_POST[$trim]=="yes")
{  //trimming video
	if(isset($_POST[$inputVideoFile]) && isset($_POST[$startTime]) && isset($_POST[$endTime]) && isset($_POST[$keepInputFile]))
	{ //Input is proper
			trimVideo($inputVideoDirectory.$_POST[$inputVideoFile], $outputVideoFile, $_POST[$startTime], $_POST[$endTime], $_POST[$keepInputFile]==true);
			echo substr($outputVideoFile,strlen($outputVideoDirectory)+1); //returns the filename
	}
	else 
		echo "Something is wrong";
}
else if (isset($_POST[$convert]) && $_POST[$convert]=="yes")
{
	//converting the video
	if(isset($_POST[$inputVideoFile]) && isset($_POST[$outputVideoFile]) && isset($_POST[$keepInputFile]) && isset($_POST[$keepAudio]))
	{
		//input is proper
		convertVideoToWEBM($_POST[$inputVideoFile],$outputVideoFile, $_POST[$keepAudio]=="true",$_POST[$keepInputFile]=="true");
		return "Success";
	}	
	else
		echo "Something is wrong";
}
else 
	echo "Fail";

?>