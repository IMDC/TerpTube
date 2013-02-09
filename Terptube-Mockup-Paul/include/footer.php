<script>
    $(document).ready(function() {
        //TODO: make db table for source video

		var controls = new DensityBar("main-video-container","myPlayer");
		controls.options.minLinkTime = 1;
		controls.options.backFunction= undefined;
		controls.options.backButton = false;
		controls.options.forwardButton = false;
		controls.options.forwardFunction = undefined;
		controls.options.volumeControl = true;
		controls.options.audioBar = false;
		controls.options.type = DENSITY_BAR_TYPE_PLAYER;
		controls.options.playHeadImage = "images/feedback_icons/round_plus.png";
		controls.options.playHeadImageOnClick = function(){ 
			$postCommentButton.click();
			$("span#toggle-time-span").click();
		};
		controls.options.onAreaSelectionChanged = function(){
				startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
                endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
		};
		controls.options.onCommentMouseOver = function(comment)
		{
			var commentElement = $("div").find("[data-cid='" + comment.id + "']").eq(0);
			comment.originalOutline = commentElement.css("outline");
			commentElement.css("outline", "3px solid red");
		};
		controls.options.onCommentMouseOut = function(comment)
		{
			var commentElement = $("div").find("[data-cid='" + comment.id + "']").eq(0);
			commentElement.css("outline", comment.originalOutline);
			comment.originalOutline = undefined;
		};
		
		controls.options.signLinkColor = "#0000FF";
		controls.createControls();
		
        //CONSTANTS
        var CAPTION_HIDDEN = 1;
        var CAPTION_SHOW = 2;


        var textVisible = false;
        //set up variables
        var speedSlow = false;
        var video_dom = $("video#myPlayer").get(0);

        var $commentformcontainer =  $("#comment-form-wrap");

        //form inputs
        var startTimeInput     = $("#start_time");
        var endTimeInput       = $("#end_time");
        
        var $selectVideoDrop    = $("[name=select-video-names]");  // unused?
        var $optionFieldset     = $("#video-option-fieldset"); // contains the existing video,record,upload inputs
        var $videoNameFieldset  = $("#video-name-fieldset");   // will display selected video to be uploaded on form
        
        var $postCommentButton  = $("#postCommentButton");
        var $cancelButton       = $("#cancel-button");
        var selectedVideoName;
        
        var $selectedComment;

        var signlinkArray = new Array();
        var fullCommentArray = new Array();

        window.onload = createUploader($(this));

        //jQuery('#mycarousel').jcarousel();
        
        $selectVideoDrop.change(function() {
            $optionFieldset.hide();
            $videoNameFieldset.show();
            selectedVideoName = $selectVideoDrop.find('option:selected').text();
            $('.video-title').text(selectedVideoName);
            $("[name=file-name]").val(selectedVideoName);
        });

        // what actions happen when you click the new comment form 'cancel' button
        $cancelButton.click( function() {
            $optionFieldset.show();
            $videoNameFieldset.hide();
            endTimeInput.off("change");
            startTimeInput.off("change");
        	controls.setPlayHeadImage("images/feedback_icons/round_plus.png");
            //$(".comment-details").detach().appendTo("#content-left"); // move form to left side underneath 'post new comment' button
            //$(".comment-details").hide(); // hide the comment details form
            $commentformcontainer.detach().appendTo("div#content-left");
            
            resetNewCommentFormValues();
            
            $commentformcontainer.hide();
            
            showCommentReplyLinks();  // show all comment reply links if previously hidden
            showCommentTools();       // show all the comment tools divs that contain edit/delete links
            
            // drawAreaOnBar(signlinkArray);
            // drawAreaOnBar(commentArray);
            
            creatingTimedComment = false;
            
            $postCommentButton.show();
            
			controls.setAreaSelectionEnabled(false);
			controls.currentMinSelected = controls.minSelected;
			controls.currentMinTimeSelected = controls.getTimeForX(controls.currentMinSelected);
			controls.currentMaxSelected = controls.maxSelected;
			controls.currentMaxTimeSelected = controls.getTimeForX(controls.currentMaxSelected);
			controls.clearDensityBar();
			controls.drawComments();
			controls.drawSignLinks();
			controls.repaint();
			
            // remove the css class from the comment you clicked 'reply' on
            //console.log("removing css class from selectedComment element: " + $selectedComment.id);
            //$selectedComment.removeClass("writing-reply");
            
            // reset the variable
            $selectedComment = null;

        });
        
        function resetNewCommentFormValues() {

            //var $commdet = $(".comment-details");
            var $commdet = $commentformcontainer;
            
            // show the span that wraps the start and end time inputs
            // and hide the div that wraps the inputs
            $("span#toggle-time-span").show();
            $("#new-comment-time-div").hide();
            
            
            // reset start and end time inputs
            $("#new-comment-time-div input").val('');
            
            // clear text area
            $("#comment-textarea").attr("value", "");
            // reset existing video to blank choice
            $commdet.find("#userExistingVideo").val(0);
            
            // reset the video upload area fields
            $commdet.find("#video-name-fieldset div p.video-title").empty();
            $commdet.find("#video-name-fieldset").hide();
            $commdet.find("#video-option-fieldset").show();
            
            // clear file input hidden var and reset form action hidden var to 'new' value
            $commdet.find("#fileName").val('');
            $commdet.find("#formAction").val('new');
            
            // change Text in comment submit button back to original text
            $commdet.find("input#new-comment-submit-button").attr("value", "Post Comment");
            
            // reset more hidden form values back to default
            $commdet.find("#parentCommentID").attr("value", "");
            $commdet.find("#selectedCommentID").attr("value", "");
            
            // show the span that allows you to apply comment to video timeline
            $("#toggle-time-span").show();
        }

        // When the user clicks the 'modify' link inside the new comment form
        // this needs to show the video option fieldset that contains the buttons
        // to record a new video, upload a video or choose an existing video
        $(".edit-video-link").click(function(event){
            //prevent the click from scrolling the page
            event.preventDefault();
            
            $optionFieldset.show();
            $videoNameFieldset.hide();
        });

        // Editing a comment by clicking on it's 'edit' button
        $("a.comment-edit-link").click(function(event) {
            
            // stop the click from scrolling us around the page
            event.preventDefault();
            
            $cancelButton.click();
            // $(".comment-details").show();
            $postCommentButton.hide();
            
            hideCommentReplyLinks();
            hideCommentTools();
            
            // grab relevant comment info like id and type
            var commentID   = $(this).data('cid');
            var commentType = $(this).data('ctype');
            
            // get comment id
            //var commentID = $(this).attr('id').replace("edit-", '');
            
            // set the 'global' selected comment variable
            $selectedComment = $(this).parents("div[data-cid='" + commentID + "']").eq(0);

            // returns an array
            var commentDetails = getCommentById(commentID);
            
            // comment div container
            //$theCommentContainer = $("div").find("[data-cid='" + commentID + "']").eq(0);
            $theCommentContainer = $(this).parents("div[data-cid='" + commentID + "']").eq(0);
            
            // get start time of comment
            var commentStartTime = $theCommentContainer.find(".temporalinfo").data('startval');
            
            // get end time of comment
            var commentEndTime = $theCommentContainer.find(".temporalinfo").data('endval');
            
            // get comment text
            //var commentText = $theCommentContainer.find(".comment-text span").text();
            var commentText = $theCommentContainer.find("div.comment-text").text();
            // or use commentDetails.text
            
            //console.log("comment start: " + commentStartTime + ", comment end: " + commentEndTime + ", comment Text: " + commentText);
            //console.log("comment info from js array-> hasVideo: " + commentDetails.hasVideo +", video filename: " + commentDetails.videoFileName)
            
            if (commentStartTime !== commentEndTime)
            {
            	$("span#toggle-time-span").parent('#new-comment-form').find("#new-comment-time-div").show();
	            $("span#toggle-time-span").hide();
	            
	            video_dom.pause();
	            video_dom.currentTime = commentStartTime;
	            playing = false;
	            creatingTimedComment = true;
	        	controls.playHeadImage = undefined;
	            // $(".comment-details").show();
	            controls.currentMinTimeSelected = commentStartTime;
	            controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
	            controls.currentMaxTimeSelected = commentEndTime;
	            controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
	          	controls.setAreaSelectionEnabled(true);
	            startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
	            startTimeInput.on("change",function(){
					if (startTimeInput.val() >= controls.currentMaxTimeSelected - controls.options.minLinkTime)
					{
						if (startTimeInput.val() >= controls.getDuration()-controls.options.minLinkTime)
						{
							controls.currentMaxTimeSelected = controls.getDuration();
							controls.currentMinTimeSelected = Number(controls.currentMaxTimeSelected) - controls.options.minLinkTime;
							controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
							controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
							endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
							startTimeInput.val(roundNumber(controls.currentMinTimeSelected, 2));
						}
						else
						{
							controls.currentMinTimeSelected = startTimeInput.val();
							controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
							controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected) + controls.options.minLinkTime;
							controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
							endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
					//		controls.currentMinTimeSelected = controls.currentMaxTimeSelected - controls.options.minLinkTime;
					//		controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
					//		startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
						}
					}
					else if (startTimeInput.val()<=0)
					{
						controls.currentMinTimeSelected = 0;
						controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
						startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
					}
					else
					{
						controls.currentMinTimeSelected = startTimeInput.val();
						controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
					}
					controls.setHighlightedRegion(controls.currentMinSelected, controls.currentMaxSelected);
					controls.setVideoTime(controls.currentMinTimeSelected);
	            });
	            endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
	            endTimeInput.on("change",function(){
					if (endTimeInput.val() <= Number(controls.currentMinTimeSelected) + controls.options.minLinkTime)
					{
						if (endTimeInput.val()<controls.options.minLinkTime)
						{
							controls.currentMinTimeSelected = 0;
							controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
							startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
							controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected) + controls.options.minLinkTime;
							controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
							endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
						}
						else
						{
							controls.currentMaxTimeSelected = endTimeInput.val();
							controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
							endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
							controls.currentMinTimeSelected = controls.currentMaxTimeSelected - controls.options.minLinkTime;
							controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
							startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
						}
					}
					else if (endTimeInput.val()>=controls.getDuration())
					{
						controls.currentMaxTimeSelected = controls.getDuration();
						controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
						endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
					}
					else
					{
						controls.currentMaxTimeSelected = endTimeInput.val();
						controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
					}
					controls.setHighlightedRegion(controls.currentMinSelected, controls.currentMaxSelected);
					controls.setVideoTime(controls.currentMaxTimeSelected);
	            });
            }
            
            console.log("comment id: " + commentID + ", starttime: " + commentStartTime + ", endtime: " + commentEndTime + ", commenttext: " + commentText);
            
            var $commdet = $commentformcontainer;
            
            // move new comment form 
        	$commdet.detach().appendTo($theCommentContainer).show();
            // set text on form submit button to reflect that we are editing a comment
            $commdet.find("input#new-comment-submit-button").attr("value", "Finished Editing");
            
            // set form action to be "edit"
            $commdet.find("#formAction").val('edit');
            $commdet.find("#start_time").val(commentStartTime);
            $commdet.find("#end_time").val(commentEndTime);
            $commdet.find("#comment-textarea").val(commentText);
            $commdet.find("#parentCommentID").attr("value", commentID);
            
            // TODO: change the video value of the form to reflect the current comment's video value if there is one
            if (commentDetails.hasVideo) {
                
                if (commentDetails.videoFileName.substring(0,5) === "video") {  // user selected an existing video
                    var videonameonly = commentDetails.videoFileName.substring(6); // should get rid of the 'video/' part of the string
                    console.log(videonameonly);
                    $commdet.find("#userExistingVideo").val(videonameonly); //set value in select input
                }
                else {
                    // hide the upload/record section because this comment already has a video
                    $commdet.find("#video-option-fieldset").hide();
                    
                    // show the existing comment's video filename and the modify link
                    $commdet.find("#video-name-fieldset div p.video-title").empty().html(commentDetails.videoFileName);
                    $commdet.find("#video-name-fieldset").show();
                    
                    // set the value of the hidden field in the form
                    $commdet.find("input#fileName").val(commentDetails.videoFileName);
                }
            }

        });
        
        //Clicking the clock icon will move the density bar to the comments time
        $(".clock-icon").click(function(){
            video_dom.currentTime = $(this).data('startval');
            //highlight comment temporarily on the density bar
            var commentContainer = $(this).parents(".feedback-container").eq(0);
            var comment = getCommentById(commentContainer.data("cid"));
            comment.paintHighlighted = true;
            controls.clearDensityBar();
			controls.drawSignLinks();
			controls.drawComments();

			//clear the highlighted comment after 3 seconds
           	setTimeout(function(){
				comment.paintHighlighted = undefined;
				controls.clearDensityBar();
				controls.drawSignLinks();
				controls.drawComments();
           	}, 3000);
        });

        // client-side form validation so you can't submit an empty comment form
        $("form#new-comment-form").submit(function() {
            // validate
            
            // if there is any content in the text area, any existing video chosen, or any video uploaded/recorded
            if ( !( (!$.trim($("#comment-textarea").val()))
                    &&  (!$(this).find("select#userExistingVideo").val())  
                    &&  (!$.trim($(this).find("p.video-title").html()))     ) ) {
                // form contains valid input, we should allow the submit
                return true;
            }
            
            alert("Please fill in at least one field to comment");
            $(this).stop().css("background-color", "#D80032").animate({ backgroundColor: "#FFFFFF"}, 1500);
            return false;
        })


        // highlight the 'record video' button when you hover over it, required for some reason
        $("div#input-upload-div").hover(
            function() {
                $(this).find("input#uploadedfileButton").addClass('awesomehighlight');
            },
            function() {
                $(this).find("input#uploadedfileButton").removeClass('awesomehighlight');
            }
        );


        //This will delete the specific comment when the user clicks the x icon
        // TODO: if you delete a top-level comment that has replies, those replies will no longer show up, fix this?
        $(".comment-delete-link").click(function(event){
            // prevent the click from scrolling around the page
            event.preventDefault();
            
            // get the comment's ID
            var commentID = stringToIntegersOnly($(this).attr('id'));
            
            // fade out the comment in question        
            $(this).parents("[data-cid='" + commentID + "']").eq(0).fadeTo('medium', 0.5);

            var $theDeleteLink = $(this);

            // show a dialog box asking for confirmation of delete
            $( "#dialog-confirm" ).dialog({
                resizeable: false,
                height: 275,
                modal: true,
                buttons: {
                    "Yes": function() {
                        $.ajax({
                            type: "POST",
                            url: "include/delete_comment.php",
                            data: "cID=" + commentID,
                            success: function(data){
                                var retdata = $.parseJSON(data);
                                if ( retdata.status === "success" ) {
                                    $theDeleteLink.parents("[data-cid='" + retdata.id + "']").eq(0).fadeTo('slow', 0.0).remove();
                                    //delete timeline region
                                    removeComment(commentID);
                                }
                            console.log(data);
                            return true; 
                             },
                            complete: function() {},
                            error: function(xhr, textStatus, errorThrown) {
                                console.log('ajax loading error...');
                                return false;
                            }
                        });
                        $( this ).dialog( "close" );
                    },
                    Cancel: function() {
                        $( this ).dialog( "close" );
                        $theDeleteLink.parents("[data-cid='" + commentID + "']").eq(0).fadeTo('slow', 1.0);
                    }
                }
            });
                       
        });


        $("form#submitReply").submit(function() {
            // we want to store the values from the form input box, then send via ajax below

            var vidNumber =<?php echo (isset($videoNumber)) ? ($videoNumber) : ("''"); ?>;
            var $reply_container = $(this).parent('.comment-container').children('.reply-container');
            var $form_input =  $(this).children('.text_reply');
            var reply = $form_input.attr('value');
            var parent_id = $(this).children('.text_reply').attr('parent_id');
            var file = $(this).children('[name=reply-file-name]').val();

            if(reply || file > 0) {
                $.ajax({
                    type: "POST",
                    url: "include/submit_comment.php",
                    data: "pID=" + parent_id + "&file-name=" + file + "&v=" + vidNumber + "&comment=" + reply,
                    dataType: "json",
                    success: function(data){
                        //$("[name=$postCommentButton]").value =
                        alert(data);
                        $form_input.val("");
                    }
                });
            }
            else{
                alert('Please fill out a reply and then submit');
                return false;
            }

        });



        // when you click on the arrow button to expand a comments content
        $(".arrow-container").click(function() {
//            var $video = $(this).parent(".comment-content-container").children(".comment-content").children(".comment-video");
            var $video = $(this).parent(".comment-content-container").find(".comment-video");
            var $commentContainer = $(this).parent(".comment-content-container");

            if ( $(this).children(".feedback-expand").attr("src") == "images/feedback_icons/arrow_down.png") {

//                $(this).parent(".comment-content-container").children(".comment-content").css({'height': 'auto'});
                $commentContainer.find(".comment-content").css({'height': 'auto'});

                //$(".comment-content").css({'height': 'auto'}); //make this specific by doing the parent child thing
                $(this).find(".feedback-expand").attr("src" , 'images/feedback_icons/arrow_up.png');

//                var $text = $(this).parent(".comment-content-container").children(".comment-content").children(".comment-text");
                var $text = $commentContainer.find(".comment-text");

                if ($video.length > 0) { //.children(".comment-video")   //.parent(".feedback-container").children(".comment-video").exists())
                    initializeCommentVideo($video);
                }

                if ($text.length > 0) {
                    $text.css({'display' : 'block'});
                }

            }
            else {
                $commentContainer.find(".comment-content").css({'height': '100px'});
                $(this).find(".feedback-expand").attr("src" , 'images/feedback_icons/arrow_down.png');

                collapseCommentVideo($video);
            }

        });
        
        // action to take when a user clicks on a 'Reply' link underneath a user comment
        $(".commentReplyLink").click( function(event) {
            // stop the click from scrolling us around the page
           event.preventDefault();
           
           var commentID   = $(this).data('cid');
           var commentType = $(this).data('ctype');
           
           // establish the container comment structure that the reply link is associated to
           // can be a top level 'comment' or a nested 'reply'
           //$theCommentContainer = $("div#"+commentType+"-"+commentID);
           //$theCommentContainer = $("div").find("[data-cid='" + commentID + "']").eq(0);
           
           $theCommentContainer = $(this).parents("div[data-cid='" + commentID + "']").eq(0);
           
           // set the 'global' selected comment variable
           $selectedComment = $theCommentContainer;
           
           console.log(commentID + ', type: ' + commentType + ", container element is: " + $theCommentContainer[0].id);
           
           // hide all the reply links
           hideCommentReplyLinks();
           
           // temporarily hide the comment tools links that include 'edit' and 'delete'
           hideCommentTools();
           
           // hide the Post a new comment button
           $postCommentButton.hide();
           
           // add a border to the container element to encompass the reply form
           //$theCommentContainer.addClass("writing-reply");
           
           // make a reference to the reply form div wrapper
           //var $commdetwrap = $("div.comment-details");
           var $commdetwrap = $commentformcontainer;
           
           //$commdetwrap.appendTo("#"+commentType+"-"+commentID).show(); 
           $commdetwrap.detach().appendTo($theCommentContainer).show();  // should move the element in the DOM
           
           $commdetwrap.find("form#new-comment-form").attr("action", "include/submit_comment.php?pID="+commentID+"aID=<?php echo $_SESSION['participantID'];?>");
           $commdetwrap.find("#parentCommentID").attr("value", commentID);
           $commdetwrap.find("#selectedCommentID").attr("value", commentID);
           $commdetwrap.find("#formAction").attr("value", "reply");
           $commdetwrap.find("#new-comment-submit-button").attr("value", "Post Reply");
           
        });

        $("span#toggle-time-span").click(function() {
            $(this).parent('#new-comment-form').find("#new-comment-time-div").show();
            $(this).hide();
            
            video_dom.pause();
            playing = false;
            creatingTimedComment = true;
        	controls.playHeadImage = undefined;
            // $(".comment-details").show();
            $commentformcontainer.show();
            if (Number(controls.getCurrentTime())+controls.options.minLinkTime>controls.getDuration())
            {
                controls.currentMinTimeSelected = controls.getDuration() - controls.options.minLinkTime;
            }
            else
            {
            	controls.currentMinTimeSelected = controls.getCurrentTime();
            }
            controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
            controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected)+controls.options.minLinkTime;
            controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
            
          	controls.setAreaSelectionEnabled(true);
            startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
            startTimeInput.on("change",function(){
				if (startTimeInput.val() >= controls.currentMaxTimeSelected - controls.options.minLinkTime)
				{
					if (startTimeInput.val() >= controls.getDuration()-controls.options.minLinkTime)
					{
						controls.currentMaxTimeSelected = controls.getDuration();
						controls.currentMinTimeSelected = controls.currentMaxTimeSelected - controls.options.minLinkTime;
						controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
						controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
						endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
						startTimeInput.val(roundNumber(controls.currentMinTimeSelected, 2));
					}
					else
					{
						controls.currentMinTimeSelected = startTimeInput.val();
						controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
						controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected) + controls.options.minLinkTime;
						controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
						endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
				//		controls.currentMinTimeSelected = controls.currentMaxTimeSelected - controls.options.minLinkTime;
				//		controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
				//		startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
					}
				}
				else if (startTimeInput.val()<=0)
				{
					controls.currentMinTimeSelected = 0;
					controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
					startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
				}
				else
				{
					controls.currentMinTimeSelected = startTimeInput.val();
					controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
				}
				controls.setHighlightedRegion(controls.currentMinSelected, controls.currentMaxSelected);
				controls.setVideoTime(controls.currentMinTimeSelected);
            });
            endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
            endTimeInput.on("change",function(){
				if (endTimeInput.val() <= Number(controls.currentMinTimeSelected) + controls.options.minLinkTime)
					{
						if (endTimeInput.val()<controls.options.minLinkTime)
						{
							controls.currentMinTimeSelected = 0;
							controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
							startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
							controls.currentMaxTimeSelected = Number(controls.currentMinTimeSelected) + controls.options.minLinkTime;
							controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
							endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
						}
						else
						{
							controls.currentMaxTimeSelected = endTimeInput.val();
							controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
							endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
							controls.currentMinTimeSelected = controls.currentMaxTimeSelected - controls.options.minLinkTime;
							controls.currentMinSelected = controls.getXForTime(controls.currentMinTimeSelected);
							startTimeInput.val( roundNumber(controls.currentMinTimeSelected, 2));
						}
					}
					else if (endTimeInput.val()>=controls.getDuration())
					{
						controls.currentMaxTimeSelected = controls.getDuration();
						controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
						endTimeInput.val( roundNumber(controls.currentMaxTimeSelected, 2));
					}
					else
					{
						controls.currentMaxTimeSelected = endTimeInput.val();
						controls.currentMaxSelected = controls.getXForTime(controls.currentMaxTimeSelected);
					}
					controls.setHighlightedRegion(controls.currentMinSelected, controls.currentMaxSelected);
					controls.setVideoTime(controls.currentMaxTimeSelected);
            });
            controls.repaint();
            
            
            //if ( (".comment-details").find("formAction").attr("value") == 'edit' ) {
            if ( $commentformcontainer.find("formAction").attr("value") == 'edit' ) {    
                // TODO: finish this
                // get comment start and end time
                // var $commID = $(".comment-details").parent(".feedback-container").data('cid');
                var $commID = $commentformcontainer.parent(".feedback-container").data('cid');
                
            }
            else {
          //      startTimeInput.val( roundNumber(video_dom.currentTime, 2));
           //     endTimeInput.val( roundNumber(video_dom.currentTime + 2, 2));
            }
        });


        // hide the new comment create form when user clicks 'cancel' on it
        $postCommentButton.click(function(){
            //$(".comment-details").show();
            $commentformcontainer.show();
            $(this).hide();
        });


        //comment video that exists in a comment
        $(".comment-video").click(function(){
            $(this).parent(".comment-content").css({'height': 'auto'});
            $(this).parent(".comment-content").parent(".comment-content-container").children(".arrow-container").children(".feedback-expand").attr("src" , 'images/feedback_icons/arrow_up.png');
            //$(this).parent(".comment-content").parent(".feedback-container").css({'height': 'auto'});
            initializeCommentVideo($(this));
        });

        $(".comment-text").click(function(){
            $(this).parent(".comment-content").parent(".feedback-container").css({'height': 'auto'});
        });


        //when the comment thumbs are clicked do something
        $(".class-comment-thumb").click(function() {
            //updateCurrentLinkFromAlt(this.alt);
            //changeMovieSource("uploads/comment/" + signlinkArray[currentLink] + ".mp4", linkVideoName[currentLink] + ".mp4"); //this.alt
        });

        //Change the video speed when the slowdown button is clicked
        $("#video-speed").click(function() {
            if(!speedSlow)
            {
                video_dom.playbackRate = 0.5;
                speedSlow = true;
                $("#video-speed").attr("src", 'images/slowdown-pressed.png');
            }
            else{
                video_dom.playbackRate = 1.0;
                speedSlow = false;
                $("#video-speed").attr("src", 'images/slowdown-normal.png');
            }
        });

        $("#closed-caption-button").click(function() {
            // alert(video_dom.tracks[0].mode);
            if(video_dom.tracks[0].mode == 2){
                $("#closed-caption-button").attr("src", 'images/closedCaptioning.jpg');
                video_dom.tracks[0].mode = CAPTION_HIDDEN;
            }
            else
            {
                $("#closed-caption-button").attr("src", 'images/closedCaptioningDown.jpg');
                video_dom.tracks[0].mode = CAPTION_SHOW;

            }

        });

        //toggles the display of the supplemental text of the source video
        $("#source-text-comment-button").click(function() {
            $(".source-text-container").toggle();
            if(textVisible)
            {
                $("#source-text-comment-button").attr("src", 'images/text-no-comment.png');
                textVisible = false;
            }
            else
            {
                $("#source-text-comment-button").attr("src", 'images/text-comment.png');
                textVisible = true;
            }
        });

