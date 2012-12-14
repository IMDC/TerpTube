
function createControls()
{
	/*
	 *  options type can be player, record, preview
	 */
	alert("ElementID = "+this.elementID);
	$(this.elementID).addClass("videoControlsContainer");
	$(this.elementID).append('<div class="videoControlsContainer track"></div>');
	$(this.elementID+" .videoControlsContainer.track").append('<canvas class="videoControlsContainer track densitybar" width="'+$(this.videoID).width()+'px" height="'+this.options.densityBarHeight+'px"></canvas>').append('<canvas class="videoControlsContainer track selectedRegion" width="'+$(this.videoID).width()+'px" height="'+this.options.densityBarHeight+'px"></canvas>').append('<canvas class="videoControlsContainer track thumb" width="'+$(this.videoID).width()+'px" height="'+this.options.densityBarHeight+'px"></canvas>');
	$(this.elementID).append('<div class="videoControlsContainer timeBox">0:00:00/0:00:00</div>');
	if (this.options.volumeControl)
	{
		$(".videoControlsContainer.track.thumb").mouseover(function (){setVolumeBarVisible(this.elementID,true)});
		$(this.elementID).append('<div class="videoControlsContainer volumeControl"></div>');
		$(".videoControlsContainer.volumeControl").mouseover(function (){setVolumeBarVisible(this.elementID,false)});
		$(".videoControlsContainer.volumeControl").append('<img alt="volume control" src="images/recordOrPreview/audioOn.png" />').append('<div class="videoControlsContainer volumeControl volumeSlider"></div>');
		$(".videoControlsContainer.volumeControl image").click(function(){toggleMute(this.videoID, this.elementID)});
		
		$(function() {
			$(this.elementID+".videoControlsContainer.volumeControl.volumeSlider").slider({
				orientation: "horizontal",
				range: "min",
				min: 0,
				max: 100,
				value: 100,
				slide: function (event, ui) {
					$(this.videoID)[0].volume=ui.value/100;
					}
				});
		});
	}
	$(this.elementID).append('<div class="videoControlsContainer controlsBar"></div>');
	$(".videoControlsContainer.controlsBar").append('<div class="videoControlsContainer controlsBar backButtons"></div>').append('<div class="videoControlsContainer controlsBar forwardButtons"></div>').append('<div class="videoControlsContainer controlsBar videoControls"></div>');
	$(".videoControlsContainer.controlsBar.backButtons").append('<button type="button" class="videoControlsContainer controlsBar backButtons backButton"></button>');
	$(".videoControlsContainer.controlsBar.backButtons.backButton").click(this.options.backFunction);
	$(".videoControlsContainer.controlsBar.videoControls").append('<button type="button" class="videoControlsContainer controlsBar videoControls beginButton"></button>').append('<button type="button" class="videoControlsContainer controlsBar videoControls stepBackwardButton>"</button>').append('<button type="button" class="videoControlsContainer controlsBar videoControls playButton>"</button>').append('<button type="button" class="videoControlsContainer controlsBar videoControls stepForwardButton>"</button>');
	$(".videoControlsContainer.controlsBar.videoControls.beginButton").click(function(){jumpTo(this.videoID,0)});
	$(".videoControlsContainer.controlsBar.videoControls.stepBackwardButton").click(function(){stepBackward(this.videoID)});
	$(".videoControlsContainer.controlsBar.videoControls.playButton").click(function(){playPause(this.videoID)});
	$(".videoControlsContainer.controlsBar.videoControls.stepForwardButton").click(function(){stepForward(this.videoID)});
	$(".videoControlsContainer.controlsBar.videoControls.endButton").click(function(){jumpTo(this.videoID,1)});
	$(".videoControlsContainer.controlsBar.forwardButtons").append('<button type="button" class="videoControlsContainer controlsBar backButtons backButton>"</button>');
	$(".videoControlsContainer.controlsBar.forwardButtons.forwardButton").click(this.options.backFunction);
	if (this.options.audioBar)
	{
		$(".videoControlsContainer").find(".videoControlsContainer.controlsBar").eq(0).append('<div class="videoControlsContainer controlsBar audioButtonsBar"></div>');
		$(".videoControlsContainer.controlsBar.audioButtonsBar").append('Remove audio from the video?<br />'+
			'<label for="audioOff"><img src="images/recordOrPreview/audioOff.png" width="30px" height="30px" alt="audio enabled" /> </label>'+
			'<input type="radio" name="audioEnabled" value="false" id="audioOff" />'+
			'<label for="audioOn"><img src="images/recordOrPreview/audioOn.png" width="30px" height="30px" alt="audio enabled" /> </label>'+
			'<input type="radio" name="audioEnabled" value="true" class="record-or-preview" id="audioOn" checked="checked" />');
	}
	
	var instance = this;
	$(this.videoID).on('loadedmetadata',function(){instance.setupVideo()});
 
	/*
	$(elementID+".videoControlsContainer.track.densitybar")[0].width=$(elementID+".videoControlsContainer.track.densitybar").width();
	$(elementID+".videoControlsContainer.track.densitybar")[0].height=$(elementID+".videoControlsContainer.track.densitybar").height();
	$(elementID+".videoControlsContainer.track.thumb")[0].width=$(elementID+".videoControlsContainer.track.thumb").width();
	$(elementID+".videoControlsContainer.track.thumb")[0].height=$(elementID+".videoControlsContainer.track.thumb").height();
	$(elementID+".videoControlsContainer.track.selectedRegion")[0].width=$(elementID+".videoControlsContainer.track.selectedRegion").width();
	$(elementID+".videoControlsContainer.track.selectedRegion")[0].height=$(elementID+".videoControlsContainer.track.selectedRegion").height();
	this.drawTrack();
	*/
}


