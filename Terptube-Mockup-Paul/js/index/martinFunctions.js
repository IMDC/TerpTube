		
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
			alert(data);
			$('#'+targetID).append(data);	},
		error:function(request){alert(request.statusText)}
	});
	
	
}

function popUpRecorder(element)
{
	$("#"+element).dialog ({
		autoOpen: false,
		resizable: false,
		modal: true,
		draggable: false,
		closeOnEscape: false,
		open: function(event, ui) {
			$(".ui-dialog-titlebar-close", this.parentNode).hide(); 
			//loadRecorderPage(element, "recordOrPreview/index.php","feature=record");
			jQuery.getScript("js/recordOrPreview/videoManipulation.js", function() {
				loadRecorderPage(element, "recordOrPreview/index.php", "feature=record" );});
//			$("#"+element).load("recordOrPreview/index.php?feature=record");
		},
		position: { my: "center top", at: "center top" },
		show: "blind",
		hide: "blind",
		minWidth: 700,
		title: "Video Record / Preview"
	});
	
	$("#"+element).dialog ("open");
//	alert(element);
	
}