var recording_trackPadding = 12;
var recording_trackWidth = $('#densitybar').width() - 2*recording_trackPadding;
//document.write(trackWidth);
var recording_trackHeight = $('#densitybar').height() - 2*recording_trackPadding;
var recording_currentTime = 0;
var recording_currentMinTimeSelected = 0;
var recording_currentMaxTimeSelected = 0;
var recording_durationSelected = 0;
var recording_minSelected = recording_trackPadding;
var recording_maxSelected = recording_trackPadding;
var recording_currentMinSelected = recording_minSelected;
var recording_currentMaxSelected = recording_maxSelected;
var recording_video;
var recording_triangleWidth = recording_trackPadding;
var recording_minTimeCoordinate = 0;
var recording_minTime = 3 * 1000; //seconds
var recording_preview = false;
var recording_stepSize = 0.1;
var recording_maxSpeed = 2.0;
var recording_recordTimer;
var recording_transcodeTimer;
var recording_maxRecordingTime = 60*1000; //60 seconds
var recording_minRecordingTime = 1000*3;
var recording_isRecording = false;

$("#densitybar")[0].width=$("#densitybar").width();
$("#densitybar")[0].height=$("#densitybar").height();
$("#thumb")[0].width=$("#thumb").width();
$("#thumb")[0].height=$("#thumb").height();
$("#selectedRegion")[0].width=$("#selectedRegion").width();
$("#selectedRegion")[0].height=$("#selectedRegion").height();

function recording_checkFeatures(format)
{
	if (format == "mp4")
		format = "h264";
	else if (format == "ogv")
		format = "ogg";
	if (!Modernizr.canvas || !Modernizr.video || !Modernizr.video[format])
		return false;
	return true;
}

function recording_drawTrack()
{
	var context = $("#densitybar")[0].getContext("2d");
	context.lineJoin = "round";
	context.fillStyle = "#cccccc";
	context.strokeStyle = "#000000"; 
	context.strokeRect(0,0, $('#densitybar').width(), $('#densitybar').height());
	context.fillRect(recording_trackPadding, recording_trackPadding, recording_trackWidth, recording_trackHeight);
	context.strokeRect(recording_trackPadding, recording_trackPadding, recording_trackWidth, recording_trackHeight);
	
}

function recording_updateTimeBox(currentTime, duration)
{
	$("#timeBox").html(getTimeCodeFromSeconds(currentTime) +"/"+getTimeCodeFromSeconds(duration));
}


function recording_paintThumb(time)
{
	var context = $("#thumb")[0].getContext("2d");
	var position = recording_getXForTime(time);
	if (time==0)
		position = recording_trackPadding;
	context.clearRect(0, 0, $('#densitybar').width(), $('#densitybar').height());
	context.fillStyle = "#000000";
	context.strokeStyle = "#000000";
	context.beginPath();
	context.moveTo(position - recording_trackPadding , 0);
	context.lineTo(position + recording_trackPadding , 0);
	context.lineTo(position, recording_trackPadding);
	context.closePath();
	context.fill();
	context.lineWidth = 2;
	context.beginPath();
	context.moveTo(position, 0);
	context.lineTo(position, $('#densitybar').height()-recording_trackPadding);
	context.closePath();
	context.stroke();
}

function recording_getXForTime(time)
{
	var x = recording_trackPadding + time/recording_maxRecordingTime* recording_trackWidth;
	return x;
}

function recording_getTimeForX(x)
{
	var time = (x - recording_trackPadding)*recording_maxRecordingTime/recording_trackWidth;
	return time;
}

function recording_checkStop()
{
	// if (video.paused)
		// return;
	var time = recording_getCurrentTime();
	if (time >=recording_minRecordingTime)
	{
		recording_setInputEnabled("recordButton", true);
	}
	if (time >= recording_maxRecordingTime)
	{
		recording_stopRecording();
	}
	recording_repaint();
}

function recording_toggleRecording()
{
	//Change icon on button
	if (recording_isRecording)
		recording_stopRecording();
	else
		recording_startRecording();	
	
}

function recording_startRecording()
{
	$("#recordButton")[0].style.backgroundImage = "url(images/rec2_small.gif)";
	recording_setInputEnabled("recordButton", false);
	recording_setInputEnabled("submitButton", false);
	recording_currentMinSelected = recording_minSelected;
	recording_currentMinTimeSelected = recording_getTimeForX(recording_currentMinSelected);
	recording_currentMaxSelected = recording_maxSelected;
	recording_currentMaxTimeSelected = recording_getTimeForX(recording_currentMaxSelected);
	recording_setHighlightedRegion(recording_currentMinSelected, recording_currentMaxSelected);
	recording_isRecording = true;
	
	$("#flashContentObject")[0].startRecording();
}

//Called by Flash when recording actually started
function recording_recordingStarted()
{
	recording_currentTime = new Date().valueOf();
	if (recording_recordTimer)
		clearInterval(recording_recordTimer);
	recording_recordTimer = setInterval(function(){recording_checkStop();}, 100);
	
}

