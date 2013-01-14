var DENSITY_BAR_TYPE_RECORDER = "recorder";
var DENSITY_BAR_TYPE_PLAYER = "player";

function createControls()
{
	/*
	 *  options type can be player, record, preview
	 */
	var instance = this;
	
	if (this.options.type == DENSITY_BAR_TYPE_PLAYER)
	{
		this.setupVideo = setupVideoPlayback;
	}
	else if (this.options.type == DENSITY_BAR_TYPE_RECORDER)
	{
		this.setupVideo = setupVideoRecording;
	}
	
	$(this.elementID).addClass("videoControlsContainer");
	$(this.elementID).append('<div class="videoControlsContainer track"></div>');
	$(this.elementID+" .videoControlsContainer.track").append('<canvas class="videoControlsContainer track densitybar"></canvas>').append('<canvas class="videoControlsContainer track selectedRegion"></canvas>').append('<canvas class="videoControlsContainer track thumb"></canvas>');
	$(this.elementID).find(".videoControlsContainer.track").eq(0).append('<div class="videoControlsContainer track timeBox">0:00:00/0:00:00</div>');
	if (this.options.volumeControl)
	{
		$(".videoControlsContainer.track.thumb").mouseover(function (){instance.setVolumeBarVisible(false);});
		$(this.elementID).find(".videoControlsContainer.track").eq(0).append('<div class="videoControlsContainer track volumeControl"></div>');
		$(".videoControlsContainer.track.volumeControl").mouseover(function (){instance.setVolumeBarVisible(true);});
		$(".videoControlsContainer.track.volumeControl").append('<img alt="volume control" src="images/recordOrPreview/audioOn.png" />').append('<div class="videoControlsContainer track volumeControl volumeSlider"></div>');
		$(".videoControlsContainer.track.volumeControl image").click(function(){instance.toggleMute();});
		
		$(function() {
			$(instance.elementID).find(".videoControlsContainer.track.volumeControl.volumeSlider").eq(0).slider({
				orientation: "horizontal",
				range: "min",
				min: 0,
				max: 100,
				value: 100,
				slide: function (event, ui) {
					$(instance.videoID)[0].volume=ui.value/100;
					}
				});
		});
	}
	$(this.elementID).append('<div class="videoControlsContainer controlsBar"></div>');
	$(".videoControlsContainer.controlsBar").append('<div class="videoControlsContainer controlsBar backButtons"></div>').append('<div class="videoControlsContainer controlsBar forwardButtons"></div>').append('<div class="videoControlsContainer controlsBar videoControls"></div>');
	if (this.options.type==DENSITY_BAR_TYPE_PLAYER)
	{
		$(".videoControlsContainer.controlsBar.backButtons").append('<button type="button" class="videoControlsContainer controlsBar backButtons backButton"></button>');
	}
	else
	{
		$(".videoControlsContainer.controlsBar.backButtons").append('<button type="button" class="videoControlsContainer controlsBar backButtons backButton record"></button>');
	}
	$(".videoControlsContainer.controlsBar.backButtons.backButton").click(instance.options.backFunction);
	if (this.options.type==DENSITY_BAR_TYPE_PLAYER)
	{
		$(".videoControlsContainer.controlsBar.videoControls").append('<button type="button" class="videoControlsContainer controlsBar videoControls beginButton"></button>').append('<button type="button" class="videoControlsContainer controlsBar videoControls stepBackwardButton"></button>').append('<button type="button" class="videoControlsContainer controlsBar videoControls playButton"></button>').append('<button type="button" class="videoControlsContainer controlsBar videoControls stepForwardButton"></button>').append('<button type="button" class="videoControlsContainer controlsBar videoControls endButton"></button>');
		$(".videoControlsContainer.controlsBar.videoControls.beginButton").click(function(){instance.jumpTo(0);});
		$(".videoControlsContainer.controlsBar.videoControls.stepBackwardButton").click(function(){instance.stepBackward();});
		$(".videoControlsContainer.controlsBar.videoControls.playButton").click(function(){instance.playPause();});
		$(".videoControlsContainer.controlsBar.videoControls.stepForwardButton").click(function(){instance.stepForward();});
		$(".videoControlsContainer.controlsBar.videoControls.endButton").click(function(){instance.jumpTo(1);});
	}
	else
	{
		$(".videoControlsContainer.controlsBar.videoControls").append('<button type="button" class="videoControlsContainer controlsBar videoControls recordButton"></button>');
		$(".videoControlsContainer.controlsBar.videoControls.recordButton").click(function(){instance.recording_toggleRecording();});
	}
	if (this.options.type==DENSITY_BAR_TYPE_PLAYER)
	{
		$(".videoControlsContainer.controlsBar.forwardButtons").append('<button type="button" class="videoControlsContainer controlsBar forwardButtons forwardButton"></button>');
	}
	else
	{
		$(".videoControlsContainer.controlsBar.forwardButtons").append('<button type="button" class="videoControlsContainer controlsBar forwardButtons forwardButton record"></button>');
	}
	$(".videoControlsContainer.controlsBar.forwardButtons.forwardButton").click(instance.options.forwardFunction);
	if (this.options.audioBar)
	{
		$(".videoControlsContainer").find(".videoControlsContainer.controlsBar").eq(0).append('<div class="videoControlsContainer controlsBar audioButtonsBar"></div>');
		$(".videoControlsContainer.controlsBar.audioButtonsBar").append('Remove audio from the video?<br />'+
			'<label for="audioOff"><img src="images/recordOrPreview/audioOff.png" width="30px" height="30px" alt="audio enabled" /> </label>'+
			'<input type="radio" name="audioEnabled" value="false" id="audioOff" />'+
			'<label for="audioOn"><img src="images/recordOrPreview/audioOn.png" width="30px" height="30px" alt="audio enabled" /> </label>'+
			'<input type="radio" name="audioEnabled" value="true" class="record-or-preview" id="audioOn" checked="checked" />');
	}
	if (this.options.type==DENSITY_BAR_TYPE_PLAYER)
	{
		$(this.videoID).on('loadedmetadata',function(){instance.setupVideo();});
	}
	this.trackPadding = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0).width() / 40;
	this.trackWidth = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0).width() - 2*this.trackPadding;
	this.trackHeight = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0).height() - 2*this.trackPadding;
	
	
	
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var thumbElement = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0);
	var selectedRegionElement = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0);
	
	densityBarElement[0].width=densityBarElement.width();
	densityBarElement[0].height=densityBarElement.height();
	thumbElement[0].width=thumbElement.width();
	thumbElement[0].height=thumbElement.height();
	selectedRegionElement[0].width=selectedRegionElement.width();
	selectedRegionElement[0].height=selectedRegionElement.height();
	
	
	this.currentMinTimeSelected = 0;
	this.currentMaxTimeSelected = 0;
	this.durationSelected = 0;
	this.minSelected = this.trackPadding;
	this.maxSelected = this.trackPadding+this.trackWidth;
	this.currentMinSelected = this.minSelected;
	this.currentMaxSelected = this.maxSelected;
	this.video;
	this.triangleWidth = this.trackPadding;
	this.minTimeCoordinate = 0;
	this.minTime = 3; //seconds
	this.preview = false;
	this.stepSize = 0.1;
	this.maxSpeed = 2.0;
	this.timer;
	
	//var recording_minTime = 3 * 1000; //seconds
	this.recording_recordTimer;
	this.recording_transcodeTimer;
	
