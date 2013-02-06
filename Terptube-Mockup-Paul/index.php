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
}
?>

<div id="container">
    <div id="header">
        <h3>Terptube Interpretation Review/Discussion <?php echo implode(', ', array(SITE_BASE, $_SESSION['participantID'], $_SESSION['supervisorName'], $_SESSION['role'])); ?> </h3>
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
                        <span>Hello welcome to SignlinkStudio.com. I will be using the acronym SLS. SignlinkStudio.com explains the concept of signlinking and how it works. There are different areas you can visit in this website. If you don't understand signlinking, you can select "Getting Started" section. If you do understand you can select other section of this website. This section is "About SignlinkStudio" Explain the process involved and how signlinking works. Under "Software" there are two software available that you can download to your computer. The cost is free, no cost to you. Also have online support for how to develop signlinks, using the software, filming and other information. Here is the various research papers we have submitted to conferences around the world. If you have a question related to signlinking, the software or the website, please contact us. Enjoy your visit! </span>
                    </div>
                </div>
            </div> <!-- end source-media-container -->


            <div class="cleardiv"></div>
            <div id="fullcommarraydiv">There should be stuff in here</div>
            
            <!---------------------------- Used to add a comment form --------------------------------------- -->
            <!-- Everytime this is clicked it will toggle the input form submission -->
            <div id="commentButtonWrap">
                <button id="postCommentButton" name="postCommentbutton">Post a new comment</button>
            </div>

            <!-- This div will contain the form to add a new comment -->
            <div id="comment-form-wrap" style="display:none">

                <form id="new-comment-form" action="include/submit_comment.php?pID=0&aID=<?php echo $_SESSION['participantID'];?>" enctype="multipart/form-data" method="post">
                    <span id="toggle-time-span">Apply comment to portion of video</span>
                    <div id="new-comment-time-div" style="display:none;">
                        <label>Start Time:</label>
                        <input type="text" id="start_time" name="start_time" />
    
                        <label>End Time</label>
                        <input type="text" id="end_time" name="end_time" />
    
                        <label>Make a comment</label><br />
                    </div>
                    <textarea id="comment-textarea" name="comment"></textarea>

                    <fieldset id="video-option-fieldset" name="video-option-fiedset">
                        <legend>Choose a Video Upload Option:</legend>

                        <label>Existing Video:</label>
                        <?php $existingvideos = getExistingVideosForSourceID($videoNumber); ?>
                        <select id="userExistingVideo" name="user-existing-video">
                        	<option value=""> </option>
                        	<?php 
                        	foreach ($existingvideos as $existingVid) {
                        		print '<option value="'.$existingVid.'">'.$existingVid. '</option>';
							}
                        	?>
                        </select>


	                    <div id="input-upload-div">
	                    	Upload Video: 
	                    	<input id="uploadedfileButton"  type="button" value="Choose Video" >
	                    	</input>
	                    </div>

                        <div id="input-record-div">
                        	Record Video:
                        	<input id="popUpRecordingWindowButton" type="button" value="Record Video" onclick="javascript:popUpRecorder('videoRecordingOrPreview','record',null)" />
                        	
						</div>
						<div id="videoRecordingOrPreview" style="display:hidden">
                        		
                        </div>
                    </fieldset>


                    <fieldset id="video-name-fieldset" name="video-name-fieldset" style="margin-top:10px;display:none">
                        Video:
                        <a href="#" class="edit-video-link">Modify</a>
                        <div>
                            <p class="video-title" name="video-title"></p>
                        </div>
                    </fieldset>

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
        <div id="content-right">
            <!-- Holds the comments -->
            <div class="comment-container" id="3" >

                <?php
                //This will pull top level comments
                             
				$toplevelcomments = getTopLevelCommentsForSourceID($videoNumber);
				foreach ($toplevelcomments as $comment) {
	
                    ?>

                    <div class="feedback-container clearfix" id="comment-<?php echo $comment["id"]; ?>" data-cid="<?php echo $comment["id"];?>" data-ctype="comment">
                        <div class="comment-left-side-wrap">
                            <div class="comment-avatar-div">
                                <img src="images/avatar/<?php echo $comment["authoravatarfilename"]; ?>" />
                                <p>Name: <?php echo $comment["authorname"]; ?></p>
                                <p>Joined: <?php echo $comment["authorjoindate"]; ?></p>
                            </div>
                            <?php 
                                error_log("output from index.php printing comment tools: comment author: " .  $comment['author'] . " participantID: " . $_SESSION['participantID']);
                                if( isset($comment['author']) && isset($_SESSION['participantID']) && (intval($comment['author']) == intval($_SESSION['participantID'])) ) {
                                    echo printCommentTools($comment["id"]);
                                } 
                            ?>                         
                        </div>
                        <div class="clearfix"></div>

                        <div id="comment-content-container-<?php echo $comment["id"]; ?>" class="comment-content-container">
                            <div class="comment-date"><?php echo $comment['date']; ?></div>
                            <div class="comment-content">

                                <?php if ($comment["hasvideo"] === 1) { ?>
                                    <!--  <video class="comment-video" preload="auto" poster="uploads/comment/thumb/<?php echo getVideoThumbnail($comment["id"],1); ?>" style="left:35%"> -->
                                    <video class="comment-video" preload="auto" poster="<?php echo getVideoThumbnail($comment["id"], $comment["videofilename"], 0); ?>" style="left:35%">
                                        <!-- <source src="uploads/comment/<?php echo $comment["id"]; ?>.webm" type="video/webm" /> -->
                                        <!--   <source src="<?php echo VIDCOMMENT_DIR . $comment["id"]; ?>.webm" type="video/webm" />	-->
                                        <?php echo printCommentVideoSource($comment); ?>
<!--                                     	<source src="<?php echo 'uploads/comment/' . $comment["videofilename"]; ?>" type="video/webm" /> -->
                                        <?php //TODO: check after a comment is uploaded that it is converted to webm or mp4 ?? ?>
                                    </video>
                                <?php } ?>


                                <?php if ($comment["text"] != "") { ?>
                                    <div class="comment-text">
                                        <span><?php echo html_entity_decode($comment["text"]); ?></span>
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
                            <p></p><a href="#" class="commentReplyLink" data-cid="<?php echo $comment['id'];?>" data-ctype="comment">Reply</a></p>
                        </div>
                    </div>

                    <?php
                    //This will pull any replies to a top level comment
					$commentReplies = getCommentRepliesForSourceID($videoNumber, $comment["id"]);
					foreach ($commentReplies as $reply) {	
                    ?>
                        
                    <div class="feedback-container reply-container clearfix" id="reply-<?php echo $reply["id"]; ?>" data-cid="<?php echo $reply["id"];?>" data-ctype="reply" >
                        <div class="comment-left-side-wrap">
                            <div class="comment-avatar-div">
                                <img src="images/avatar/<?php echo $reply["authoravatarfilename"]; ?>" />
                                <p>Name: <?php echo $reply["authorname"]; ?></p>
                                <p>Joined: <?php echo date_format(new DateTime($reply["authorjoindate"]), 'M d,Y'); ?></p>
                            </div>
                            <?php if(intval($reply['author'])==intval($_SESSION['participantID'])){echo printCommentTools($reply["id"]);} ?>                         
                        </div>
                        <div class="clearfix"></div>

                        <div id="comment-content-container-<?php echo $reply["id"]; ?>" class="comment-content-container reply-content-container">
                            <div class="comment-date"><?php echo $reply['date']; ?></div>
                            <div class="comment-content reply-content">
                            <?php 
                            if ($reply["hasvideo"] === 1) { ?>
                                <video class="comment-video" preload="auto" poster="<?php echo getVideoThumbnail($reply["id"], $comment["videofilename"], 0); ?>" style="left:35%">
                                    <?php echo printCommentVideoSource($reply); ?>
                                    <?php //TODO: check after a comment is uploaded that it is converted to webm or mp4 ?? ?>
                                </video>
                            <?php 
                            } 
                            ?>
                            <?php 
                            if (!empty($comment["text"])) { ?>
                                <div class="comment-text">
                                    <span><?php echo html_entity_decode($reply["text"]); ?></span>
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
            ?>

            </div>
            
        </div>

        <div id="footer">
            <?php include('include/footer.php'); ?>
        </div>
    </div>
</div>