function recording_stopRecording()
{
	setBlur(true, "");
	recording_setInputEnabled("recordButton", false);
	recording_setInputEnabled("backButton", false);
	recording_setInputEnabled("submitButton", false);
	recording_recordTimer = clearInterval(recording_recordTimer);
	$("#recordButton")[0].style.backgroundImage = "url(images/rec1_small.gif)";
	recording_isRecording = false;
	$("#flashContentObject")[0].stopRecording();
}

function recording_recordingStopped(success)
{
	setBlur(false, "");
	recording_setInputEnabled("recordButton", true);
	recording_setInputEnabled("backButton", true);
	if (success)
	{
		recording_setInputEnabled("submitButton", true);
		alert("Recording uploaded/stopped");
	}
	else
	{
		alert("Recording failed!");
	}
}

function recording_recordingUploadProgress(value)
{
//	$("#uploadProgress").html(value);
	setBlurText("Uploading: " + value+"%")
}

function recording_cameraReady(flag)
{
	if (flag)
		recording_setInputEnabled("recordButton", true);
	else
		recording_setInputEnabled("recordButton", false);
}

function microphoneReady(flag)
{
	if (flag)
		recording_setInputEnabled("recordButton", true);
	else
		recording_setInputEnabled("recordButton", false);
}


function recording_goToPreviewing()
{
	recording_setInputEnabled("submitButton", false);
	recording_setInputEnabled("recordButton", false);
	recording_setInputEnabled("backButton", false);
	var blurText = "Converting video";
	setBlur(true,blurText);
	recording_transcodeTimer = setInterval(function(){
		if (blurText.length>20)
		{
			blurText = blurText.substring(0, 16);
		}
		else
		{
			blurText+=".";	
		}
		
		setBlurText(blurText);
		}, 500);
	$("#flashContentObject")[0].startTranscoding();
}

function recording_recordingTranscodingFinished(fileName)
{
	clearInterval(recording_transcodeTimer);
	setBlur(false, "");
	if (fileName==null)
	{
		alert("Converting video failed! Please record again.");
		recording_setInputEnabled("submitButton", false);
		recording_setInputEnabled("recordButton", true);
		recording_setInputEnabled("backButton", true);
	}
	else
	{
		alert("Transcoding finished successfully: "+fileName);
		refreshPage("preview.php", 'vidfile='+fileName+'&type=record&keepvideofile=false')
	}
}


function recording_repaint()
{
	var curTime = recording_getCurrentTime();
	recording_paintThumb(curTime);
	
	recording_currentMaxSelected = recording_getXForTime(curTime);
	recording_currentMaxTimeSelected = curTime;
	var timeBoxCurrentTime = recording_getCurrentTime() / 1000;
	timeBoxCurrentTime = timeBoxCurrentTime <=0 ? 0: timeBoxCurrentTime;
	recording_updateTimeBox(timeBoxCurrentTime, recording_maxRecordingTime / 1000);
	
	recording_setHighlightedRegion(recording_currentMinSelected, recording_currentMaxSelected);
}

function recording_getCurrentTime()
{
	return new Date().valueOf() - recording_currentTime;
}

function recording_setHighlightedRegion(startX, endX)
{
	//alert (currentMinSelected +" "+startX);
	//if (currentMinSelected==startX && currentMaxSelected==endX)
	//	return; 
	var context = $("#selectedRegion")[0].getContext("2d");
	context.clearRect(0, 0, $('#densitybar').width(), $('#densitybar').height());
	context.fillStyle = "#0x666666";
	
	context.fillRect(startX, recording_trackPadding, endX-startX, $('#densitybar').height()-2*recording_trackPadding);
}

function recording_setupVideo()
{
	recording_setInputEnabled("recordButton", false);
	recording_setInputEnabled("submitButton", false);
	recording_paintThumb(0);
	recording_minTimeCoordinate = recording_getXForTime(recording_minTime);
	recording_currentMinSelected = recording_minSelected;
	recording_currentMinTimeSelected = recording_getTimeForX(recording_currentMinSelected);
	recording_currentMaxSelected = recording_maxSelected;
	recording_currentMaxTimeSelected = recording_getTimeForX(recording_currentMaxSelected);
	recording_setHighlightedRegion(recording_currentMinSelected, recording_currentMaxSelected);
	
	recording_currentTime = new Date().valueOf();
	recording_repaint();
}


function recording_goBack(page)
{
 //Check if person came from recording video, or from uploading video
		refreshPage(page,"", "right");
		
}

function recording_setInputEnabled(inputName, enabled)
{
	if (enabled)
	{
		$("#"+inputName).attr("disabled",false);
		$("#"+inputName).css('opacity',1);
	}
	else
	{
		$("#"+inputName).attr("disabled",true);
		$("#"+inputName).css('opacity',0.5);
	}
	
}

function recording_setControlsEnabled(flag)
{
	if (flag)
	{
		$('#videoContainer :input').prop('disabled', false);
	}
	else
	{
		$('#videoContainer :input').prop('disabled', true);
	}
}

recording_drawTrack();
recording_setupVideo();