//	var recording_maxRecordingTime = 60*1000; //60 seconds
//	var recording_minRecordingTime = 1000*3;
	
	this.isRecording = false;
	this.isPastMinimumRecording = false;
	
	this.drawTrack();
	if (this.options.type==DENSITY_BAR_TYPE_RECORDER)
	{
		this.setupVideo();
	}
}

/*
 * Comments is an object array that contains - 
 * startTime:number, endTime:number, commentID:number, [
 */
function setComments(commentsArray)
{
	this.comments = commentsArray;
}

function drawComments()
{
	this.clearComments();
	
	for (var i=0;i<this.comments; i++)
	{
		this.drawComment(this.comments[i]);
	}
}

function clearComments()
{
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0)[0].getContext("2d");
	context.clearRect(0, 0, densityBarElement.width(), densityBarElement.height());
}

function drawComment(comment)
{
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0)[0].getContext("2d");
	
		context.fillStyle = comment.color;
		
		var startX = this.getXForTime(comment.startTime);
		var endX = this.getXForTIme(comment.endTime);
		context.fillRect(startX, this.trackPadding, endX-startX, densityBarElement.height()-2*this.trackPadding);
}

function densityBar(elementID, videoID, options)
{
	this.elementID = "#"+elementID;
	this.videoID = "#"+videoID;
	this.comments = new Array();
	//type can be player, recorder
	this.options = {
			volumeControl:true,
			type:DENSITY_BAR_TYPE_PLAYER,
			backButton: true,
			backFunction:function(){alert("Back");},
			forwardButton: true,
			forwardFunction:function(){alert("Forward");},
			audioBar:true,
			densityBarHeight: 40,
			areaSelectionEnabled: false,
			minRecordingTime : 3,
			maxRecordingTime : 60
			};
	if (typeof options!='undefined')
	{
		for (key in options)
		{
			this.options[key] = options[key];
		}
	}
	
	this.createControls = createControls;
	this.drawTrack = drawTrack;
	this.updateTimeBox = updateTimeBox;
	this.paintThumb =  paintThumb;
	this.getXForTime = getXForTime;
	this.drawLeftTriangle = drawLeftTriangle;
	this.drawRightTriangle = drawRightTriangle;
	this.jumpTo = jumpTo;
	this.checkStop = checkStop;
	this.play = play;
	this.pause = pause;
	this.playPause = playPause;
	this.setPlayButtonIconSelected = setPlayButtonIconSelected;
	this.repaint = repaint;
	this.stepForward = stepForward;
	this.stepBackward = stepBackward;
	this.setMouseOutThumb = setMouseOutThumb;
	this.setMouseDownThumb = setMouseDownThumb;
	this.setHighlightedRegion = setHighlightedRegion;
	this.getTimeForX = getTimeForX;
	this.setVideoTimeFromCoordinate = setVideoTimeFromCoordinate;
	this.setVideoTime = setVideoTime;
	this.setControlsEnabled = setControlsEnabled;
	this.setVolumeBarVisible = setVolumeBarVisible;
	this.toggleMute = toggleMute;
	this.recording_toggleRecording = recording_toggleRecording;
	this.recording_startRecording = recording_startRecording;
	this.recording_checkStop= recording_checkStop;
	this.recording_goToPreviewing = recording_goToPreviewing;
	this.recording_recordingStarted = recording_recordingStarted;
	this.recording_recordingStopped = recording_recordingStopped;
	this.recording_stopRecording = recording_stopRecording;
	this.recording_recordingTranscodingFinished = recording_recordingTranscodingFinished;
	this.recording_recordingUploadProgress = recording_recordingUploadProgress;
	this.recording_cameraReady = recording_cameraReady;
	this.recording_microphoneReady = recording_microphoneReady;
	this.setInputEnabled = setInputEnabled;
	this.getDuration = getDuration;
	this.getCurrentTime = getCurrentTime;
}



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
	
	var densityBarElement =$(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = densityBarElement[0].getContext("2d");
	context.clearRect(0, 0, densityBarElement.width(), densityBarElement.height());
	context.lineJoin = "round";
	context.fillStyle = "#cccccc";
	context.strokeStyle = "#000000"; 
	context.strokeRect(0,0, densityBarElement.width(), densityBarElement.height());
	context.fillRect(this.trackPadding, this.trackPadding, this.trackWidth, this.trackHeight);
	context.strokeRect(this.trackPadding, this.trackPadding, this.trackWidth, this.trackHeight);
}