<?php    
    // if we want all videos displayed, get all of them so the timeline temporal regions are drawn correctly
    if (isset($_GET['all']) && ($_GET['all']==1))
        $allcomms = getAllCommentsForSourceID($videoNumber, 0);
    else // only show the participant's created comments, and the ones from the admin
        $allcomms = getFilteredCommentsForSourceID($videoNumber, 1, 0, $_SESSION['participantID']);
    
    foreach($allcomms as $each_array) { 
		echo "var tempFullCommentObject = new fullcomment('".$each_array['id']."','"
			.$each_array['sourceid']."','"
			.$each_array['author']."','"
			.$each_array['parentid']."','"
			.html_entity_decode(preg_replace("/\r*\n/", "", $each_array['text']))."',"
			.$each_array['starttime'].","
			.$each_array['endtime'].",'"
			.$each_array['date']."',"
			.$each_array['isdeleted'].","
			.$each_array['istemporalcomment'].","
			.$each_array['hasvideo'].",'"
			.htmlentities($each_array['videofilename'], ENT_QUOTES)."','"
			.htmlentities($each_array['authorname'], ENT_QUOTES)."','"
			.$each_array['authorjoindate']."','"
			.$each_array['authorrole']."', get_random_color());\n";
        echo "fullCommentArray.push(tempFullCommentObject);\n";
    }



