//       
  $(document).ready(function() {
  	
     	var creatingSignlink=false;
     	var canvas;
    	var ctx;
    	var x = 75;
    	var y = 50;
    	var WIDTH = 320;
    	var HEIGHT = 20;
    	var signlinkArray = new Array();
    	
    	//get inputs from post signlink
    	var startTimeInput = $('[name=signlink-start]');
 		var endTimeInput = $('[name=signlink-end]');
 		var linkInput = $('[name=signlink-link]');
 		$("#toggle-sl-creation").hide();
    	
    	//26 height so 13 each for y and 1 for x
    	var dragokstart = false;
    	var dragokend = false;
    	var startRectangle;
        var endRectangle; 
        canvas = document.getElementById("canvas");
    	ctx = canvas.getContext('2d');
		
		var video_dom = document.querySelector('#comment-video');
		var duration = 38;
		
		//when you hit the create signlink button it will toggle the signlink form
		//start the rectangle at the time in the video 
		//current point is given a point relative to the time in the video
     	$("#toggle-sl-creation").click(function() {
     	     $('#add-signlink').toggle();
     	     $("#toggle-sl-creation").hide();
     	     creatingSignlink = true;
     	     video_dom.pause();
     	     
     	     var currentPoint = convertTimeToPoint(video_dom.currentTime);
     	     startRectangle = {x: currentPoint, y: 0, width: 4, height: 40};
             endRectangle = {x: currentPoint + 10,y: 0, width: 4, height: 40 };
             //set the form start and end to the current marks
             startTimeInput.val(roundNumber(convertCanvasPointToTime(currentPoint),2));
             endTimeInput.val(roundNumber(convertCanvasPointToTime(currentPoint + 10),2));
             setInterval(draw, 10);
     	});
     	
     	$("#postSignlink").click(function() {
     		
     		if(!(startTimeInput.val() == "") && !(endTimeInput.val() == "") && !(linkInput.val() == ""))
     		{
     			var postObject = new signlink(startTimeInput.val(), endTimeInput.val(), linkInput.val());
     			signlinkArray.push(postObject);
     			$("#toggle-sl-creation").show();
       		    $('#add-signlink').toggle();
     			
     			resetSignLinkForm();
     			creatingSignlink = false;
     		}
     		else
     		{
     		    alert('Please fill in the forms before posting');	
     		}
     	});
     	
       function createUploader(){            
            var uploader = new qq.FileUploader({
                element: document.getElementById('file-uploader-demo1'),
                action: 'scripts/ajaxupload/upload.php',
                debug: true,
                onComplete: function(id, fileName, responseJSON){
                	
                	changeMovieSource('uploads/signlink/temp/' + fileName, fileName);
                }
            });           
        }
       
        window.onload = createUploader;
    
        //Change the movie source when a thumb is clicked
		function changeMovieSource(url, title){
                
		  		   var $video = $('#comment-video');
		  		   
		  		    
                	$('#comment-video').show();
		       	 	$('.video-text-description').show();
		       	 	$('#file-uploader-demo1').hide();
		       	 	$('[name=signlink-start]').val('0');
		       	 	$('[name=signlink-end]').val('0');
                    $('#canvas').show();
                    $("#toggle-sl-creation").show();
		        	try {
		         	    document.getElementById('comment-video').src = url;
		       		}
		      		 catch (e) {
		      	    	 alert(e);
		      		 }     
		       		$video.attr('autoplay', 'true');
		       		$video.data('currentTitle', title);
		      		document.getElementById('comment-video').load();
		      		
		       		videoChangingPending = false;
		  }
		

		
   //--------------------CANVAS CONTROLS ----------------------------------------------------------------------

	  $("canvas").mousedown(function myDown(e){

				var position = $("canvas").position();

				if (e.clientX < startRectangle.x + 2 + position.left && e.clientX > startRectangle.x - 2 +
						position.left && e.clientY < startRectangle.y + 20 + position.top &&
						e.clientY > startRectangle.y -20 + position.top){
					
					    startRectangle.x = e.clientX;
					    dragokstart = true;


						$("canvas").mousemove(function myMove(e){

							if (dragokstart && (e.clientX - position.left) < endRectangle.x - 20){
								startRectangle.x = e.client
								
								X - position.left;
								
								//give it a canvas point it gives back current time
							    video_dom.currentTime = convertCanvasPointToTime(startRectangle.x); 
							    startTimeInput.val(roundNumber(video_dom.currentTime,2));
							}
						});
				}
				
				if (e.clientX < endRectangle.x + 2 + position.left && e.clientX > endRectangle.x - 2 +
						position.left && e.clientY < endRectangle.y + 20 + position.top &&
						e.clientY > endRectangle.y - 20 + position.top){
					
					   endRectangle.x = e.clientX;
					  // endRectangle.y = e.clientY;
					   dragokend = true;


						$("canvas").mousemove(function myMove(e){

							if (dragokend && (e.clientX - position.left) > (startRectangle.x + 5)){
								endRectangle.x = e.clientX - position.left;
							    
								//give it a canvas point it gives back current time
								video_dom.currentTime = convertCanvasPointToTime(endRectangle.x);
								endTimeInput.val(roundNumber(video_dom.currentTime,2));
							}
						});
				}

		});



		$("canvas").mouseup(function myUp(e){
				dragokstart = false;
				dragokend = false;
				$("canvas").mousemove(null);

				});
			
	     function clear() {  
				ctx.clearRect(0, 0, WIDTH, HEIGHT);
		  }


		function draw() {
			clear();
			
				ctx.fillStyle = "#FAF7F8";
				rect(0,0,WIDTH,HEIGHT);
				
				ctx.fillStyle = "#999999";
				
				//create rects for previously recorded signs
				for(var i = 0; i < signlinkArray.length; i++)
			    {
					var start = convertTimeToPoint(signlinkArray[i].startTime);
			        var end = convertTimeToPoint(signlinkArray[i].endTime)
					rect(start, startRectangle.y - 20,  end - start , 40);
				}
				
				//if they are creating a signlink add it to canvas
				if(creatingSignlink){
					ctx.fillStyle = "#444444";
					rect(startRectangle.x - 2, startRectangle.y - 20, startRectangle.width, startRectangle.height);
					rect(endRectangle.x - 2, endRectangle.y - 20, endRectangle.width, endRectangle.height);
					ctx.fillStyle = "#0000CD"
					rect(startRectangle.x + 2, endRectangle.y - 20,  endRectangle.x - startRectangle.x -4 , 40);
				}
		 }

		function rect(x,y,w,h) {
			ctx.beginPath();
			ctx.rect(x,y,w,h);
			ctx.closePath();
			ctx.fill();
		}
		
		
		//----------------------GENERAL FUNCTIONS ----------------------
		function signlink(start,end,link)
		{
			this.startTime=start;
			this.endTime=end;
			this.link=link;
		}
		
		function resetSignLinkForm(){
			startTimeInput.val("");
	 		endTimeInput.val("");
	 		linkInput.val("");
		}
		
		//give it a canvas point and it will give back a time
		function convertCanvasPointToTime(x)
		{
			var canvasPercentage = x / canvas.width;
			var time = canvasPercentage * duration;
			return time;
		}
		
		//give it a time percentage and it will give back a canvas point
		function convertTimeToPoint(x)
		{
			var timePercentage = x / duration;
			var canvasPoint = timePercentage * canvas.width;
			return canvasPoint;
		}
		
		//round number, num is number you want to round and dec is the number of decimal places
		function roundNumber(num, dec) {
			var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
			return result;
		}

});
   
	

        // in your app create uploader as soon as the DOM is ready
        // don't wait for the window to load  
  