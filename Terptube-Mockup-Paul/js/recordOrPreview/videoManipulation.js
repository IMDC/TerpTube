/**
 * @author Marto
 */

function getTimeCodeFromSeconds(time, duration, separator)
{
	time = Math.floor(time*1000);
	time/=1000;
	var sec = "" + Math.floor(time % 60);
	var min = "" + Math.floor((time / 60) % 60);
	var hrs = "" + Math.floor((time / 60) / 60) % 60;
	
	while (sec.length < 2)
		sec = "0" + sec;
	if ( typeof(duration) == 'undefined')
	{
		while (min.length < 2)
			min = "0" + min;
		if (hrs == 0)
			return  min + ":" + sec;
		else
			return hrs + ":" + min + ":" + sec;
	}
	
	duration = Math.floor(duration*1000);
	duration/=1000;
	var durationSec = ""+Math.floor(duration % 60);
	var durationMin = Math.floor((duration / 60) % 60);
	var durationHrs = Math.floor((duration / 60) / 60) % 60;
	
	while (durationSec.length < 2)
		durationSec = "0" + durationSec;
		
	var resultDuration = ""+durationSec;
	var result = ""+sec;
	
	if (durationHrs == 0)
	{
		if (durationMin == 0)
		{
			
		}
		else 
		{
			if (durationMin>9)
			{
				while (min.length < 2)
					min = "0" + min;	
			}
			resultDuration = durationMin+":"+durationSec;
			result = min+":"+sec;
		}
		resultDuration = durationMin+":"+durationSec;
		result = min+":"+sec;
	}
	else
	{
		resultDuration = durationHrs + ":" + durationMin + ":" + durationSec;

		while (min.length < 2)
			min = "0" + min;
		while (hrs.length < 2)
			hrs = "0" + hrs;
		
		result = hrs + ":" + min + ":" + sec;
	}
	
	if ( typeof(separator) == 'undefined')
		return result;
	else
		return result + separator + resultDuration;
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