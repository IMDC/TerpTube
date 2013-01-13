		
function loadRecorderPage(targetID,address, dataSend)
{
	//$('<div>test</div>').dialog('open');
//	waitingDialog({title: "", message: "loading..."});
	
	
	$.ajax({ 
		url: address, 
		type: "POST", 
		contentType:"application/x-www-form-urlencoded", 
		data: dataSend,
		success: function (data){
//			alert(data);
			$('#'+targetID).append(data);	},
		error:function(request){alert(request.statusText)}
	});
	
	
}

function popUpRecorder(element, feature, fileName, type)
{
	
	$("#"+element).dialog ({
		autoOpen: false,
		resizable: false,
		modal: true,
		draggable: false,
		closeOnEscape: false,
		dialogClass: "record-or-preview-dialog",
		open: function(event, ui) {
			$(".ui-dialog-titlebar-close", this.parentNode).hide(); 
			//loadRecorderPage(element, "recordOrPreview/index.php","feature=record");
			jQuery.getScript("js/recordOrPreview/videoManipulation.js", function() {
				var arguments = "feature="+feature;
				if (fileName)
					arguments+="&vidfile="+fileName;
				if (type)
					arguments+="&type="+type;
				loadRecorderPage(element, "recordOrPreview/index.php", arguments );});
//			$("#"+element).load("recordOrPreview/index.php?feature=record");
		},
		create: function(event, ui) {
			$(event.target).parent().css('position', 'relative');
		},
		position: { my: "center top", at: "center top", of: $("body") },
		show: "blind",
		hide: "blind",
		minWidth: 740,
		title: "Video Record / Preview"
	});
	
	$("#"+element).dialog ("open");
//	alert(element);
}

function closeRecorderPopUp(element)
{
	$("#"+element).dialog ("close");
}


function setBlur(flag, loadingText)
{
	//#videoRecordingOrPreview
	if (flag)
		$("#loadingIndicator").addClass("modal"); 
	else
		$("#loadingIndicator").removeClass("modal");
	setBlurText(loadingText); 	
}

function setBlurText(text)
{
	//check for null values or no argument sent
	if (text!=null && typeof text !== "undefined")
		$("#loadingIndicator").html(text);
}
