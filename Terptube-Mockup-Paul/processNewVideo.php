<?php

    require_once('setup.php');
    require_once(INC_DIR . 'config.inc.php');
    require_once(INC_DIR . 'header.php');
    require_once(INC_DIR . 'functions.inc.php');

    global $db;
    
    $uploadedFileName = mysqli_real_escape_string($db, $_POST['file-name']);
    $userid = intval($_POST['participantIDhidden']);
    $usersessionid = intval($_SESSION['participantID']);
    $supername = mysqli_real_escape_string($db, $_POST['supervisor']);
    
    if ($userid != $usersessionid) {
        echo "session doesn't match GET param";
        return;
    }
    
    /* create a prepared statement */
    $query = "INSERT INTO video_source (source_id, participant_id, title, duration, comment, created) values ('DEFAULT', ?, ?, '50000', ?, DEFAULT)";
    $stmt = mysqli_stmt_init($db);
    if ( !mysqli_stmt_prepare($stmt, $query)) {
        error_log("Failed to prepare statement in 'processNewVideo.php'");
        print "Failed to prepare statement";
        return;
    }
    else {
        /* bind parameters */
        $superstring = "supervisor: " . $supername;
        mysqli_stmt_bind_param($stmt, "iss", $userid, $uploadedFileName, $superstring);

        /* execute query */
        if (!mysqli_stmt_execute($stmt)) {
            $errnum = mysqli_stmt_errno($stmt);
            $errmsg = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            error_log("ERROR: $errnum, $errmsg");
            echo $errmsg;
            
            echo "<h1>An error has occured, please go back and try again</h1>";
            echo "<a href='create.php'>Go back</a>";
            return;
        }
        // all is fine apparently
        $insertedID = mysqli_insert_id($db);
        
        if (!file_exists('uploads/temp/' . $uploadedFileName)) {
            // video file was never successfully uploaded
            echo 'recorded video file was not uploaded successfully';
        }
        
        if ( !rename('uploads/temp/'.$uploadedFileName, 'uploads/video/'.$uploadedFileName) ){
            echo "error moving newly recorded file";
        }
        
        // redirect to index page where the 'source' video is the one the user just made
        header( 'Location: index.php?v=' . $insertedID . '&pID=' . $userid);
        
        
    }

?>
