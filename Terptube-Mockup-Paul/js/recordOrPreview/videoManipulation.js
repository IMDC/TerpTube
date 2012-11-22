/**
 * @author Marto
 */

function getTimeCodeFromSeconds(time)
{
	var timecode;
	var time = Math.floor(time*1000);
//	var mil = "" + Math.round(time % 1000);
	var sec = "" + Math.floor((time / 1000) % 60);
	var min = "" + Math.floor(((time / 1000) / 60) % 60);
	var hrs = "" + Math.floor(((time / 1000) / 60) / 60) % 60;
//		while (mil.length < 3)
//			mil = "0" + mil;
		while (sec.length < 2)
			sec = "0" + sec;
		while (min.length < 2)
			min = "0" + min;
		while (hrs.length < 2)
			hrs = "0" + hrs;

		return hrs + ":" + min + ":" + sec;// + "." + mil;
	return timecode;
}

function setBlur(flag, loadingText)
{
	if (flag)
		$("#videoContainer").addClass("loading"); 
	else
		$("#videoContainer").removeClass("loading");
	setBlurText(loadingText); 	
}

function ajaxSuccess(targetID, data, direct)
{
	$('#'+targetID).html(data);
	if (direct == "left" || direct =="right" || direct =="up" || direct =="down" || direct == "none")
	{
//		if (direct != "none")
//			$('#playerContent').hide("slide", {direction: direct}, 1000);
		
		if (direct == "left")
			$('#'+targetID).show("slide", {direction: "right"}, 500);
		else if (direct =="right")
			$('#'+targetID).show("slide", {direction: "left"}, 500);
		else if (direct =="up")
			$('#'+targetID).show("slide", {direction: "down"}, 500);
		else if (direct =="down")
			$('#'+targetID).show("slide", {direction: "up"}, 500);
	}
	//Reload the entire page with the result of the call
	//	closeWaitingDialog();
	//	$('html').html(data);
		
}
			
function refreshPage(targetID,address, dataSend, direct)
{
	//$('<div>test</div>').dialog('open');
//	waitingDialog({title: "", message: "loading..."});
	
	if (typeof direct === "undefined")
		direct = "left";
	if (direct != "none")
		$('#'+targetID).hide("slide", {direction: direct}, 500);
	$.ajax({ 
		url: address, 
		type: "POST", 
		contentType:"application/x-www-form-urlencoded", 
		data: dataSend,
		success: function (data){ajaxSuccess(targetID, data, direct);}
	});
	
}
function setBlurText(text)
{
	//check for null values or no argument sent
	if (text!=null && typeof text !== "undefined")
		$("#modal").html(text);
}

/*$("body").bind({
    ajaxStart: function() { 
        setBlur(true); 
    },
    ajaxStop: function() { 
        setBlur(false); 
    }    
});

*/