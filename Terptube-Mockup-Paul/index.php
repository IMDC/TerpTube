<?php
require_once('setup.php');
require_once(INC_DIR . 'config.inc.php');
require_once(INC_DIR . 'header.php');
require_once(INC_DIR . 'functions.inc.php');

// grab the id from the url to match the video_source table in the database
$videoNumber = intval($_GET['v']);

// terribly unsafe
$sql = "SELECT * From video_source WHERE source_id = '$videoNumber'";
$result = mysqli_query($db, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $videoName = $row['title'];
    $sourceSuppText = html_entity_decode($row['comment']);
}

// redirect the user to the start page if they are not logged in
if (!isset($_SESSION['participantID'])) {
    header("Location: " . SITE_BASE . 'start.php?nologin=1');
}
?>

<div id="container">
    <div id="header">
        <img src="images/eku.jpg" title="Eastern Kentucky University" alt="Eastern Kentucky University" style="height:30px;;" />
        <img src="images/imdclogosmall.png" title="Inclusive Media and Design Centre" alt="IMDC lab at Ryerson University" style="height:30px;clear:both;" />
        <br />
        <h1>Terptube Interpretation Review/Discussion</h1>
        <h1><?php echo implode(', ', array(SITE_BASE, $_SESSION['participantID'], $_SESSION['supervisorName'], $_SESSION['role'])); ?></h1>
    </div>
    <div id="navigation"></div>
	<?php echo checkError(); ?>
    <div id="content-container" class="testfakeclass">

        <div id="content-left">
            <!-- Houses the main player with the canvas and sample videos -->
            <div class="source-media-container clearfix">
                <div style="float:left">
                    <div id="main-video-container" class="video-info-container">
                        <div class="source-video-controls">
                            <img class="clickable" id="video-speed" src="images/slowdown-normal.png" /><br/>
                 <!--            <img class="clickable" id="video-link-forward-button" src="images/link-forward.png" /><br/>
                            <img class="clickable" id="video-link-back-button" src="images/link-back.png" /><br/>
                         -->    <img class="clickable" id="source-text-comment-button" src="images/text-no-comment.png" /><br/>
