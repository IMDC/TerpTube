var trackPadding = 12;
var trackWidth = $('#densitybar').width() - 2*trackPadding;
//document.write(trackWidth);
var trackHeight = $('#densitybar').height()-2*trackPadding;
var currentTime = 0;
var currentMinTimeSelected = 0;
var currentMaxTimeSelected = 0;
var durationSelected = 0;
var minSelected = trackPadding;
var maxSelected = trackPadding+trackWidth;
var currentMinSelected = minSelected;
var currentMaxSelected = maxSelected;
var video;
var triangleWidth = trackPadding;
var minTimeCoordinate = 0;
var minTime = 3; //seconds
var preview = false;
var stepSize = 0.1;
var maxSpeed = 2.0;
var timer;
$("#densitybar")[0].width=$("#densitybar").width();
$("#densitybar")[0].height=$("#densitybar").height();
$("#thumb")[0].width=$("#thumb").width();
$("#thumb")[0].height=$("#thumb").height();
$("#selectedRegion")[0].width=$("#selectedRegion").width();
$("#selectedRegion")[0].height=$("#selectedRegion").height();

function checkFeatures(format)
{
	if (format == "mp4")
		format = "h264";
	else if (format == "ogv")
		format = "ogg";
	if (!Modernizr.canvas || !Modernizr.video || !Modernizr.video[format])
		return false;
	return true;
}

function drawTrack()
{
	var context = $("#densitybar")[0].getContext("2d");
	context.lineJoin = "round";
	context.fillStyle = "#cccccc";
	context.strokeStyle = "#000000"; 
	context.strokeRect(0,0, $('#densitybar').width(), $('#densitybar').height());
	context.fillRect(trackPadding, trackPadding, trackWidth, trackHeight);
	context.strokeRect(trackPadding, trackPadding, trackWidth, trackHeight);
	
}

function updateTimeBox(currentTime, duration)
{
	$("#timeBox").html(getTimeCodeFromSeconds(currentTime) +"/"+getTimeCodeFromSeconds(duration));
}


function paintThumb(time)
{
	var context = $("#thumb")[0].getContext("2d");
	var position = getXForTime(time);
	if (time==0)
		position = trackPadding;
	context.clearRect(0, 0, $('#densitybar').width(), $('#densitybar').height());
	context.fillStyle = "#000000";
	context.strokeStyle = "#000000";
	context.beginPath();
	context.moveTo(position - trackPadding , 0);
	context.lineTo(position + trackPadding , 0);
	context.lineTo(position, trackPadding);
	context.closePath();
	context.fill();
	context.lineWidth = 2;
	context.beginPath();
	context.moveTo(position, 0);
	context.lineTo(position, $('#densitybar').height()-trackPadding);
	context.closePath();
	context.stroke();
}

function getXForTime(time)
{
	var x = trackPadding + video.currentTime/video.duration * trackWidth;
	return x;
}

function drawLeftTriangle(position, context)
{
	context.fillStyle = "#FF0000";
	context.beginPath();
	context.moveTo(position - triangleWidth, 2*trackPadding+trackHeight);
	context.lineTo(position, 2*trackPadding+trackHeight);
	context.lineTo(position, trackPadding+trackHeight);
	context.closePath();
	context.fill();
}

function drawRightTriangle(position, context)
{
	context.fillStyle = "#FF0000";
	context.beginPath();
	context.moveTo(position + triangleWidth, 2*trackPadding+trackHeight);
	context.lineTo(position, 2*trackPadding+trackHeight);
	context.lineTo(position, trackPadding+trackHeight);
	context.closePath();
	context.fill();
}
function getRelativeMouseCoordinates(event){

	var x;
	var y;
 	if (event.offsetX !== undefined && event.offsetY !== undefined) 
	{ 
		x = event.offsetX;
		y = event.offsetY; 
	}
	else if (event.layerX !== undefined && event.layerY !== undefined) 
	{ 
		x = event.layerX;
		y = event.layerY; 
	}
	
	return {x:x, y:y};
}

function jumpTo(jumpPoint)
{
	if (jumpPoint==0)
		setVideoTime(currentMinTimeSelected);
	else if (jumpPoint==1)
		setVideoTime(currentMaxTimeSelected);
		
}

function checkStop()
{
	// if (video.paused)
		// return;
	if (video.currentTime >= currentMaxTimeSelected)
	{
		pause();
		video.currentTime = currentMaxTimeSelected;
	}
	repaint();
}


function play()
{
	if (video.paused)
		video.play();
	//preview = false;
//	timer = setInterval("checkStop()", 100);
}

function pause()
{
	if (!video.paused)
		video.pause();
//	clearInterval(timer);
	preview = false;
}

function playPause()
{
	//Change icon on button
	if (video.paused)
		play();
	else
		pause();	
}