function updateTimeBox(currentTime, duration)
{
	$(this.elementID).find(".videoControlsContainer.timeBox").eq(0).html(getTimeCodeFromSeconds(currentTime) +"/"+getTimeCodeFromSeconds(duration));
}

function paintThumb(time)
{
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0)[0].getContext("2d");
	var position = this.getXForTime(time);
	if (time==0)
		position = this.trackPadding;
	context.clearRect(0, 0, densityBarElement.width(), densityBarElement.height());
	context.fillStyle = "#000000";
	context.strokeStyle = "#000000";
	context.beginPath();
	context.moveTo(position - this.trackPadding , 0);
	context.lineTo(position + this.trackPadding , 0);
	context.lineTo(position, this.trackPadding);
	context.closePath();
	context.fill();
	context.lineWidth = 2;
	context.beginPath();
	context.moveTo(position, 0);
	context.lineTo(position, densityBarElement.height()-this.trackPadding);
	context.closePath();
	context.stroke();
}

function drawLeftTriangle(position, context)
{
	context.fillStyle = "#FF0000";
	context.beginPath();
	context.moveTo(position - this.triangleWidth, 2*this.trackPadding+this.trackHeight);
	context.lineTo(position, 2*this.trackPadding+this.trackHeight);
	context.lineTo(position, this.trackPadding+this.trackHeight);
	context.closePath();
	context.fill();
}

