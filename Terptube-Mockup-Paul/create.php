<?php
require_once('setup.php');
require_once(INC_DIR . 'config.inc.php');
require_once(INC_DIR . 'header.php');
require_once(INC_DIR . 'functions.inc.php')

?>
<style>
    .container {
        padding: 25px;
    }
    form {
        font-size: 16px;
        font-family: Tahoma, sans-serif;
        padding: 15px;
    }
    input {
        padding: 20px;
    }
    div {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 16px;
        padding: 10px;
    }
    h1,h3 {
        font-family: Georgia, serif;
        padding: 10px 0;
    }
    h1 {
        font-size: 16px;
    }
    h3 {
        font-size: 12px;
    }
    
</style>

<div class="container">
    <h1>You are participant: <?php echo $_SESSION['participantID'];?> under supervisor: <?php echo $_SESSION['supervisorName']; ?> </h1>
    <h1>You are acting as a: <?php echo $_SESSION['role']; ?> </h1>
    <h3>If these are blank then please go to the start page</h3>
    <form id="createVideoForm" action="processNewVideo.php" method="post" enctype="multipart/form-data">
        <fieldset id="video-name-fieldset" name="video-name-fieldset" style="margin-top:10px;display:none">
            Video:
            <a href="#" class="edit-video-link">Modify</a>
            <div>
                <p class="video-title" name="video-title"></p>
            </div>
        </fieldset>

        <input type="hidden" name="file-name" id="fileName" />
        <div id="input-record-div">
            Record Video:
            <input id="popUpRecordingWindowButton" type="button" value="Record Video" onclick="javascript:popUpRecorder('videoRecordingOrPreview','record',null)" />
        </div>
        <div id="videoRecordingOrPreview" style="display:hidden"></div>
        <input type="hidden" name="participantIDhidden" id="participantIDhidden" value="<?php echo $_SESSION['participantID']; ?>" />
        <input type="hidden" name="supervisor" value="<?php echo $_SESSION['supervisorName']; ?>" />
        <input type="submit" value="Finished" />
    </form>
</div>

<div id="loadingIndicator" style="display:hidden"></div>
<script>
    $(document).ready( function() {
        $("#createVideoForm").submit( function() {
           if ( $("#participantIDhidden").val() == '' ) {
               alert('No participant ID found, please go to the start page');
               return false;
           }
           else if ( $("#fileName").val() == '' ) {
               alert('No recorded video found, please try again');
               return false;
           }
        });
        
    });
</script>
</body>
</html>