function densityBar(elementID, videoID, options)
{
	this.elementID = "#"+elementID;
	this.videoID = "#"+videoID;
	
	this.trackPadding = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0).width() / 15;
	this.trackWidth = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0).width()/15 - 2*this.trackPadding;
	this.trackHeight = $(this.elementID).find(".videoControlsContainer.track.densitybar").eq(0).height()-2*this.trackPadding;
	
	alert ("densityBar:"+$(this.elementID).find(".videoControlsContainer.track.densitybar").size());
	alert("trackWidth: "+$(this.trackWidth));
	
	this.currentTime = 0;
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
	this.options = options;
	
	this.createControls = createControls;
	this.drawTrack = drawTrack;
	this.updateTimeBox = updateTimeBox;
	this.paintThumb =  paintThumb1;
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
	this.setHighlightedRegion = setHighlightedRegion;
	this.setupVideo = setupVideo1;
	this.getTimeForX = getTimeForX;
	this.sestVideoTimeFromCoordinate = setVideoTimeFromCoordinate;
	this.setVideoTime = setVideoTime;
	this.setControlsEnabled = setControlsEnabled;
	this.setVolumeBarVisible = setVolumeBarVisible;
	this.toggleMute = toggleMute;
	
	if (typeof this.options=='undefined')
	{
		alert("Options is undefined");
		this.options = {
				volumeControl:true,
				type:"player",
				backFunction:function(){alert("Back")},
				forwardFunction:function(){alert("Forward")},
				audioBar:true,
				densityBarHeight: 40
				}
		alert("Options is undefined endo of method");
	}
	if (typeof this.options.volumeControl=='undefined')
	{
		this.options.volumeControl = true;
	}
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
	var context = $(this.elementID+".videoControlsContainer.track.densitybar")[0].getContext("2d");
	context.lineJoin = "round";
	context.fillStyle = "#cccccc";
	context.strokeStyle = "#000000"; 
	context.strokeRect(0,0, $(elementID+".videoControlsContainer.track.densitybar").width(), $(this.elementID+".videoControlsContainer.track.densitybar").height());
	context.fillRect(this.trackPadding, this.trackPadding, this.trackWidth, this.trackHeight);
	context.strokeRect(this.trackPadding, this.trackPadding, this.trackWidth, this.trackHeight);
	
}

function updateTimeBox(currentTime, duration)
{
	$(this.elementID+".videoControlsContainer.timeBox").html(getTimeCodeFromSeconds(currentTime) +"/"+getTimeCodeFromSeconds(duration));
}


function paintThumb1(time)
{
	alert("Track Height: "+this.trackHeight);
	alert(this.elementID+".videoControlsContainer.track.thumb");
	alert($(this.elementID+".videoControlsContainer.track.thumb"));
	var context = $(this.elementID).find(".videoControlsContainer.track.thumb").eq(0)[0].getContext("2d");
	var position = this.getXForTime(time);
	if (time==0)
		position = this.trackPadding;
	context.clearRect(0, 0, $(this.elementID+".videoControlsContainer.track.densitybar").width(), $(this.elementID+".videoControlsContainer.track.densitybar").height());
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
	context.lineTo(position, $(this.elementID+".videoControlsContainer.track.densitybar").height()-trackPadding);
	context.closePath();
	context.stroke();
}