<!--                             <img class="clickable" id="closed-caption-button" src="images/closedCaptioning.jpg" width="23" height="22" /><br/> -->
                        </div> <!-- source-video-controls -->

                        <video id="myPlayer" id="videoTest" width="640" preload="auto">
                            <source src="uploads/video/<?php echo $videoName; ?>" type="video/webm" />
                            <?php echo getCaptionFileForSourceVideo($videoNumber); ?>
                            Your browser does not support the video tag.
                        </video>

                    </div> <!-- video-info-container -->

                    
                    
                    <div class="cleardiv"></div>
                    <!------------ Source video description Box ------------------------>
                    <div class="source-text-container">
                        <span><?php print $sourceSuppText; ?></span>
                    </div>
                </div>
            </div> <!-- end source-media-container -->


            <div class="cleardiv"></div>
            
            <!---------------------------- Used to add a comment form --------------------------------------- -->
            <!-- Everytime this is clicked it will toggle the input form submission -->
            <div id="commentButtonWrap">
                <button id="postCommentButton" name="postCommentbutton"><img src="images/feedback_icons/round_plus.png" style="vertical-align:middle;margin-right:10px;height:20px;"/>Post a new comment</button>
            </div>

            <!-- This div will contain the form to add a new comment -->
            <div id="comment-form-wrap" style="display:none">

                <form id="new-comment-form" action="include/submit_comment.php?pID=0&aID=<?php echo $_SESSION['participantID'];?>" enctype="multipart/form-data" method="post">
                    <div id="form-toggle-time-span-wrap" class="form-span-full">
                        <span id="toggle-time-span"><img src="images/feedback_icons/clock.png" style="vertical-align:middle;width:25px;height:25px;" />Apply comment to portion of video</span>
                    </div>
                    <div id="new-comment-time-div" class="form-span-full" style="display:none;">
                            <label>Start Time:</label>
                            <input type="text" id="start_time" name="start_time" />

                            <label>End Time</label>
                            <input type="text" id="end_time" name="end_time" />
                    </div>
                    
                    <div id="form-video-options-wrap" class="form-span-full">
                        <fieldset id="video-option-fieldset" name="video-option-fieldset">
                            <legend style="padding-bottom:10px;">Choose a Video Upload Option:</legend>

                            <div class="video-upload-option-wrap" style="clear:left;">
                                <label>Existing Video:</label>
                                <?php $existingvideos = getExistingVideosForSourceID($videoNumber); ?>
                                <select style="margin-bottom:10px;" id="userExistingVideo" name="user-existing-video">
                                    <option value=""> </option>
                                    <?php 
                                    foreach ($existingvideos as $existingVid) {
                                        print '<option value="'.$existingVid.'">'.$existingVid. '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="video-upload-option-wrap">
                                <div id="input-upload-div">
                                    Upload Video: 
                                    <input id="uploadedfileButton"  type="button" style="width:90%;" value="Choose Video" >
                                    </input>
                                </div>
                            </div>

                            <div class="video-upload-option-wrap">
                                <div id="input-record-div">
                                    Record Video:
                                    <input id="popUpRecordingWindowButton" type="button" style="width:90%;" value="Record Video" onclick="javascript:popUpRecorder('videoRecordingOrPreview','record',null)" />

                                </div>
                            </div>
                            <div id="videoRecordingOrPreview" style="display:hidden">

                            </div>
                        </fieldset>
                    </div>

                    <div id="form-video-name-wrap">
                        <fieldset id="video-name-fieldset" name="video-name-fieldset" style="margin-top:10px;display:none">
                            Video:
                            <a href="#" class="edit-video-link">Modify</a>
                            <div>
                                <p class="video-title" name="video-title"></p>
                            </div>
                        </fieldset>
                    </div>
                    <div id="form-textarea-wrap" class="form-span-full">
                        <fieldset id="comment-textarea-fieldset" name="comment-textarea-fieldset">
                            <legend>Write a text comment</legend>
                            <textarea id="comment-textarea" name="comment" placeholder="Your text comment here"></textarea>
                        </fieldset>
                    </div>
                    

                    <input type="hidden" name="file-name" id="fileName" />
                    <input type="hidden" name="v" value="<?php echo $videoNumber; ?>" />
                    <input type="hidden" name="partID" value="<?php echo $_SESSION['participantID'];?>" />
                    <input type="hidden" name="formAction" value="new" id="formAction" />
                    <input type="hidden" name="parentID" value="" id="parentCommentID" />
                    <input type="hidden" name="commentID" value="" id="selectedCommentID" />
                    <br/>
                    <input type="button" id="previewButton" name="previewButton" style="display:none" value="Preview"/>
                    <input type="submit" id="new-comment-submit-button" value="Post Comment" />
                    <input type="button" id="cancel-button" name="cancel-button" value ="Cancel" />

                </form>
            </div>


        </div>  <!-- end of content-left div -->
        <div> <!-- test div added to encompass the content-right div and possibly enable comment scrolling -->
        <div id="content-right">
            <!-- Holds the comments -->
            <div class="comment-container" id="3" >

                <?php
                // check if we should only show comments made by the admin and current participant, or ALL the comments
                isset($_GET['all']) ? ($showAllComments=intval($_GET['all'])) : ($showAllComments=0);

                if ($showAllComments >= 1) {
                    //$toplevelcomments = getTopLevelCommentsForSourceID($videoNumber);
                    $toplevelcomments = getTopLevelCommentsForSourceID($videoNumber, 1, NULL);
                }
                else {
                    //This will pull top level non-deleted comments made by the admin (id=0) and the currently logged in participant
                    $toplevelcomments = getTopLevelCommentsForSourceID($videoNumber, 0, isset($_SESSION['participantID']) ? (intval($_SESSION['participantID'])) : NULL);    
                }
                // check if there are NO comments
                if (empty($toplevelcomments))
                  echo '<div class="feedback-container clearfix"><p>No comments yet</p></div>';
                else {
                  foreach ($toplevelcomments as $comment) {
                ?>

                    <div class="feedback-container clearfix" id="comment-<?php echo $comment["id"]; ?>" data-cid="<?php echo $comment["id"];?>" data-ctype="comment">
                        <div class="comment-left-side-wrap">
                            <div class="comment-avatar-div">
                                <img src="images/avatar/<?php echo $comment["authoravatarfilename"]; ?>" />
                                <p>Name: <?php echo $comment["authorname"]; ?></p>
                                <p>Joined: <?php echo date_format(new DateTime($comment["authorjoindate"]), 'M d, Y'); ?></p>
                            </div>
                            <?php 
                                //error_log("output from index.php printing comment tools: comment author: " .  $comment['author'] . " participantID: " . $_SESSION['participantID']);
                                if( isset($comment['author']) && isset($_SESSION['participantID']) && (intval($comment['author']) == intval($_SESSION['participantID'])) ) {
                                    echo printCommentTools($comment["id"], 'comment');
                                } 
                            ?>                         
                        </div>
                        <div class="clearfix"></div>

                        <div id="comment-content-container-<?php echo $comment["id"]; ?>" class="comment-content-container">
                            <div class="comment-date"><?php echo $comment['date']; ?></div>
                            <div class="comment-content">

                                <?php if ($comment["hasvideo"] === 1) { ?>
                                    <!--  <video class="comment-video" preload="auto" poster="uploads/comment/thumb/<?php //echo getVideoThumbnail($comment["id"],1); ?>" style="left:35%"> -->
                                    <!-- <video class="comment-video" preload="auto" poster="<?php //echo getVideoThumbnail($comment["id"], $comment["videofilename"], 0); ?>" style="left:35%"> -->
                                    <video class="comment-video" preload="auto" poster="<?php echo getCommentThumbnail($comment); ?>" style="left:35%">
                                        <!-- <source src="uploads/comment/<?php echo $comment["id"]; ?>.webm" type="video/webm" /> -->
                                        <!--   <source src="<?php echo VIDCOMMENT_DIR . $comment["id"]; ?>.webm" type="video/webm" />	-->
                                        <?php echo printCommentVideoSource($comment); ?>
<!--                                     	<source src="<?php echo 'uploads/comment/' . $comment["videofilename"]; ?>" type="video/webm" /> -->
                                        <?php //TODO: check after a comment is uploaded that it is converted to webm or mp4 ?? ?>
                                    </video>
                                <?php } ?>


                                <?php if ($comment["text"] != "") { ?>
                                    <div class="comment-text">
                                        <?php echo (preg_replace("/\r*\n/", "", $comment["text"])); ?>
                                    </div>
                                <?php } ?>
                            
                            </div>

                            <div class="arrow-container">
                                <img class="feedback-expand clickable" src="images/feedback_icons/arrow_down.png" />
                            </div>
                        </div>

                        <div class="feedback-properties">
                    		<?php if ($comment["istemporalcomment"] === 1) { ?>
                            	<img class="clock-icon clickable temporalinfo" src="images/feedback_icons/clock.png" alt="Jump to comment start time" data-startval="<?php echo $comment["starttime"];?>" data-endval="<?php echo $comment["endtime"];?>" /><br/>
                        	<?php } ?>
                        </div>
                        <div class="reply-wrap">
                            <a href="#" class="commentReplyLink" data-cid="<?php echo $comment['id'];?>" data-ctype="comment">Reply<img src="images/feedback_icons/reply-arrow.png" style="height:25px;vertical-align:middle;" alt="" /></a></p>
                        </div>
                    </div>

                    <?php
                    //This will pull replies to top level comments
                    
					// check if we should show all comment replies regardless of author, or only specific ones
                    if ( isset($_GET['all']) && (intval($_GET['all'])===1) ) {
                        $commentReplies = getCommentRepliesForSourceID($videoNumber, $comment["id"]);
                    }
                    else {
                        $commentReplies = getFilteredRepliesForSourceID($videoNumber, $comment["id"], 0, $_SESSION['participantID']);
                    }
                    
                    // iterate through the array of returned replies and display them
					foreach ($commentReplies as $reply) {	
                    ?>
                        
                    <div class="feedback-container reply-container clearfix" id="reply-<?php echo $reply["id"]; ?>" data-cid="<?php echo $reply["id"];?>" data-ctype="reply" >
                        <div class="comment-left-side-wrap">
                            <div class="comment-avatar-div">
                                <img src="images/avatar/<?php echo $reply["authoravatarfilename"]; ?>" />
                                <p>Name: <?php echo $reply["authorname"]; ?></p>
                                <p>Joined: <?php echo date_format(new DateTime($reply["authorjoindate"]), 'M d, Y'); ?></p>
                            </div>
                            <?php if(intval($reply['author'])==intval($_SESSION['participantID'])){echo printCommentTools($reply["id"], 'reply');} ?>                         
                        </div>
                        <div class="clearfix"></div>

                        <div id="comment-content-container-<?php echo $reply["id"]; ?>" class="comment-content-container reply-content-container">
                            <div class="comment-date"><?php echo $reply['date']; ?></div>
                            <div class="comment-content reply-content">
                            <?php 
                            if ($reply["hasvideo"] === 1) { ?>
                                <!-- <video class="comment-video" preload="auto" poster="<?php //echo getVideoThumbnail($reply["id"], $reply["videofilename"], 0); ?>" style="left:35%"> -->
                                <video class="comment-video" preload="auto" poster="<?php echo getCommentThumbnail($reply); ?>" style="left:35%">
                                    <?php echo printCommentVideoSource($reply); ?>
                                </video>
                            <?php 
                            } 
                            ?>
                            <?php 
                            if (!empty($reply["text"])) { ?>
                                <div class="comment-text">
                                    <?php echo (preg_replace("/\r*\n/", "",$reply["text"])); ?>
                                </div>
                            <?php 
                            } 
                            ?>
                            
                            </div>

                            <div class="arrow-container">
                                <img class="feedback-expand clickable" src="images/feedback_icons/arrow_down.png" />
                            </div>
                        </div>

                        <div class="feedback-properties">
                            <?php if ($reply["istemporalcomment"] === 1) { ?>
                                <img class="clock-icon clickable temporalinfo" src="images/feedback_icons/clock.png" alt="Jump to comment start time" data-startval="<?php echo $reply["starttime"];?>" data-endval="<?php echo $reply["endtime"]; ?>" />
                                <br/>
                            <?php } ?>
                        </div>
                        <!--
                        <div class="reply-wrap">
                            <p></p><a href="#" class="commentReplyLink" data-cid="<?php echo $reply['id'];?>" data-ctype="reply">Reply</a></p>
                        </div>
                        -->
                    </div>
                    
                    <?php 
                        
                    } // end foreach loop to process replies to the top level comments
                        
                } // end foreach loop to process top level comments 
            } // end else statement if there are no top level comments
            ?>

            </div> <!-- end of comment-container div -->
            
        </div> <!-- end of content-right div -->
        </div> <!-- end test div to encompass content-right div -->

        <div id="footer">
            <?php include('include/footer.php'); ?>
        </div>
    </div>
</div>
