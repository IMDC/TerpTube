<script>
    $(document).ready(function() {
        //TODO: make db table for source video

        //CONSTANTS
        var CAPTION_HIDDEN = 1;
        var CAPTION_SHOW = 2;


        //set up variables
        var speedSlow = false;
        var video_dom = $("video#myPlayer").get(0);

        //form inputs
        var startTimeInput     = $("#start_time");
        var endTimeInput       = $("#end_time");
        
        var $selectVideoDrop    = $("[name=select-video-names]");  // unused?
        var $optionFieldset     = $("#video-option-fiedset");
        var $videoNameFieldset  = $("#video-name-fieldset");
        
        var $postCommentButton  = $("#postCommentButton");
        var $cancelButton       = $("#cancel-button");
        var selectedVideoName;

        var signlinkArray = new Array();
        var commentArray  = new Array();
        var fullCommentArray = new Array();

        var tempStartTime;
        var tempEndtTime;
        var tempName;
        var tempComment;
        var tempLink;

        var plusSignVisible      = false;
        var creatingTimedComment = false;

        var regionPointer = 0; // used to keep track where the link index is for LinkTimes array
        var playing       = false;
        var textVisible   = false;
        
        var linkCanvas  = document.getElementById('linkCanvas');
        var ctx         = linkCanvas.getContext('2d');
        ctx.globalAlpha = 0.4;
        
        var traversalCanvas     = document.getElementById('traversalCanvas');
        var traversalctx        = traversalCanvas.getContext('2d');
        var traversalCanvasDrag = false;

        var myRectangle =  {x: -10,y: 17 - 1, width: 1, height: ctx.canvas.height, borderWidth: 1};
        var plusCircle  =  {x: 0,y: 8 , width: 7, height: Math.PI*2};

        var selectorRectLeft  = {x:0, y:17 - 1, width:1, height:ctx.canvas.height};
        var selectorRectRight = {x:0, y:17 - 1, width:1, height:ctx.canvas.height};
        var selectorInBetween = {x:0, y:17 - 1, width:1, height:ctx.canvas.height};

        var selectorRightDrag = false;
        var selectorLeftDrag  = false;
        //var selectorRectRight = {};

        var date = new Date();
        var time = date.getTime();

        //canvas images
        var plusImage = new Image();
        plusImage.src = "images/feedback_icons/round_plus.png";

        var selectorLeftImg = new Image();
        selectorLeftImg.src = "images/feedback_icons/selector_left.png";

        var selectorRightImg = new Image();
        selectorRightImg.src = "images/feedback_icons/selector_right.png";

        //current link is the video the user has selected or the play head is in contact with
        var currentLink = -1;
        var currentClickableIndex = -1;
        var sourceVideoClickable = false;

        window.onload = createUploader($(this));

        //jQuery('#mycarousel').jcarousel();

        // duration here functions as a 'global'
        var duration = 0;
        
        // event listener to set the correct duration when the video metadata has been loaded
        video_dom.addEventListener('loadedmetadata', function() {
            console.log(video_dom.duration);
            setSourceDuration(video_dom.duration);
            drawAreaOnBar(signlinkArray);
            drawAreaOnBar(commentArray);
        });
        
        // sets the global 'duration' variable to the argument passed in
        // also changes the html span element representing the video total time
        function setSourceDuration(theDur) {
            duration = theDur;
            // change the html duration value to reflect this
            $("span#video-total-time").html(formatVideoTime(duration))
        }
        //var duration = video_dom.duration;

        $selectVideoDrop.change(function() {
            $optionFieldset.hide();
            $videoNameFieldset.show();
            selectedVideoName = $selectVideoDrop.find('option:selected').text();
            $('.video-title').text(selectedVideoName);
            $("[name=file-name]").val(selectedVideoName);
        });

        $cancelButton.click( function() {
            $optionFieldset.show();
            $videoNameFieldset.hide();
            
            $(".comment-details").appendTo("#content-left").hide(); // move form to left side underneath 'post new comment' button
            $(".commentReplyLink").show(); // show all comment reply links if previously hidden
            drawAreaOnBar(signlinkArray);
            drawAreaOnBar(commentArray);
            
            creatingTimedComment = false;
            resetNewCommentFormValues();
            $postCommentButton.show();

        });
        
        function resetNewCommentFormValues() {
            var $commdet = $(".comment-details");
            // reset start and end time inputs
            $("#new-comment-time-div input").val('');
            $("#new-comment-time-div").hide();
            // clear text area
            $("#comment-textarea").attr("value", "");
            // reset existing video to blank choice
            $commdet.find("#userExistingVideo").val(0);
            // clear file input, reset form action to 'new' value
            $commdet.find("#fileName").val('').find("formAction").val('new');
            $commdet.find("input#new-comment-submit-button").attr("value", "Post Comment");
            $commdet.find("#parentCommentID").attr("value", "");
            $("#toggle-time-span").show();
        }

        // is this used for anything?!
        $(".edit-video-link").click(function(){
            $optionFieldset.show();
            $videoNameFieldset.hide();
        });

        // Editing a comment by clicking on it's 'edit' button
        $("a.comment-edit-link").click(function() {
            // $(".comment-details").show();
            $postCommentButton.hide();
            
            // get comment id
            var commentID = $(this).attr('id').replace("edit-", '');
            
            // comment div container
            var commentContainer = $("div#comment-"+commentID);
            
            // get start time of comment
            var commentStartTime = commentContainer.find(".temporalinfo").data('startval');
            
            // get end time of comment
            var commentEndTime = commentContainer.find(".temporalinfo").data('endval');
            
            // get comment text
            var commentText = commentContainer.find(".comment-text span").text();
            
            // TODO: activate canvas handles
            
            console.log("comment id: " + commentID + ", starttime: " + commentStartTime + ", endtime: " + commentEndTime + "commenttext: " + commentText);
            
            var $commdet = $("div.comment-details");
            
            // move new comment form 
            $commdet.appendTo("#comment-"+commentID).show(); // should move the element in the DOM
            $commdet.find("input#new-comment-submit-button").attr("value", "Finished Editing");
            
            // commentContainer.find(".edit-comment-wrap").show();
            // var editForm = commentContainer.find("#form-edit-comment-"+commentID);
            
            // set form action to be "edit"
            $commdet.find("#formAction").val('edit');
            $commdet.find("#start_time").val(commentStartTime);
            $commdet.find("#end_time").val(commentEndTime);
            $commdet.find("#comment-textarea").val(commentText);
            $commdet.find("#parentCommentID").attr("value", commentID);

        });
        
		// show seek bar plus sign for new comment on mouse hover
        $("#traversalCanvas").hover(
            function(){
                plusSignVisible = true;
            },
            function(){
                plusSignVisible = false;
            }
        );


        //Clicking the clock icon will move the density bar to the comments time
        $(".clock-icon").click(function(){
            video_dom.currentTime = $(this).data('startval');
        });

        //This will delete the specific comment when the user clicks the x icon
        $(".comment-delete-link").click(function(){
            // get the comment's ID
            var commentID = stringToIntegersOnly($(this).attr('id'));
            
            // fade out the comment in question
            $("div#comment-" + commentID).fadeTo('slow', 0.5);
//            alert(commentID);

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
                                    alert('awesome');
                                    $("div#comment-" + retdata.id).remove();
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
                        $("div#comment-" + commentID).fadeTo('slow', 1.0);
                    }
                }
            });
                       
        });


        $("form#submitReply").submit(function() {
            // we want to store the values from the form input box, then send via ajax below

            var vidNumber =<?php echo $videoNumber; ?>;
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
           
           var commentID = $(this).data('cid');
           var commentType = $(this).data('ctype');
           
           // establish the container comment structure that the reply link is associated to
           // can be a top level 'comment' or a nested 'reply'
           //$theCommentContainer = $("div#"+commentType+"-"+commentID);
           $theCommentContainer = $("div").find("[data-cid='" + commentID + "']");
           
           console.log(commentID + ', type: ' + commentType + ", container element is: " + $theCommentContainer[0].id);
           
           // hide the reply link 
           $(this).hide();
           
           // add a border to the container element to encompass the reply form
           $theCommentContainer.addClass("writing-reply");
           
           // make a reference to the reply form div wrapper
           var $commdetwrap = $("div.comment-details");
           
           //$commdetwrap.appendTo("#"+commentType+"-"+commentID).show(); 
           $commdetwrap.appendTo($theCommentContainer).show();  // should move the element in the DOM
           
           $commdetwrap.find("form#new-comment-form").attr("action", "include/submit_comment.php?pID="+commentID+"aID=<?php echo $_SESSION['participantID'];?>");
           $commdetwrap.find("#parentCommentID").attr("value", commentID);
           $commdetwrap.find("#formAction").attr("value", "reply");
           $commdetwrap.find("#new-comment-submit-button").attr("value", "Post Reply");
           
        });

        $("span#toggle-time-span").click(function() {
            $(this).parent('#new-comment-form').find("#new-comment-time-div").show();
            $(this).hide();
            
            video_dom.pause();
            video_dom.currentTime = Math.round((duration/2));
            playing = false;
            creatingTimedComment = true;
            clearCanvas(ctx);
            clearCanvas(traversalctx);
            //$(".comment-details").show();
            
            if ( (".comment-details").find("formAction").attr("value") == 'edit' ) {
                // TODO: finish this
                // get comment start and end time
                var $commID = $(".comment-details").parent(".feedback-container").data('cid');
                
            }
            else {
                startTimeInput.val( roundNumber(video_dom.currentTime, 2));
                endTimeInput.val( roundNumber(video_dom.currentTime + 2, 2));
            }
        })


        // hide the new comment create form when user clicks 'cancel' on it
        $postCommentButton.click(function(){
            $(".comment-details").show();
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

        //PlayButton Clicked
        $("#play-button").click(function() {
            if (playing){
                $("#play-button").attr("src", 'images/play_button.png');
                video_dom.pause();
                playing = false;
            }
            else {
                video_dom.play();
                playing = true;
                $("#play-button").attr("src", 'images/pause_button.png');
                animate(time, myRectangle);
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

        //To move the link forward or back
        $("#video-link-forward-button").click(function() {
            regionPointer = calculateColorRegionPosition();

            if(regionPointer == (signlinkArray.length - 1) && playHeadInColoredRegion() )
            {
                video_dom.currentTime = signlinkArray[0].startTime;
            }
            else{
                if(playHeadInColoredRegion()){
                    video_dom.currentTime = signlinkArray[regionPointer + 1].startTime;
                }
                else{
                    video_dom.currentTime = signlinkArray[regionPointer].startTime;
                }

            }
            movePlayHead();
            animate();
        });

        $("#video-link-back-button").click(function() {
            regionPointer = calculateColorRegionPosition();
            if(!(regionPointer == 0) ) // if its not at the first link still
            {
                video_dom.currentTime = signlinkArray[regionPointer - 1].startTime;
            }
            else{
                video_dom.currentTime = signlinkArray[signlinkArray.length - 1].startTime;
            }
            animate();
            movePlayHead();
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

        //code to run every second and run through the canvas
        video_dom.addEventListener('play', function() {
            
//            video_dom.width = canvas_draw.width = video_dom.offsetWidth;
//            video_dom.height = canvas_draw.height = video_dom.offsetHeight;
//            var ctx_draw = canvas_draw.getContext('2d');
            
            video_dom.width = video_dom.offsetWidth;
            video_dom.height = video_dom.offsetHeight;
            var ctx_draw = traversalCanvas.getContext('2d');
            
            playing=true;
            draw_interval = setInterval(function() {
            }, 1000)
        }, false);

        //SENSES WHEN VIDEO ENDS
        video_dom.addEventListener('ended', function() {
            $("#play-button").attr("src", 'images/play_button.png');
            playing = false;

        }, false);

        video_dom.addEventListener('pause', function() {
            interval = null;
            playing = false;
            //animProp.animate = false;

        }, false);

        traversalCanvas.addEventListener('mousedown', function(e) {
            var offset = $(this).offset();
            // var position = $('#traversalCanvas').position();
            var position = $(this).position();

            e.clientX = e.clientX - offset.left;
            //if they click on the plus sign
            var relX = e.clientX - offset.left;
            var relY = e.clientY - offset.top;
            console.log("e.clientX: " + e.clientX + " e.clientY: " + e.clientY + " relX: " + relX + " relY: " + relY);

            //if user clicks the "plus" icon above the playhead
            if (relX < plusCircle.x + plusCircle.width && relX > plusCircle.x - plusCircle.width && !creatingTimedComment
                && relY < plusImage.height && relY > 0 ){ //plusCircle.y - plusImage.height
                // startTimeInput.val(roundNumber(convertCanvasPointToTime(plusCircle.x,duration),2));
                // video_dom.pause();
                video_dom.pause();
                playing = false;
                creatingTimedComment = true;
                clearCanvas(ctx);
                clearCanvas(traversalctx);
                $(".comment-details").show();
                startTimeInput.val( roundNumber(video_dom.currentTime, 2));
                endTimeInput.val( roundNumber(video_dom.currentTime + 2, 2));
                $(":button[name=previewButton]").css("display","inline");


            }
            else if(creatingTimedComment) {

                //left comment triangle
                if (relX < selectorRectLeft.x  && relX > selectorRectLeft.x - selectorLeftImg.width
                    && relY > selectorRectLeft.y + selectorRectLeft.height  && relY < selectorRectLeft.y + selectorLeftImg.height + selectorRectLeft.height){
                    selectorLeftDrag = true;

                    traversalCanvas.addEventListener('mousemove', function(e) {

                        if(selectorLeftDrag && (e.clientX - offset.left < selectorRectRight.x - 3 )){
                            selectorRectLeft.x = e.clientX - offset.left;
                            video_dom.currentTime = convertCanvasPointToTime(selectorRectLeft.x, duration);
                            startTimeInput.val(roundNumber(video_dom.currentTime, 2));
                        }
                    }, true);
                }

                //right comment triangle
                if (relX > selectorRectRight.x  && relX < selectorRectRight.x + selectorRightImg.width
                    && relY > selectorRectRight.y + selectorRectRight.height  && relY < selectorRectRight.y + selectorRightImg.height + selectorRectRight.height){
                    selectorRightDrag = true;
                    traversalCanvas.addEventListener('mousemove', function(e) {

                        if(selectorRightDrag && (e.clientX - offset.left > selectorRectLeft.x + 1 )){
                            selectorRectRight.x = e.clientX - offset.left;
                            video_dom.currentTime = convertCanvasPointToTime(selectorRectRight.x, duration);
                            endTimeInput.val(roundNumber(video_dom.currentTime, 2));

                        }
                    }, true);
                }
            }
            else{
                var relativeX = (e.pageX - offset.left);
                percentage = relativeX / traversalCanvas.width;
                video_dom.currentTime = percentage * video_dom.duration;
                movePlayHead();
                animate();
                traversalCanvasDrag = true;
            }


        }, true);

        traversalCanvas.addEventListener('mouseup', function(e) {
            traversalCanvasDrag = false;
            selectorRightDrag = false;
            selectorLeftDrag = false;

        }, true);

        traversalCanvas.addEventListener('mousemove', function(e) {
            if(traversalCanvasDrag && !creatingTimedComment){
                var offset = $(this).offset();
                var relativeX = (e.pageX - offset.left);

                percentage = relativeX / traversalCanvas.width;
                video_dom.currentTime = percentage * video_dom.duration;
                movePlayHead();
                traversalCanvasDrag = true;
            }
        }, true);


        //to make the bar move
        function animate() {
            //This will light up the box red if there is a comment at that second
            //If current link is equal to -1 that means there is no comment at this time.
            //if it selects a comment at the time it takes currentLink the array id and will keep checking until it is off
            //and change it back to blue

            sourceVideoClickable = playHeadInColoredRegion();

            if(sourceVideoClickable && !creatingTimedComment)
            {
                $(".source-media-container").css("background", "red");
            }
            else{
                $(".source-media-container").css("background", "#dcd3e3");
                currentLink = -1;
            }

            //Change the position of playheads
            if(creatingTimedComment)
            {
                moveSliders();
            }
            else{
                movePlayHead();
            }


            // Loop animate
            requestAnimFrame(function(){
                animate();
            });
        }

        function formatVideoTime(seconds) {
            var m=Math.floor(seconds/60)<10?"0"+Math.floor(seconds/60):Math.floor(seconds/60);
            var s=Math.floor(seconds-(m*60))<10?"0"+Math.floor(seconds-(m*60)):Math.floor(seconds-(m*60));
            return m+":"+s;
        }


        function movePlayHead(){

            // calculate the percentage of current time in relation to the canvas size
            //call move Playhead to advance the play head
            var currentTime = video_dom.currentTime;
            //var duration = video_dom.duration; //edited to use global
            var percentage = currentTime/duration;
            var xPosition = percentage*traversalCanvas.width;

            // draw times on the bar
            $("span#video-current-time").html(formatVideoTime(currentTime));
            //$("span#video-total-time").html(formatVideoTime(duration));

            // clear
            traversalctx.clearRect(0, 0, traversalCanvas.width, traversalCanvas.height);

            // draw
            traversalctx.beginPath();
            traversalctx.rect(xPosition, myRectangle.y, myRectangle.width, myRectangle.height);

            plusCircle.x = xPosition;

            //draw plus circle
            if(plusSignVisible){
                //uncomment below line if you want to use arc instead of image
                //traversalctx.arc(xPosition, plusCircle.y, plusCircle.width, 0, plusCircle.height, true);
                traversalctx.drawImage(plusImage, xPosition - (plusImage.width /2), plusCircle.y - (plusImage.height /2));
            }

            traversalctx.fillStyle = "red";
            traversalctx.fill();
            traversalctx.lineWidth = myRectangle.borderWidth;
            traversalctx.strokeStyle = "black";
            traversalctx.stroke();

            selectorRectLeft.x = xPosition;
            selectorRectRight.x = xPosition + 4;
        }

        function moveSliders(){
            // calculate the percentage of current time in relation to the canvas size
            //call move Playhead to advance the play head
            // clear
            traversalctx.clearRect(0, 0, traversalCanvas.width, traversalCanvas.height);
            // draw
            traversalctx.beginPath();

            traversalctx.rect(selectorRectLeft.x, selectorRectLeft.y, selectorRectLeft.width, selectorRectLeft.height);
            traversalctx.rect(selectorRectRight.x, selectorRectRight.y, selectorRectLeft.width, selectorRectLeft.height);
            traversalctx.rect(selectorRectLeft.x, selectorInBetween.y, selectorRectRight.x - selectorRectLeft.x, selectorRectLeft.height);

            traversalctx.drawImage(selectorLeftImg, selectorRectLeft.x - selectorLeftImg.width, 42);
            traversalctx.drawImage(selectorRightImg, selectorRectRight.x, 42);

            traversalctx.fillStyle = "black";
            traversalctx.fill();
            traversalctx.lineWidth = myRectangle.borderWidth;
            traversalctx.strokeStyle = "black";
            traversalctx.stroke();
        }


        //this will analyze where the currentTime is and see what the back and forward
        //should point to
        function calculateColorRegionPosition() {
            for(var i = signlinkArray.length - 1; i > -1; i--){
                if(video_dom.currentTime >= signlinkArray[i].startTime && video_dom.currentTime <= signlinkArray[i].endTime){
                    //if the playhead is in a colored region
                    return i;
                }
                else if(video_dom.currentTime >= signlinkArray[i - 1].endTime && video_dom.currentTime <= signlinkArray[i].startTime){
                    //if the playhead is not in a region does not cover if its the first 1
                    return i;
                }
                else if(video_dom.currentTime <= signlinkArray[0].startTime){
                    return 0;
                }
            }
        }

        function playHeadInColoredRegion() {

            for(var i = signlinkArray.length - 1; i > -1; i--) {
                if(video_dom.currentTime >= signlinkArray[i].startTime && video_dom.currentTime <= signlinkArray[i].endTime){
                    //if the playhead is in a colored region
                    currentLink = i;
                    return true;
                }
            }
            return false;
        }

        window.requestAnimFrame = (function(callback){
            return window.requestAnimationFrame ||
                window.webkitRequestAnimationFrame ||
                window.mozRequestAnimationFrame ||
                window.oRequestAnimationFrame ||
                window.msRequestAnimationFrame ||
                function(callback){
                window.setTimeout(callback, 1000 / 60);
            };
        })();


        //when passed in a alt id it will spit out
        //needs to be changed
        function updateCurrentLinkFromAlt(altID) {
            for(var i = 0; i < signlinkArray.length; i++) {
                if(altID == signlinkArray[i].link) {
                    currentLink = i;
                    break;
                }
            }
        }

        //canvas code generated by database start and end times
        //Generate the colored region for the comments
//        duration = 33;
        // duration = $("video#myPlayer").get(0).duration;
        // duration

<?php
$sql = "Select * From video_comment WHERE source_id = $videoNumber Order By comment_start_time ASC";
$result = mysqli_query($db, $sql);

while ($row = mysqli_fetch_assoc($result)) {

    //if there is no video associated to the comment it means it is purely a text comment
    if (file_exists('uploads/comment/' . $row['comment_id'] . '.webm')) {
        ?>
                tempStartTime =  <?php echo $row['comment_start_time']; ?>;
                tempEndTime = <?php echo $row['comment_end_time']; ?>;
    <?php } else {
        ?>
                tempStartTime = -1;
                tempEndTime = -1;
    <?php } ?>

                tempName = "<?php echo $row['comment_id']; ?>";
                tempComment = "<?php echo htmlentities($row['text_comments']); ?>";


                var postObject = new comment(tempStartTime, tempEndTime, tempName, tempComment);
                commentArray.push(postObject);

        
    <?php
}

?>
    function fullcomment(commID, source_id, authID, parentID, textcont, start, 
                            end, commdate, deleted, tempcommentbool, 
                            hasvideobool, videofilename) {
            this.id = commID; 
            this.sourceid = source_id;
            this.author = authID;
            this.parentid = parentID;
            this.text = textcont;
            this.starttime = start;
            this.endtime = end;
            this.date = commdate;
            this.isdeleted = deleted;
            this.istemporalcomment = tempcommentbool;
            this.hasvideo = hasvideobool;
            this.videofilename = videofilename;
		
    }
    
<?php
    $allcomms = json_decode(getAllCommentsForSourceID($videoNumber, 1),true);
    foreach($allcomms as $each_array) {
        $output = array();
        foreach ($each_array as $key=>$val) {
            //echo "$key:$val";
            //echo "$key: $val";
            array_push($output, '"'.$val.'"');
        }
        $fullcommentargs = implode($output, ',');
        echo "var tempFullCommentObject = new fullcomment($fullcommentargs);\n";
        echo "fullCommentArray.push(tempFullCommentObject);\n";
    }
?>
    //var tempFullCommentObject = new fullcomment(cid,sid,aid,pid,text,start,end,date,del,tempbool,hasvid,vidfilename);
    //fullCommentArray.push(tempFullCommentObject);
        
        <?php


$commentsAsText = convertJSONCommentArrayToHTML($allcomms);

echo '$("div#fullcommarraydiv").empty().html(' . $commentsAsText . ');';

//-----------ADD SIGNLINKS TO DENSITY BAR ------------------------------------------//

// TODO: fix this sql statement to use a prepared statement, extremely unsafe as is
$sql = "Select * From video_signlink WHERE source_id = '$videoNumber' Order By start_time ASC";
$result = mysqli_query($db, $sql);

while ( $row = mysqli_fetch_assoc($result) ) {
    ?>

    var postObject = new signlink( <?php echo $row['start_time']; ?>, <?php echo $row['end_time']; ?>, <?php echo $row['signlink_id']; ?>);
    signlinkArray.push(postObject);

    <?php
}

mysqli_close($db);
?>

                       drawAreaOnBar(commentArray);
                       drawAreaOnBar(signlinkArray);


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



                       function signlink(startTime,endTime,link) {
                           this.startTime=startTime;
                           this.endTime=endTime;
                           this.link = link;
                       }

                       function comment(start,end,videoName,comment) {
                           this.startTime=start;
                           this.endTime=end;
                           this.name = videoName;
                           this.comment = comment;
                       }

                       function drawAreaOnBar(object) {
                           for(var i = 0; i < object.length; i++) {

                               if(object[i].name > 0 || object[i].comment > 0) {
                                   //canvas color get random colors for each comment
                                   ctx.fillStyle = get_random_color();
                               }
                               else {
                                   //canvas color get random colors for each comment
                                   ctx.fillStyle = "rgb(0,0,0)";
                               }
                               //calulate start of canvas comment
                               var videoPercentageStart = object[i].startTime / duration;
                               var canvasPercentageStart = videoPercentageStart*ctx.canvas.width;

                               //calculate end of canvas comment
                               var videoPercentageEnd = object[i].endTime / duration;
                               var canvasPercentageEnd = videoPercentageEnd*ctx.canvas.width;

                               var width = Math.floor(canvasPercentageEnd) - Math.floor(canvasPercentageStart);

                               if(width == 0)
                               {
                                   width = 1;
                               }

                               //rect x,y,width,height
                               ctx.fillRect(canvasPercentageStart ,0, width, ctx.canvas.height);
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