function setPlayButtonIconSelected(isPlayIcon)
{
	var playButton = $("#playButton")[0];
	
	if (isPlayIcon)
	{
		//set the icon to the play icon	
		playButton.style.backgroundImage = "url(images/recordOrPreview/play_small.png)";
	}
	else
	{
		//set the icon to the pause icon	
		playButton.style.backgroundImage = "url(images/recordOrPreview/pause_small.png)";
	}
}

function repaint()
{
		currentTime = video.currentTime;
		paintThumb(currentTime);
		var timeBoxCurrentTime = currentTime-currentMinTimeSelected;
		timeBoxCurrentTime = timeBoxCurrentTime <=0 ? 0: timeBoxCurrentTime;
		updateTimeBox(timeBoxCurrentTime, currentMaxTimeSelected-currentMinTimeSelected);
		if (preview && currentTime >= currentMaxTimeSelected)
		{
			preview = false;
			video.pause();
			setVideoTime(currentMaxTimeSelected);
		}
	//	setHighlightedRegion(currentMinSelected, currentMaxSelected);
}

function setMouseUpThumb(event)
{
	var thumbCanvas = $("#thumb")[0];
	if (thumbCanvas.onmousemove)
	{
		thumbCanvas.onmousemove = null;
	}
	preview = false;
}

function stepForward()
{
	if (video.currentTime + stepSize > currentMaxTimeSelected)
	{
		video.currentTime = currentMaxTimeSelected;
	}
	else
		video.currentTime+= stepSize;
	repaint();
}

function stepBackward()
{	
	if (video.currentTime -stepSize < currentMinTimeSelected)
	{
		video.currentTime = currentMinTimeSelected;
	}
	else
		video.currentTime-= stepSize;
	repaint();
}

function setMouseOutThumb(event)
{
	var thumbCanvas = $("#thumb")[0];
	if (thumbCanvas.onmousemove)
	{
		thumbCanvas.onmousemove = null;
	}
}

function setMouseDownThumb(event)
{
	var thumbCanvas = $("#thumb")[0];
	var selectedRegionCanvas = $("#selectedRegion")[0];
	var coords = getRelativeMouseCoordinates(event);
	preview = false;

	if (coords.y < trackPadding + trackHeight)
	{	//Restrict the playhead to only within the selected region
		thumbCanvas.onmousemove = function(event){ 
			var coords = getRelativeMouseCoordinates(event);
		//	if (coords.y < trackPadding + trackHeight)
		//	{
				if (coords.x >= currentMinSelected && coords.x<=currentMaxSelected)
					setVideoTimeFromCoordinate(coords.x);
				else if (coords.x <currentMinSelected)
					setVideoTime(currentMinTimeSelected);
				else
					setVideoTime(currentMaxTimeSelected);
		//	}
		};
		if (coords.x >= currentMinSelected && coords.x<=currentMaxSelected)
			setVideoTimeFromCoordinate(coords.x);
	}
	else
	{
		if (coords.x <=currentMinSelected && coords.x >= currentMinSelected - triangleWidth)
		{
			//Left triangle selected			
			var offset = currentMinSelected - coords.x;
			thumbCanvas.onmousemove = function(event){
				var coords = getRelativeMouseCoordinates(event);
				currentMinSelected = coords.x + offset;
				if (currentMinSelected < minSelected )
					currentMinSelected = minSelected;
				if (currentMinSelected > currentMaxSelected - minTimeCoordinate)
					currentMinSelected = currentMaxSelected - minTimeCoordinate;
				currentMinTimeSelected = getTimeForX(currentMinSelected);
				setHighlightedRegion(currentMinSelected, currentMaxSelected);
				setVideoTime(currentMinTimeSelected);
			};

		}
		else if (coords.x >=currentMaxSelected && coords.x <=currentMaxSelected + triangleWidth)
		{
			//Right triangle selected	;
			var offset = coords.x - currentMaxSelected;
			thumbCanvas.onmousemove = function(event){
				var coords = getRelativeMouseCoordinates(event);
				currentMaxSelected = coords.x - offset;
				if (currentMaxSelected > maxSelected)
					currentMaxSelected = maxSelected;
				if (currentMaxSelected < currentMinSelected + minTimeCoordinate)
					currentMaxSelected = currentMinSelected + minTimeCoordinate;
				currentMaxTimeSelected = getTimeForX(currentMaxSelected);
				setHighlightedRegion(currentMinSelected, currentMaxSelected);
				setVideoTime(currentMaxTimeSelected);
			};
		}
	}
}