function drawRightTriangle(position, context)
{
	context.fillStyle = "#FF0000";
	context.beginPath();
	context.moveTo(position + this.triangleWidth, 2*this.trackPadding+this.trackHeight);
	context.lineTo(position, 2*this.trackPadding+this.trackHeight);
	context.lineTo(position, this.trackPadding+this.trackHeight);
	context.closePath();
	context.fill();
}

function setAreaSelectionEnabled(flag)
{
	this.options.areaSelectionEnabled = flag;
	this.setHighlightedRegion(this.currentMinSelected, this.currentMaxSelected);
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
		this.setVideoTime(this.currentMinTimeSelected);
	else if (jumpPoint==1)
		this.setVideoTime(this.currentMaxTimeSelected);
		
}

function checkStop()
{
	// if (video.paused)
		// return;
	if ($(this.videoID)[0].currentTime >= this.currentMaxTimeSelected)
	{
		this.pause();
		$(this.videoID)[0].currentTime = this.currentMaxTimeSelected;
	}
	this.repaint();
}


function play()
{
	if ($(this.videoID)[0].paused)
		$(this.videoID)[0].play();
	//preview = false;
//	timer = setInterval("checkStop()", 100);
}

function pause()
{
	if (!$(this.videoID)[0].paused)
		$(this.videoID)[0].pause();
//	clearInterval(timer);
	this.preview = false;
}

function playPause()
{
	//Change icon on button
	if ($(this.videoID)[0].paused)
		this.play();
	else
		this.pause();	
}

function setPlayButtonIconSelected(isPlayIcon)
{
	var playButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.playButton").eq(0)[0];
	
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
		this.paintThumb(this.getCurrentTime());
		if (this.options.type==DENSITY_BAR_TYPE_PLAYER)
		{
			var timeBoxCurrentTime = this.getCurrentTime()-this.currentMinTimeSelected;
			timeBoxCurrentTime = timeBoxCurrentTime <=0 ? 0: timeBoxCurrentTime;
			this.updateTimeBox(timeBoxCurrentTime, this.currentMaxTimeSelected-this.currentMinTimeSelected);
			if (this.preview && this.getCurrentTime() >= this.currentMaxTimeSelected)
			{
				this.preview = false;
				this.pause();
				this.setVideoTime(this.currentMaxTimeSelected);
			}
		}
		if (this.options.type==DENSITY_BAR_TYPE_RECORDER)
		{
			this.currentMaxSelected = this.getXForTime(this.getCurrentTime());
			this.currentMaxTimeSelected = this.getCurrentTime();
			var timeBoxCurrentTime = this.getCurrentTime();
			this.updateTimeBox(timeBoxCurrentTime, this.getDuration());
			this.setHighlightedRegion(this.currentMinSelected, this.currentMaxSelected);
		}
	//	setHighlightedRegion(currentMinSelected, currentMaxSelected);
}

