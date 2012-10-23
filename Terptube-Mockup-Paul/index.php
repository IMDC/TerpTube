<?php
require_once('setup.php');
require_once(INC_DIR . 'config.inc.php');
require_once(INC_DIR . 'header.php');

$videoNumber = intval($_GET['v']);

$sql = "SELECT * From video_source WHERE source_id = '$videoNumber'";
$result = mysqli_query($db, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $videoName = $row['title'];
}
?>

<div id="container">
    <div id="header">
        <h3>Terptube Interpretation Review/Discussion <?php echo SITE_BASE; ?> </h3>
    </div>
    <div id="navigation"></div>

    <div id="content-container">

        <div id="content-left">
            <!-- Houses the main player with the canvas and sample videos -->
            <div class="source-media-container clearfix">
                <div style="float:left">
                    <div class="video-info-container">
                        <div class="source-video-controls">
                            <img class="clickable" id="video-speed" src="images/slowdown-normal.png" /><br/>
                            <img class="clickable" id="video-link-forward-button" src="images/link-forward.png" /><br/>
                            <img class="clickable" id="video-link-back-button" src="images/link-back.png" /><br/>
                            <img class="clickable" id="source-text-comment-button" src="images/text-no-comment.png" /><br/>
                            <img class="clickable" id="closed-caption-button" src="images/closedCaptioning.jpg" width="23" height="22" /><br/>
                        </div> <!-- source-video-controls -->

                        <video id="myPlayer" id="videoTest" width="640" preload="auto">
                            <source src="uploads/video/<?php echo $videoName; ?>" type="video/mp4" />
                            <track id="enTrack" kind="captions" src="uploads/caption/upc.vtt" type="text/vtt" srclang="en" label="English Subtitles" default />
                            <!--  <source src="movie.ogg" type="video/ogg" /> -->
                            Your browser does not support the video tag.
                        </video>

                    </div> <!-- video-info-container -->


                    <div class="play-canvas" style="position: relative; height:60px;"> <!--  top:-21px" -->
                        <!--  link canvas is the comments -->
                        <canvas id="linkCanvas" width="640" height="25"
                                style="margin:0;padding:0;position: absolute; left: 0; bottom: 30%; z-index: 0;" >
                        </canvas>

                        <!-- traversal canvas is the playhead -->
                        <canvas id="traversalCanvas" width="640" height="60px"
                                style="margin:0;padding:0;position: absolute; left: 0; top: 0; z-index: 1;">
                        </canvas>
                    </div>
                    <div id="video-playback-buttons-container">
                        <img class="clickable" id="play-button" src="images/play_button.png" />
                        <span id="video-current-time">00:00</span>/
                        <span id="video-total-time">00:00</span>
                    </div>

                    <div class="cleardiv"></div>
                    <!------------ Source video description Box ------------------------>
                    <div class="source-text-container">
                        <span>Hello welcome to SignlinkStudio.com. I will be using the acronym SLS. SignlinkStudio.com explains the concept of signlinking and how it works. There are different areas you can visit in this website. If you don't understand signlinking, you can select "Getting Started" section. If you do understand you can select other section of this website. This section is "About SignlinkStudio" Explain the process involved and how signlinking works. Under "Software" there are two software available that you can download to your computer. The cost is free, no cost to you. Also have online support for how to develop signlinks, using the software, filming and other information. Here is the various research papers we have submitted to conferences around the world. If you have a question related to signlinking, the software or the website, please contact us. Enjoy your visit! </span>
                    </div>
                </div>
            </div> <!-- end source-media-container -->




            <!---------------------------- Used to add a comment form --------------------------------------- -->
            <!-- Everytime this is clicked it will toggle the input form submission -->
            <div id="commentButtonWrap">
                <button name="postCommentbutton">Post a new comment</button>
            </div>

            <!-- This div will contain the form to add a new comment -->
            <div class="comment-details" style="display:none">

                <form id="new-comment-form" action="include/submit_comment.php?pID=0" enctype="multipart/form-data" method="post">
                    <label>Start Time:</label>
                    <input type="text" name="start_time" />

                    <label>End Time</label>
                    <input type="text" name="end_time" />

                    <label>Make a comment</label><br />
                    <textarea name="comment"></textarea>

                    <fieldset name="video-option-fiedset">
                        <legend>Choose a Video Upload Option:</legend>

                        <label>Existing Video:</label>
                        <select name="select-video-names">
                            <option value="video-1">Video 1</option>
                            <option value="video-2">Video 2</option>
                            <option value="video-3">Video 3</option>
                        </select>


                        <p id="input-upload-span"> Upload Video: <input id="uploadedfileButton"  type="button" value="Choose Video" /> </p>

                        Record Video:

                    </fieldset>


                    <fieldset name="video-name-fieldset" style="margin-top:10px;display:none">
                        Video:
                        <a href="#" class="edit-video-link">Modify</a>
                        <div>
                            <p class="video-title" name="video-title"></p>
                        </div>
                    </fieldset>

                    <input type="hidden" name="file-name" />

                    <input type="hidden" name="v" value="<?php echo $videoNumber; ?>" />

                    <br/>

                    <input type="button" name="previewButton" style="display:none" value="Preview"/>

                    <input type="submit" value="Post Comment" />
                    <input type="button" name="cancel-button" value ="Cancel" />

                </form>
            </div>


        </div>
        <div id="content-right">
            <!-- Holds the comments -->
            <div class="comment-container" id="3" >

                <?php
                //This will pull non generic comments
                $sql = "Select * From video_comment WHERE source_id = $videoNumber AND comment_start_time != 0 AND comment_end_time != 0 AND parent_id = 0 ORDER BY date DESC";
                $result = mysqli_query($db, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    $videoExists = file_exists('uploads/comment/' . $row['comment_id'] . '.mp4');
                    ?>

                    <div class="feedback-container clearfix">

                        <img class="delete-comment" src="images/feedback_icons/x_icon.png" alt=""/>

                        <div class="comment-avatar-div">
                            <img src="images/avatar/avatar1.png" />
                            <p>Name: Joe</p>
                            <p>Date Created: June</p>
                        </div>

                        <div class="comment-content-container">
                            <div class="comment-content">

                                <?php if ($videoExists) { ?>
                                    <video  class="comment-video" preload="auto" poster="uploads/comment/thumb/<?php echo $row['comment_id']; ?>" style="left:35%">
                                        <source src="<?php echo 'uploads/comment/' . $row['comment_id'] . '.mp4'; ?>" type="video/mp4" />
                                    </video>
                                <?php } ?>


                                <?php if ($row[text_comments] != "") { ?>
                                    <div class="comment-text" <?php if ($videoExists) { ?> style="display:none" <?php } ?> >
                                        <span><?php echo $row[text_comments]; ?></span>
                                    </div>
                                <?php } ?>

                                <div class="reply-container">
                                    <?php
                                    //This will pull replies to comments
                                    $sql_reply = "Select * From video_comment WHERE parent_id = '$row[comment_id]' AND source_id = '$videoNumber' ORDER BY date ASC";
                                    $res = mysqli_query($db, $sql_reply);


                                    //this is the container for the reply to a reply
                                    while ($list = mysqli_fetch_assoc($res)) {
                                        ?>
                                        <div class="reply-content" id="<?php echo $list['comment_id'] ?>" >

                                            <img src="images/avatar/avatar2.png" style="width:25px;height:25px;"  />

                                            <?php if (file_exists('uploads/comment/' . $list['comment_id'] . '.mp4')) {
                                                ?>

                                                <video class="comment-video" width="240" height="180"  preload="auto" controls="controls" poster="uploads/comment/thumb/<?php echo $list['comment_id'] ?>.jpg">
                                                    <source src="uploads/comment/<?php echo $list['comment_id'] . '.mp4'; ?>" type="video/mp4" />
                                                </video>

                                            <?php } ?>

                                            <div class="comment-text" >
                                                <span><?php echo $list['text_comments']; ?></span>
                                            </div>
                                        </div>


                                    <?php }
                                    ?>
                                </div>


                                <fieldset name="reply-fieldset" >
                                    <legend>Reply:</legend>

                                    <form id="submitReply" enctype="multipart/form-data" method="post">

                                        <img alt="Record Video" src="images/feedback_icons/clock.png" style="position:relative;left:0px;height:25px;width:25px;float:left"  />
                                        <p class="reply-upload-span"><img alt="Upload Video" src="images/feedback_icons/upload.png" style="position:relative;left:0px;height:25px;width:25px;float:left"  /></p>

                                        <label class="reply-file-label">Comment:</label>
                                        <input class="text_reply" name="text_reply" type="text" parent_id="<?php echo $row['comment_id']; ?>" />

                                        <input type="submit" style="float:right" src="images/feedback_icons/reply-arrow.png" value="Submit">

                                        <input type="hidden" name="reply-file-name" />
                                    </form>


                                </fieldset>

                            </div>

                            <div class="arrow-container">
                                <img class="feedback-expand clickable" src="images/feedback_icons/arrow_down.png" />
                            </div>
                        </div>

                        <div class="feedback-properties">
                            <img class="clock-icon clickable" src="images/feedback_icons/clock.png" alt="<?php echo $row[comment_start_time]; ?>"/><br/>

                        </div>

<!--                        <div class="cleardiv"></div>-->
                    </div>

                <?php } ?>




            </div>
        </div>

        <div id="footer">
            <?php include('include/footer.php'); ?>
        </div>
    </div>

</div>