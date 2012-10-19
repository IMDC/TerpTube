   $(document).ready(function() {
			//generate a random color for the rectangles
		  	 function get_random_color() {
		  			var letters = '0123456789ABCDEF'.split('');
		  			var color = '#';
		  			for (var i = 0; i < 6; i++ ) {
		  				color += letters[Math.round(Math.random() * 15)];
		  			}
		  			return color;
		  	  }


		  	 //Change the movie source when a thumb is clicked
		  	function changeMovieSource(url, title){
		          var $video = $('#comment-video');
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

		  	//starts the player auto
		  	function startPlayer(url, title) {

		      	 if(videoChangingPending == true) 
		         		  return;

		      	 document.getElementById('comment-video').pause();
		      	 videoChangingPending = true;
		       	 var changeMovieCallback = function(){ changeMovieSource(url, title);}
		      	 var t = setTimeout(changeMovieCallback, 800);

		       }
		  	
		  	 //to make the bar move
			   function animate(lastTime, myRectangle){

	             //This will light up the box red if there is a comment at that second
	             //If current link is = to -1 that means there is no comment at this time. 
	             //if it slectes a comment at the time it akes currentLink the array id and will keep checking until it is off
	             //and change it back to blue
	             
		           if(currentLink == -1){
				     for(var i = 0; i < linkStartTimes.length; i++)
				     {
					     if((video_dom.currentTime >= linkStartTimes[i] && video_dom.currentTime <= linkEndTimes[i]) || (video_dom.currentTime == linkStartTimes[i]))
					     {
					    	 $(".source-media-container").css("background", "red");
					    	 currentLink = i;
					    	 sourceVideoClickable = true;
					    	 break;
					     }
					    
				      }       
		           }
		           else if ((video_dom.currentTime >= linkStartTimes[currentLink] && video_dom.currentTime <= linkEndTimes[currentLink]) || (video_dom.currentTime == linkStartTimes[currentLink])){
		        	    
			       } 
		           else{
		        	   $(".source-media-container").css("background", "blue");
		        	     currentLink = -1;
		        	     sourceVideoClickable = false;
			       }


			    	if(playing){
				        // calculate the percentage of current time in relation to the canvas size
				        //call move Playhead to advance the play head
			    	    var currentTime = video_dom.currentTime;
			    	    var duration = video_dom.duration;
			    	    var percentage = currentTime/duration;
			    	    movePlayHead(percentage);
			    	    
			    	 }

			    	    // request new frame
			    	 requestAnimFrame(function(){
			    	        	animate(lastTime, myRectangle);
			    	 });
			    } 

			    function movePlayHead(xVar){

			    	// clear
		    	    traversalctx.clearRect(0, 0, traversalCanvas.width, traversalCanvas.height);
		    	 
		    	    // draw
		    	    traversalctx.beginPath();
		    	    traversalctx.rect(xVar*traversalCanvas.width, myRectangle.y, myRectangle.width, myRectangle.height);
		    	 
		    	    traversalctx.fillStyle = "black";
		    	    traversalctx.fill();
		    	    traversalctx.lineWidth = myRectangle.borderWidth;
		    	    traversalctx.strokeStyle = "black";
		    	    traversalctx.stroke();

			    }
   }