function stepForward()
{
	if ($(this.videoID)[0].currentTime + this.stepSize > this.currentMaxTimeSelected)
	{
		$(this.videoID)[0].currentTime = this.currentMaxTimeSelected;
	}
	else
		$(this.videoID)[0].currentTime+= this.stepSize;
	this.repaint();
}

function stepBackward()
{	
	if ($(this.videoID)[0].currentTime -this.stepSize < this.currentMinTimeSelected)
	{
		$(this.videoID)[0].currentTime = this.currentMinTimeSelected;
	}
	else
		$(this.videoID)[0].currentTime-= this.stepSize;
	this.repaint();
}

function setMouseOutThumb(event)
{
	$(this.elementID).find(".videoControlsContainer.track.thumb").eq(0).off('mousemove');
}

function setMouseDownThumb(event)
{
	var instance = this;
	var thumbCanvas = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0);
	var selectedRegionCanvas = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0)[0];
	var coords = getRelativeMouseCoordinates(event);
	this.preview = false;

	if (coords.y < instance.trackPadding + instance.trackHeight)
	{	//Restrict the playhead to only within the selected region
		thumbCanvas.on('mousemove', function(event){ 
			var coords = getRelativeMouseCoordinates(event);
		//	if (coords.y < trackPadding + trackHeight)
		//	{
				if (coords.x >= instance.currentMinSelected && coords.x<=instance.currentMaxSelected)
					instance.setVideoTimeFromCoordinate(coords.x);
				else if (coords.x <instance.currentMinSelected)
					instance.setVideoTime(instance.currentMinTimeSelected);
				else
					instance.setVideoTime(instance.currentMaxTimeSelected);
		//	}
		});
		if (coords.x >= instance.currentMinSelected && coords.x<=instance.currentMaxSelected)
			instance.setVideoTimeFromCoordinate(coords.x);
	}
	else
	{
		if (!instance.options.areaSelectionEnabled)
		{
			return;
		}
		if (coords.x <=instance.currentMinSelected && coords.x >= instance.currentMinSelected - instance.triangleWidth)
		{
			//Left triangle selected			
			var offset = instance.currentMinSelected - coords.x;
			thumbCanvas.on('mousemove', function(event){
				var coords = getRelativeMouseCoordinates(event);
				instance.currentMinSelected = coords.x + offset;
				if (instance.currentMinSelected < instance.minSelected )
					instance.currentMinSelected = instance.minSelected;
				if (instance.currentMinSelected > instance.currentMaxSelected - instance.minTimeCoordinate)
					instance.currentMinSelected = instance.currentMaxSelected - instance.minTimeCoordinate;
				instance.currentMinTimeSelected = instance.getTimeForX(instance.currentMinSelected);
				instance.setHighlightedRegion(instance.currentMinSelected, instance.currentMaxSelected);
				instance.setVideoTime(instance.currentMinTimeSelected);
			});

		}
		else if (coords.x >=instance.currentMaxSelected && coords.x <=instance.currentMaxSelected + instance.triangleWidth)
		{
			//Right triangle selected	;
			var offset = coords.x - instance.currentMaxSelected;
			thumbCanvas.on('mousemove', function(event){
				var coords = getRelativeMouseCoordinates(event);
				instance.currentMaxSelected = coords.x - offset;
				if (instance.currentMaxSelected > instance.maxSelected)
					instance.currentMaxSelected = instance.maxSelected;
				if (instance.currentMaxSelected < instance.currentMinSelected + instance.minTimeCoordinate)
					instance.currentMaxSelected = instance.currentMinSelected + instance.minTimeCoordinate;
				instance.currentMaxTimeSelected = instance.getTimeForX(instance.currentMaxSelected);
				instance.setHighlightedRegion(instance.currentMinSelected, instance.currentMaxSelected);
				instance.setVideoTime(instance.currentMaxTimeSelected);
			});
		}
	}
}