function getXForTime(time)
{
	var x = this.trackPadding + $(this.videoID)[0].currentTime/$(this.videoID)[0].duration * this.trackWidth;
	return x;
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

function jumpTo(videoID, jumpPoint)
{
	if (jumpPoint==0)
		setVideoTime(videoID, currentMinTimeSelected);
	else if (jumpPoint==1)
		setVideoTime(videoID, currentMaxTimeSelected);
		
}

function checkStop()
{
	// if (video.paused)
		// return;
	if ($(videoID)[0].currentTime >= currentMaxTimeSelected)
	{
		pause($(videoID)[0]);
		$(videoID)[0].currentTime = currentMaxTimeSelected;
	}
	repaint();
}


function play()
{
	if ($(videoID)[0].paused)
		$(videoID)[0].play();
	//preview = false;
//	timer = setInterval("checkStop()", 100);
}

function pause()
{
	if (!$(videoID)[0].paused)
		$(videoID)[0].pause();
//	clearInterval(timer);
	preview = false;
}

function playPause()
{
	//Change icon on button
	if ($(videoID)[0].paused)
		play(videoID);
	else
		pause(videoID);	
}

function setPlayButtonIconSelected(isPlayIcon)
{
	var playButton = $(this.elementID+".videoControlsContainer.controlsBar.videoControls.playButton")[0];
	
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
		this.currentTime = $(this.videoID)[0].currentTime;
		this.paintThumb(this.currentTime);
		var timeBoxCurrentTime = this.currentTime-this.currentMinTimeSelected;
		timeBoxCurrentTime = timeBoxCurrentTime <=0 ? 0: timeBoxCurrentTime;
		updateTimeBox(timeBoxCurrentTime, this.currentMaxTimeSelected-this.currentMinTimeSelected);
		if (this.preview && this.currentTime >= this.currentMaxTimeSelected)
		{
			preview = false;
			$(this.videoID)[0].pause();
			this.setVideoTime(this.currentMaxTimeSelected);
		}
	//	setHighlightedRegion(currentMinSelected, currentMaxSelected);
}

function stepForward()
{
	if ($(videoID)[0].currentTime + this.stepSize > this.currentMaxTimeSelected)
	{
		$(videoID)[0].currentTime = this.currentMaxTimeSelected;
	}
	else
		$(videoID)[0].currentTime+= this.stepSize;
	this.repaint();
}

function stepBackward(videoID)
{	
	if ($(videoID)[0].currentTime -this.stepSize < this.currentMinTimeSelected)
	{
		$(videoID)[0].currentTime = this.currentMinTimeSelected;
	}
	else
		$(videoID)[0].currentTime-= this.stepSize;
	this.repaint();
}

function setMouseOutThumb(elementID,event)
{
	$(elementID+".videoControlsContainer.track.thumb").off('mousemove');
}

function setMouseDownThumb(elementID, event)
{
	var thumbCanvas = $(elementID+".videoControlsContainer.track.thumb");
	var selectedRegionCanvas = $(elementID+".videoControlsContainer.track.selectedRegion")[0];
	var coords = this.getRelativeMouseCoordinates(event);
	preview = false;

	if (coords.y < trackPadding + trackHeight)
	{	//Restrict the playhead to only within the selected region
		thumbCanvas.on('mousemove', function(event){ 
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
		});
		if (coords.x >= currentMinSelected && coords.x<=currentMaxSelected)
			setVideoTimeFromCoordinate(coords.x);
	}
	else
	{
		if (coords.x <=currentMinSelected && coords.x >= currentMinSelected - triangleWidth)
		{
			//Left triangle selected			
			var offset = currentMinSelected - coords.x;
			thumbCanvas.on('mousemove', function(event){
				var coords = getRelativeMouseCoordinates(event);
				currentMinSelected = coords.x + offset;
				if (currentMinSelected < minSelected )
					currentMinSelected = minSelected;
				if (currentMinSelected > currentMaxSelected - minTimeCoordinate)
					currentMinSelected = currentMaxSelected - minTimeCoordinate;
				currentMinTimeSelected = getTimeForX(currentMinSelected);
				setHighlightedRegion(currentMinSelected, currentMaxSelected);
				setVideoTime(currentMinTimeSelected);
			});

		}
		else if (coords.x >=currentMaxSelected && coords.x <=currentMaxSelected + triangleWidth)
		{
			//Right triangle selected	;
			var offset = coords.x - currentMaxSelected;
			thumbCanvas.on('mousemove', function(event){
				var coords = getRelativeMouseCoordinates(event);
				currentMaxSelected = coords.x - offset;
				if (currentMaxSelected > maxSelected)
					currentMaxSelected = maxSelected;
				if (currentMaxSelected < currentMinSelected + minTimeCoordinate)
					currentMaxSelected = currentMinSelected + minTimeCoordinate;
				currentMaxTimeSelected = getTimeForX(currentMaxSelected);
				setHighlightedRegion(currentMinSelected, currentMaxSelected);
				setVideoTime(currentMaxTimeSelected);
			});
		}
	}
}

function setHighlightedRegion(startX, endX)
{
	//alert (currentMinSelected +" "+startX);
	//if (currentMinSelected==startX && currentMaxSelected==endX)
	//	return; 
	var context = $(this.elementID+".videoControlsContainer.track.selectedRegion")[0].getContext("2d");
	context.clearRect(0, 0, $(this.elementID+".videoControlsContainer.track.densitybar").width(), $(this.elementID+".videoControlsContainer.track.densitybar").height());
	context.fillStyle = "#00ff00";
	
	context.fillRect(startX, trackPadding, endX-startX, $(this.elementID+".videoControlsContainer.track.densitybar").height()-2*this.trackPadding);
	this.drawLeftTriangle(startX, context);
	this.drawRightTriangle(endX, context);
}

function setupVideo1()
{
	var instance = this;
	$(this.videoID).on('timeupdate', function(){instance.checkStop()}, false);
	$(this.videoID).on('play', function(){instance.setPlayButtonIconSelected(false)}, false);
	$(this.videoID).on('pause', function(){instance.setPlayButtonIconSelected(true)}, false);
	alert(this.elementID)
	this.paintThumb(0);
	this.minTimeCoordinate = this.getXForTime(minTime);
	this.currentMinSelected = this.minSelected;
	this.currentMinTimeSelected = this.getTimeForX(currentMinSelected);
	this.currentMaxSelected = this.maxSelected;
	this.currentMaxTimeSelected = this.getTimeForX(currentMaxSelected);
	this.setHighlightedRegion(this.currentMinSelected, this.currentMaxSelected);

	this.repaint();
	$(elementID+".videoControlsContainer.track").on('mouseleave',function(){ this.setVolumeBarVisible(false)});
	$(elementID+".videoControlsContainer.track.thumb").on('mousedown',function(e){this.setMouseDownThumb(elementID,e)});
	$(elementID+".videoControlsContainer.track.thumb").on('mouseout', function(e){this.setMouseOutThumb(elementID,e)});
	$(elementID+".videoControlsContainer.track.thumb").on('mouseup', function(e){$(elementID+".videoControlsContainer.track.thumb").off("mouseout")});

}

function getTimeForX(x)
{
	var time = (x - trackPadding)*video.duration/trackWidth;
	return time;
}

function setVideoTimeFromCoordinate(position)
{
	var time = getTimeForX(position);
	if (time != $(videoID)[0].currentTime)
		$(videoID)[0].currentTime = time;	
	repaint();
}

function setVideoTime(videoID, time)
{
	if (time != $(videoID)[0].currentTime)
		$(videoID)[0].currentTime = time;	
	repaint();
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


function setControlsEnabled(elementID,flag)
{
	if (flag)
	{
		$(elementID+".videoControlsContainer :input").prop('disabled', false);
		$(elementID+".videoControlsContainer.track.thumb").on('mousedown',function(e){setMouseDownThumb(e)});	
	}
	else
	{
		$(elementID+".videoControlsContainer :input").prop('disabled', true);
		$(elementID+".videoControlsContainer.track.thumb").off('mousedown');
	}
}



function setVolumeBarVisible(elementID, flag)
{
	if (flag)
//		$("#volumeSlider").css("display", "block");
		$(elementID+".videoControlsContainer.volumeControl.volumeSlider").show('slide', {direction: "right"}, 200);
	else
//		$("#volumeSlider").css("display", "none");
		$(elementID+".videoControlsContainer.volumeControl.volumeSlider").hide('slide', {direction: "right"}, 200);
}

function toggleMute(videoID, elementID)
{
	if ($(elementID+".videoControlsContainer.volumeControl image").attr("src").match("images/audioOn.png"))
	{
		$(elementID+".videoControlsContainer.volumeControl image").attr("src", "images/audioOff.png");
		$(videoID)[0].muted = true;
		$(elementID+".videoControlsContainer.volumeControl.volumeSlider").slider('value', 0);
	}
	else
	{
		$(elementID+".videoControlsContainer.volumeControl image").attr("src", "images/audioOn.png");
		$(videoID)[0].muted = false;
		$(elementID+".videoControlsContainer.volumeControl.volumeSlider").slider('value', $(videoID)[0].volume*100);
	}
}