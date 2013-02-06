<?php
require_once('setup.php');
require_once(INC_DIR . 'config.inc.php');
require_once(INC_DIR . 'header.php');
require_once(INC_DIR . 'functions.inc.php');


// grab the id from the url to match the video_source table in the database
$videoNumber = intval($_GET['v']);

$partIDsession    = intval($_SESSION['participantID']);
$partIDget        = intval($_GET['pID']);

?>
<link rel="stylesheet" href="<?php echo SITE_BASE; ?>css/menteecreate.css" type="text/css" media="screen" />

<div id="container">
    <div id="header">
        <img src="images/eku.jpg" title="Eastern Kentucky University" alt="Eastern Kentucky University" style="width:10%;padding-left:15%;" />
        <img src="images/imdclogosmall.png" title="Inclusive Media and Design Centre" alt="IMDC lab at Ryerson University" style="width:10%;clear:both;padding-left:15%;" />
        <br />
        <h1>Terptube Mentee Video Creation <?php echo implode(', ', array(SITE_BASE, $partIDsession, $_SESSION['supervisorName'], $_SESSION['role'])); ?> </h1>
    </div>
    <div id="navigation"></div>
    <?php echo checkError(); // add  ?>
    <div id="content-container" class="testfakeclass">

        <!-- This div will contain the form to add a new comment -->
            <div id="comment-form-wrap">

                <form id="new-comment-form" action="include/submit_comment.php?pID=0&action=create&aID=<?php echo $_SESSION['participantID'];?>" enctype="multipart/form-data" method="post">

                    <fieldset id="video-option-fieldset" name="video-option-fiedset">

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
                    <input type="hidden" name="partID" value="<?php echo $_SESSION['participantID'];?>" />
                    <input type="hidden" name="formAction" value="create" id="formAction" />

                    <br/>

                    <input type="button" id="previewButton" name="previewButton" style="display:none" value="Preview"/>

                    <label for="comment">Additional text information</label>
                    <textarea id="comment-textarea" name="comment" style="max-width:400px;"></textarea>

                    <input type="submit" id="new-comment-submit-button" value="Post Comment" />
                    <input type="button" id="cancel-button" name="cancel-button" value ="Cancel" />

                </form>
            </div>



        <div id="footer">
            <?php include('include/footer.php'); ?>
        </div>
    </div>
</div>