function setHighlightedRegion(startX, endX)
{
	//alert (currentMinSelected +" "+startX);
	//if (currentMinSelected==startX && currentMaxSelected==endX)
	//	return; 
	var densityBarElement = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0);
	var context = $(this.elementID).find(".videoControlsContainer.track.selectedRegion").eq(0)[0].getContext("2d");
	context.clearRect(0, 0, densityBarElement.width(), densityBarElement.height());
	
	if (this.options.areaSelectionEnabled)
	{
		context.fillStyle = "#00ff00";
		
		
		this.drawLeftTriangle(startX, context);
		this.drawRightTriangle(endX, context);
	}
	else
	{
		context.fillStyle = "#0x666666";
	}
	context.fillRect(startX, this.trackPadding, endX-startX, densityBarElement.height()-2*this.trackPadding);
}

function setupVideoPlayback()
{
	var instance = this;
	$(this.videoID)[0].addEventListener('timeupdate', function(){instance.checkStop();}, false);
	$(this.videoID)[0].addEventListener('play', function(){instance.setPlayButtonIconSelected(false);}, false);
	$(this.videoID)[0].addEventListener('pause', function(){instance.setPlayButtonIconSelected(true);}, false);
	this.paintThumb(0);
	this.minTimeCoordinate = this.getXForTime(this.minTime);
	this.currentMinSelected = this.minSelected;
	this.currentMinTimeSelected = this.getTimeForX(this.currentMinSelected);
	this.currentMaxSelected = this.maxSelected;
	this.currentMaxTimeSelected = this.getTimeForX(this.currentMaxSelected);
	this.setHighlightedRegion(this.currentMinSelected, this.currentMaxSelected);

	this.repaint();
	$(this.elementID).find(".videoControlsContainer.track").eq(0).on('mouseleave',function(){ instance.setVolumeBarVisible(false);});
	
	var densityBarThumbElement = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0);
	densityBarThumbElement.on('mousedown',function(e){instance.setMouseDownThumb(e);});
	densityBarThumbElement.on('mouseout', function(e){instance.setMouseOutThumb(e);});
	densityBarThumbElement.on('mouseup', function(e){densityBarThumbElement.off("mousemove");});

}

function setupVideoRecording()
{
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton").eq(0);
	this.setInputEnabled(recordButton, false);
	this.setInputEnabled(forwardButton, false);
	this.minTimeCoordinate = this.getXForTime(this.minTime);
	this.currentMinSelected = this.minSelected;
	this.currentMinTimeSelected = this.getTimeForX(this.currentMinSelected);
	this.currentMaxSelected = this.maxSelected;
	this.currentMaxTimeSelected = this.getTimeForX(this.currentMaxSelected);
	this.setHighlightedRegion(this.currentMinSelected, this.currentMaxSelected);
	
	this.recording_startTime = new Date().valueOf();
	this.paintThumb(0);
	this.repaint();
}

function setInputEnabled(element, enabled)
{
	if (enabled)
	{
		element.attr("disabled",false);
		element.css('opacity',1);
	}
	else
	{
		element.attr("disabled",true);
		element.css('opacity',0.5);
	}
}