//-----------ADD SIGNLINKS TO DENSITY BAR ------------------------------------------//

// TODO: fix this sql statement to use a prepared statement, extremely unsafe as is
$sql = "Select * From video_signlink WHERE source_id = '" . $videoNumber . "' Order By start_time ASC";
$result = mysqli_query($db, $sql);

while ( $row = mysqli_fetch_assoc($result) ) {
    ?>

    var postObject = new signlink( <?php echo $row['start_time']; ?>, <?php echo $row['end_time']; ?>, <?php echo $row['signlink_id']; ?>);
    signlinkArray.push(postObject);

    <?php
}

mysqli_close($db);
?>

						controls.clearDensityBar();
						controls.setComments(fullCommentArray);
				//		controls.setSignLinks(signlinkArray);


                        function hideCommentReplyLinks() {
                            $("a.commentReplyLink").hide();
                        }
                        function showCommentReplyLinks() {
                            $("a.commentReplyLink").show();
                        }
                        function hideCommentTools() {
                            // $("a.comment-edit-link").hide();
                            // $("a.comment-delete-link").hide();
                            $("div.comment-tools-div").hide();
                        }
                        function showCommentTools() {
                            // $("a.comment-edit-link").hide();
                            // $("a.comment-delete-link").hide();
                            $("div.comment-tools-div").show();
                        }
                

                       function initializeCommentVideo($video) {

                           if(!($video.get(0).currentTime > 0 && $video.get(0).ended == false))
                           {
                               $video.attr('autoplay', 'true');
                               $video.load();
                           }

                           $video.css({
                               'height' : '240px',
                               'width' : '320px',
                               'left': '5%',
                               'top' : '0px'
                           });

                           $video.attr("controls", 'controls');
                           $video.get()[0].play();

                       }

                       function collapseCommentVideo($video, $source) {
                           $video.css({
                               'height' : '100px',
                               'width' : '120px',
                               'left': '35%',
                               'top' : '0px'
                           });


                           $video.attr('autoplay', 'false');
                           //$video.attr('src', '');
                           $video.removeAttr("controls");
                           $video.get()[0].pause();

                       }

						function removeComment(commentId)
						{
							for (var i=0; i< fullCommentArray.length; i++)
							{
								if (fullCommentArray[i].id == commentId)
								{
									fullCommentArray.splice(i,1);
									controls.clearDensityBar();
									controls.drawSignLinks();
									controls.drawComments();
									
									return;
								}
							}
						}

						function getCommentById(commentId)
						{
							for (var i=0; i< fullCommentArray.length; i++)
							{
								if (fullCommentArray[i].id == commentId)
								{
									return fullCommentArray[i]
								}
							}
						}

                       

                       //called when user uploads a video in the reply box
                       function createUploader($this){
                       	
                           var uploader = new qq.FileUploaderBasic({
                           	   multiple: false,
                           	   element:document.getElementById('uploadedfileButton'),
                               button: document.getElementById('input-upload-div'),
                               action: 'scripts/ajaxupload/upload.php?v=source',
                               debug: true,
                               onProgress: function(id, fileName, loaded, total) {
                               	 setBlur(true, "Uploading:" +Math.round(loaded/total*100)+"%");
                               },
                               onComplete: function(id, fileName1, responseJSON){
                                   // $("[name=file-name]").val(responseJSON.fileName);
                                   // $optionFieldset.hide();
                                   // $videoNameFieldset.show();
                                   // selectedVideoName = responseJSON.fileName;
                                   // $('.video-title').text(selectedVideoName);
									setBlur(false, "");
                                   
                                   
                                   //martin here!!!!!!!!!!!!!!!
                                   	popUpRecorder('videoRecordingOrPreview','preview',responseJSON.fileName, "upload");
                               }
                           });
                           

                           //loop for all reply upload spans
                           for(var i = 0; i < $('.reply-upload-span').length; i++)
                           {
                               var uploader = new qq.FileUploaderBasic({
                                   button: $('.reply-upload-span').get()[i], // document.getElementByClassName('reply-upload-span'),
                                   action: 'scripts/ajaxupload/upload.php?v=reply&id=' + i,
                                   debug: true,
                                   onComplete: function(id, fileName, responseJSON){
                                       var obj = jQuery.parseJSON(responseJSON);
                                       var $label = $(".reply-file-label");
                                       var $hiddenFile = $("[name=reply-file-name]");

                                       $label.get()[obj.id].innerHTML = fileName;
                                       $hiddenFile.get()[obj.id].value = fileName;
                                   }
                               });

                           }
                       }
                       
                   });
</script>
<div id="loadingIndicator" style="display:hidden"></div>
</body>
<div id="dialog-confirm" title="Delete this comment?">
    <p style="line-height:150%;"><span class="ui-icon ui-icon-alert" style="float:left;margin:0 30px 50px 0;"></span>This will delete this comment permanently. Are you sure?</p>
</div>
</html>