function setHighlightedRegion(startX, endX)
{
	//alert (currentMinSelected +" "+startX);
	//if (currentMinSelected==startX && currentMaxSelected==endX)
	//	return; 
	var context = $("#selectedRegion")[0].getContext("2d");
	context.clearRect(0, 0, $('#densitybar').width(), $('#densitybar').height());
	context.fillStyle = "#00ff00";
	
	context.fillRect(startX, trackPadding, endX-startX, $('#densitybar').height()-2*trackPadding);
	drawLeftTriangle(startX, context);
	drawRightTriangle(endX, context);
}

function setupVideo()
{
	video =  $("#video")[0];
	video.addEventListener('timeupdate', function(){checkStop()}, false);
	video.addEventListener('play', function(){setPlayButtonIconSelected(false)}, false);
	video.addEventListener('pause', function(){setPlayButtonIconSelected(true)}, false);
	paintThumb(0);
	minTimeCoordinate = getXForTime(minTime);
	currentMinSelected = minSelected;
	currentMinTimeSelected = getTimeForX(currentMinSelected);
	currentMaxSelected = maxSelected;
	currentMaxTimeSelected = getTimeForX(currentMaxSelected);
	setHighlightedRegion(currentMinSelected, currentMaxSelected);

	repaint();
	$('#track').mouseleave(function(){ setVolumeBarVisible(false)});
	$('#thumb').bind('mousedown',function(e){setMouseDownThumb(e)});
	$('#thumb').bind('mouseout', function(e){setMouseOutThumb(e)});
	$('#thumb').bind('mouseup', function(e){setMouseUpThumb(e)});

}

function getTimeForX(x)
{
	var time = (x - trackPadding)*video.duration/trackWidth;
	return time;
}

function setVideoTimeFromCoordinate(position)
{
	var time = getTimeForX(position);
	if (time != video.currentTime)
		video.currentTime = time;	
	repaint();
}

function setVideoTime(time)
{
	if (time != video.currentTime)
		video.currentTime = time;	
	repaint();
}

function previewClip()
{
	setVideoTimeFromCoordinate(currentMinSelected);
	if (!video.playing)
		video.play();
	preview = true;
	repaint();
}

function goBack(where)
{
 //Check if person came from recording video, or from uploading video
	if (where=="record")
		refreshPage('playerContent', "record.php","", "right");
	else if (where == "upload")
	{
		refreshPage('playerContent', "upload.php","", "right");
	}
		
}
function transcodeAjax(inputVideoFile, keepVideoFile)
{
	setControlsEnabled(false);
	if (currentMinSelected == minSelected && currentMaxSelected == maxSelected)
	{
		//No need to trim as the user has not moved the start/end points
	}
	setBlurText("Trimming Video...");
	setBlur(true);
	$.ajax({
		url: "recordOrPreview/transcoder.php", 
		type: "POST",
		data: { 
			trim:"yes", 
			inputVidFile: inputVideoFile, 
			startTime: currentMinTimeSelected, 
			endTime: currentMaxTimeSelected, 
			keepInputFile: inputVideoFile},
		success: function (data){transcodeSuccess(data);},
		error: function (data) {transcodeError(data);}
	});	
}

function setControlsEnabled(flag)
{
	if (flag)
	{
		$('#videoContainer :input').prop('disabled', false);
		$('#thumb').bind('mousedown',function(e){setMouseDownThumb(e)});	
	}
	else
	{
		$('#videoContainer :input').prop('disabled', true);
		$('#thumb').unbind('mousedown');
	}
}
function transcodeSuccess(data)
{	
	setBlurText("");
	setBlur(false);
	setControlsEnabled(true);
	alert("VideoFile created: "+data);
	window.location.href = "recordOrPreview/streams.php";
}

function transcodeError(data)
{
	setBlurText("");
	setBlur(false);
	setControlsEnabled(true);
	alert("Transcode failed: "+data);
}

$(function() {
	$("#volumeSlider").slider({
		orientation: "horizontal",
		range: "min",
		min: 0,
		max: 100,
		value: 100,
		slide: function (event, ui) {
			video.volume=ui.value/100;
			}
		});
});

function setVolumeBarVisible(flag)
{
	if (flag)
//		$("#volumeSlider").css("display", "block");
		$('#volumeSlider').show('slide', {direction: "right"}, 200);
	else
//		$("#volumeSlider").css("display", "none");
		$('#volumeSlider').hide('slide', {direction: "right"}, 200);
}

function toggleMute()
{
	if ($("#volumeImage").attr("src").match("images/audioOn.png"))
	{
		$("#volumeImage").attr("src", "images/audioOff.png");
		video.muted = true;
		$("#volumeSlider").slider('value', 0);
	}
	else
	{
		$("#volumeImage").attr("src", "images/audioOn.png");
		video.muted = false;
		$("#volumeSlider").slider('value', video.volume*100);
	}
}
	drawTrack();