function recording_checkStop()
{
	// if (video.paused)
		// return;
	this.repaint();
	var time = this.getCurrentTime();
	if (!this.isPastMinimumRecording && time >=this.options.minRecordingTime)
	{
		this.isPastMinimumRecording = true;
		var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
		this.setInputEnabled(recordButton, true);
	}
	if (time >= this.getDuration())
	{
		this.recording_stopRecording();
	}
	
}

function recording_toggleRecording()
{
	//Change icon on button
	if (this.isRecording)
		this.recording_stopRecording();
	else
		this.recording_startRecording();	
	
}

function recording_startRecording()
{
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton").eq(0);
	recordButton[0].style.backgroundImage = "url(images/recordOrPreview/rec2_small.gif)";
	this.setInputEnabled(recordButton, false);
	this.setInputEnabled(forwardButton, false);
	this.currentMinSelected = this.minSelected;
	this.currentMinTimeSelected = this.getTimeForX(this.currentMinSelected);
	this.currentMaxSelected =this.maxSelected;
	this.currentMaxTimeSelected = this.getTimeForX(this.currentMaxSelected);
	this.setHighlightedRegion(this.currentMinSelected, this.currentMaxSelected);
	this.isRecording = true;
	$(this.videoID)[0].startRecording();
}

//Called by Flash when recording actually started
function recording_recordingStarted()
{
	var instance = this;
	this.recording_startTime = new Date().valueOf();
	if (this.recordTimer)
		clearInterval(this.recordTimer);
	this.recordTimer = setInterval(function(){instance.recording_checkStop();}, 100);
	
}

function recording_stopRecording()
{
	setBlur(true, "");
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	var backButton = $(this.elementID).find(".videoControlsContainer.controlsBar.backButtons.backButton").eq(0);
	var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton").eq(0);
	
	this.setInputEnabled(recordButton, false);
	this.setInputEnabled(backButton, false);
	this.setInputEnabled(forwardButton, false);
	this.recordTimer = clearInterval(this.recordTimer);
	recordButton[0].style.backgroundImage = "url(images/recordOrPreview/rec1_small.gif)";
	this.isRecording = false;
	this.isPastMinimumRecording = false;
	$(this.videoID)[0].stopRecording();
}

function recording_recordingStopped(success)
{
	setBlur(false, "");
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	var backButton = $(this.elementID).find(".videoControlsContainer.controlsBar.backButtons.backButton").eq(0);
	var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton").eq(0);
	this.setInputEnabled(recordButton, true);
	this.setInputEnabled(backButton, true);
	if (success)
	{
		this.setInputEnabled(forwardButton, true);
	}
	else
	{
		alert("Recording failed!");
	}
}

function recording_recordingUploadProgress(value)
{
//	$("#uploadProgress").html(value);
	setBlurText("Uploading: " + value+"%");
}

function recording_cameraReady(flag)
{
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	if (flag)
		this.setInputEnabled(recordButton, true);
	else
		this.setInputEnabled(recordButton, false);
}

function recording_microphoneReady(flag)
{
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	if (flag)
		this.setInputEnabled(recordButton, true);
	else
		this.setInputEnabled(recordButton, false);
}


