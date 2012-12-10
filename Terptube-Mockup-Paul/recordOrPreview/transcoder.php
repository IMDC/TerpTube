<?php
require_once("transcodeFunctions.php");	
require_once("../setup.php");

$outputVideoDirectory= UPLOAD_DIR . 'temp/';;
$inputVideoDirectory= $outputVideoDirectory;
$trim = 'trim';
$move = 'move';
$inputVideoFile = 'inputVidFile';
//$outputVideoFile = 'outputVidFile';
$outputVideoFile = 'outputVidFile';
$startTime = 'startTime';
$endTime = 'endTime';
$keepInputFile = 'keepInputFile';

$convert = 'convert';
$keepAudio = 'keepAudio';

if (isset($_POST[$trim]) && $_POST[$trim]=="yes")
{  //trimming video
	if (isset($_POST[$inputVideoFile]) && isset($_POST[$outputVideoFile]))
	{
		$inputFile = $inputVideoDirectory.$_POST[$inputVideoFile];
		$outputVideoFile = $outputVideoDirectory.$_POST[$outputVideoFile];
		if (isset($_POST[$move]))
		{
			moveFile($inputFile, $outputVideoFile);
			echo basename($outputVideoFile); //returns the filename
		}
		else if(isset($_POST[$startTime]) && isset($_POST[$endTime]) && isset($_POST[$keepInputFile]))
		{ //Input is proper
			trimVideo($inputVideoDirectory.$_POST[$inputVideoFile], $outputVideoFile, $_POST[$startTime], $_POST[$endTime], $_POST[$keepInputFile]==true);
			echo basename($outputVideoFile); //returns the filename
		}
		else 
			echo "Something is wrong 1";
	}
	else 
		echo "Something is wrong 2";
}
else if (isset($_POST[$convert]) && $_POST[$convert]=="yes")
{
	//converting the video
	//echo "input:".isset($_POST[$inputVideoFile]) . "output:".isset($_POST[$outputVideoFile]) . "keepInput:".isset($_POST[$keepInputFile]) . "keepAudio:".isset($_POST[$keepAudio]);
	if(isset($_POST[$inputVideoFile]) && isset($_POST[$outputVideoFile]) && isset($_POST[$keepInputFile]) && isset($_POST[$keepAudio]))
	{
		error_log("Converting the video...");
		//input is proper
		$outputProgress = "";
		convertVideoToWEBM($_POST[$inputVideoFile],$_POST[$outputVideoFile], $_POST[$keepAudio]=="true",$_POST[$keepInputFile]=="true", $outputProgress);
		return "Success";
	}	
	else
		echo "Something is wrong 3";
}
else 
	echo "Fail";

?>