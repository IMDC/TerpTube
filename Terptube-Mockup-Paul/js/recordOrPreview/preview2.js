function goBack(where)
{
 //Check if person came from recording video, or from uploading video
	if (where=="record")
		refreshPage('playerContent', "recordOrPreview/record.php","", "right");
	else if (where == "upload")
	{
		//Delete current video.
		closeRecorderPopUp('videoRecordingOrPreview');
	//	refreshPage('playerContent', "upload.php","", "right");
	}
		
}
function transcodeAjax(inputVideoFile, outputVideoFile, keepVideoFile, controls)
{
	//FIXME something fucks up here
	controls.setControlsEnabled(false);
	var keepAudio = true;
	if (controls.options.audioBar)
	{
		if ($(controls.elementID).find("#audioOff").eq(0).attr("checked"))
			keepAudio = false;
	}
	
	if (controls.currentMinSelected == controls.minSelected && controls.currentMaxSelected == controls.maxSelected)
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
			startTime: controls.currentMinTimeSelected, 
			endTime: controls.currentMaxTimeSelected, 
			keepInputFile: inputVideoFile,
			keepAudio: keepAudio},
		success: function (data){transcodeSuccess(data, controls);},
		error: function (data) {transcodeError(data, controls);}
	});
	
}

/**
 * Possible arguments: trim(yes/no),convert(yes/no) startTime(seconds), endTime(seconds), keepInputFile(true/false), keepAudio(true/false) 
 */
function transcodeAjax2(inputVideoFile, outputVideoFile, arguments, onSuccess, onError)
{
//	setControlsEnabled(false);
/*	if (controls.currentMinSelected == controls.minSelected && controls.currentMaxSelected == controls.maxSelected)
	{
		//No need to trim as the user has not moved the start/end points
	}*/
	if (arguments.blurText)
		setBlur(true,arguments.blurText);
	else
		setBlur(true,"Converting Video.");
	
	var ajaxArgs = new Array();
	ajaxArgs = arguments;
	ajaxArgs['inputVidFile'] = inputVideoFile;
	ajaxArgs['outputVidFile'] = outputVideoFile;
		$.ajax({
		url: "recordOrPreview/transcoder.php", 
		type: "POST",
		data: ajaxArgs,
		success: onSuccess,
		error: onError
	});	
}

function transcodeSuccess(data, controls)
{	
	setBlurText("");
	setBlur(false);
	controls.setControlsEnabled(true);
//	alert("VideoFile created: "+data);
	//window.location.href = "recordOrPreview/streams.php";
	updateFileNameField("fileName", data);
	
	var $optionFieldset = $('[name=video-option-fieldset]');
	var $videoNameFieldset= $('[name=video-name-fieldset]');
	$optionFieldset.hide();
	$videoNameFieldset.show();
	$('.video-title').text(data);
	closeRecorderPopUp('videoRecordingOrPreview');
}

function updateFileNameField(fieldName, fileName)
{
	$("#"+fieldName).val(fileName);
}
function transcodeError(data, controls)
{
	setBlurText("");
	setBlur(false);
	controls.setControlsEnabled(true);
	alert("Transcode failed: "+data);
}