function recording_goToPreviewing()
{
	var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
	var backButton = $(this.elementID).find(".videoControlsContainer.controlsBar.backButtons.backButton").eq(0);
	var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton").eq(0);
	this.setInputEnabled(forwardButton, false);
	this.setInputEnabled(recordButton, false);
	this.setInputEnabled(backButton, false);
	var blurText = "Converting video";
	setBlur(true,blurText);
	this.transcodeTimer = setInterval(function(){
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
	$(this.videoID)[0].startTranscoding();
}

function recording_recordingTranscodingFinished(fileName)
{
	clearInterval(this.transcodeTimer);
	setBlur(false, "");
	if (fileName==null)
	{
		var recordButton = $(this.elementID).find(".videoControlsContainer.controlsBar.videoControls.recordButton").eq(0);
		var backButton = $(this.elementID).find(".videoControlsContainer.controlsBar.backButtons.backButton").eq(0);
		var forwardButton = $(this.elementID).find(".videoControlsContainer.controlsBar.forwardButtons.forwardButton").eq(0);
		alert("Converting video failed! Please record again.");
		this.setInputEnabled(forwardButton, false);
		this.setInputEnabled(recordButton, true);
		this.setInputEnabled(backButton, true);
	}
	else
	{
	//	alert("Transcoding finished successfully: "+fileName);
		refreshPage($(this.elementID).parent().attr("id"), "recordOrPreview/preview.php", 'vidfile='+fileName+'&type=record&keepvideofile=false');
	}
}


function getCurrentTime()
{
	if (this.options.type==DENSITY_BAR_TYPE_RECORDER)
		return (new Date().valueOf() - this.recording_startTime)/1000;
	else if (this.options.type==DENSITY_BAR_TYPE_PLAYER)
		return $(this.videoID)[0].currentTime;
}

function getDuration()
{
	if (this.options.type==DENSITY_BAR_TYPE_RECORDER)
		return this.options.maxRecordingTime;
	else if (this.options.type==DENSITY_BAR_TYPE_PLAYER)
		return $(this.videoID)[0].duration;
}

function getTimeForX(x)
{
	var time = (x - this.trackPadding)*this.getDuration()/this.trackWidth;
	return time;
}

function getXForTime(time)
{
	var x = this.trackPadding + time/this.getDuration() * this.trackWidth;
	return x;
}

function setVideoTimeFromCoordinate(position)
{
	var time = this.getTimeForX(position);
	if (time != $(this.videoID)[0].currentTime)
		$(this.videoID)[0].currentTime = time;	
	this.repaint();
}

function setVideoTime(time)
{
	if (time != $(this.videoID)[0].currentTime)
		$(this.videoID)[0].currentTime = time;	
	this.repaint();
}

function transcodeAjax(inputVideoFile, outputVideoFile, keepVideoFile)
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
			outputVidFile: outputVideoFile,
			startTime: currentMinTimeSelected, 
			endTime: currentMaxTimeSelected, 
			keepInputFile: inputVideoFile},
		success: function (data){transcodeSuccess(data);},
		error: function (data) {transcodeError(data);}
	});	
}


function setControlsEnabled(flag)
{
	var instance = this;
	if (flag)
	{
		$(this.elementID+".videoControlsContainer :input").prop('disabled', false);
		$(this.elementID).find(".videoControlsContainer.track.thumb").eq(0).on('mousedown',function(e){instance.setMouseDownThumb(e);});	
	}
	else
	{
		$(this.elementID+".videoControlsContainer :input").prop('disabled', true);
		$(this.elementID).find(".videoControlsContainer.track.thumb").eq(0).off('mousedown');
	}
}



function setVolumeBarVisible(flag)
{
	if (flag)
//		$("#volumeSlider").css("display", "block");
		$(this.elementID).find(".videoControlsContainer.volumeControl.volumeSlider").eq(0).show('slide', {direction: "right"}, 200);
	else
//		$("#volumeSlider").css("display", "none");
		$(this.elementID).find(".videoControlsContainer.volumeControl.volumeSlider").eq(0).hide('slide', {direction: "right"}, 200);
}

function toggleMute()
{
	var instance = this;
	var imageElement = $(this.elementID).find(".videoControlsContainer.volumeControl image").eq(0);
	if (imageElement.attr("src").match("images/audioOn.png"))
	{
		imageElement.attr("src", "images/audioOff.png");
		$(this.videoID)[0].muted = true;
		$(this.elementID).find(".videoControlsContainer.volumeControl.volumeSlider").eq(0).slider('value', 0);
	}
	else
	{
		imageElement.attr("src", "images/audioOn.png");
		$(this.videoID)[0].muted = false;
		$(this.elementID).find(".videoControlsContainer.volumeControl.volumeSlider").eq(0).slider('value', $(instance.videoID)[0].volume*100);
	}
}