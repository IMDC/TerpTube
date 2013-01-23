<?php

require_once("../setup.php");

define(VIDEO_RESOLUTION_MINIMUM_STANDARD, "640x480");

function getFFMPEGPath()
{
	return "/usr/local/bin/ffmpeg";
}
	/**
	 * convert from FFMPEG Duration to seconds
	 * 
	 * @param time
	 *            in the format HH:MM:SS.mmm
	 * @return time in seconds
	 */
function parseFFMPEGTimeToSeconds($time)
{
	$array = preg_split("/[:.]/", $time);
	
	error_log("FFMPEG TIME PARSED: $array[0] $array[1] $array[2] $array[3]");
	$timeMilliseconds = $array[0]* 60 * 60 * 1000 + $array[1]*60*1000 + $array[2]*1000 + $array[3];
	return $timeMilliseconds / 1000.0;
}

	/**
	 * convert from time in seconds to FFMPEG String
	 * 
	 * @param time
	 *            in seconds
	 * @return String representation of time in the format HH:MM:SS,mmm
	 */
function parseSecondsToFFMPEGTime($time)
{
	$time = intval($time*1000);
	$mil = "" . ($time % 1000);
	$sec = "" . (($time / 1000) % 60);
	$min = "" . ((($time / 1000) / 60) % 60);
	$hrs = "" . ((($time / 1000) / 60) / 60) % 60;
	while (strlen($mil) < 3)
		$mil = "0" . $mil;
	while (strlen($sec) < 2)
		$sec = "0" . $sec;
	while (strlen($min) < 2)
		$min = "0" . $min;
	while (strlen($hrs) < 2)
		$hrs = "0" . $hrs;

	return $hrs . ":" . $min . ":" . $sec . "." . $mil;
}


function trimVideo($inputVideoFile, $outputVideoFile, $startTime, $endTime, $keepInputFile)
{
        $duration = $endTime - $startTime;
        $startTimeFFMPEG = parseSecondsToFFMPEGTime($startTime);
        $durationFFMPEG = parseSecondsToFFMPEGTime($duration);
        $ffmpeg = getFFMPEGPath();
		//TODO look into getting the output to see if the transcoding succeeded
        exec("$ffmpeg  -ss $startTimeFFMPEG -t $durationFFMPEG -i '".$inputVideoFile."' -codec:v copy -codec:a copy -y '".$outputVideoFile."'");
		
		//FIXME no permissions to delete file
		if (!$keepInputFile)
			unlink($inputVideoFile);

}

/**
 * Returns the duration of the video in seconds time format
 */
function getVideoDuration($inputVideoFile)
{
	$ffmpeg = getFFMPEGPath();
	$command = "$ffmpeg -i '".$inputVideoFile."' 2>&1";
	$output="";
	$result = exec($command, $output);
	$duration = "";	
	foreach ($output as $lineNumber => $lineText) 
	{
		if (($pos = strpos($lineText, "Duration:"))!==false)
		{
			$duration = substr($lineText, $pos+10);
			$duration = explode(",", $duration);
			$duration = $duration[0];
			break;
		} 
	}
	return parseFFMPEGTimeToSeconds("$duration");
}

function convertVideoToWEBM($inputVideoFile, $outputVideoFile, $keepAudio, $keepInputFile, $progressOutput)
{
	$ffmpeg = getFFMPEGPath();
//	echo "converting video";
	if ($keepAudio)
	{
			$command = "$ffmpeg -i '".$inputVideoFile."' -codec:a libvorbis -ar 22050 -b:a 64k -ac 1 -b:v 600k -qmin 10 -qmax 42 -quality good -s ". VIDEO_RESOLUTION_MINIMUM_STANDARD." -y '".$outputVideoFile."' 2>&1";
	}
	else
	{
			$command = "$ffmpeg -i '".$inputVideoFile."' -an -b:v 600k -s ".VIDEO_RESOLUTION_MINIMUM_STANDARD." -y '".$outputVideoFile."' 2>&1";	
	}
	$duration = getVideoDuration($inputVideoFile);
	error_log("Duration: ".$duration);
	$pointer = popen($command, "r");
	error_log("Started converting");
	while (($line = fgets($pointer, 300)) !== false)
	{
		//error_log("Inside loop: ".$line);
		//read the output and update the progress
		if (($index = strpos($line, "time=")) !== false)
		{
			$line= substr($line, $index+5);
			$line = substr($line,0, strpos($line, " "));
			$time = $line;
			$time = parseFFMPEGTimeToSeconds($time);
			$progressOutput =  ceil($time * 100 / $duration);
			error_log("Progress: ".$progressOutput);
		}
	}
	error_log("Progress: 100");
	if (!$keepInputFile)
		unlink($inputVideoFile);
		return ($duration);
}

function moveFile($inputVideoFile, $outputVideoFile)
{
	 return rename($inputVideoFile, $outputVideoFile); 
}

function tempnam_sfx($path, $suffix) 
   { 
      do 
      { 
         $file = $path."/".mt_rand().$suffix; 
      } 
      while(!($fp = @fopen($file, 'x+'))); 

      fclose($fp); 
      return $file; 
   } 
?>