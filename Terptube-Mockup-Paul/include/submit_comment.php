<?php
require_once ('../setup.php');
require_once (INC_DIR . 'config.inc.php');
require_once (INC_DIR . 'functions.inc.php');

global $db;

/** params needed for db **/

$sourceID 		    = intval($_POST['v']);
// get parent id of post, will be 0 if a top-level comment, other id for a nested comment
$parentID 			= intval($_GET['pID']);
$parentIDfromForm   = intval($_POST['parentID']);

$authorIDform       = intval($_POST['partID']);
$authorIDsession    = intval($_SESSION['participantID']);
$authorIDget        = intval($_GET['aID']);

$authorIDchecked;

// action is the action to take on the comment: new, edit, reply
$action             = mysqli_real_escape_string($db, $_POST['formAction']);

$comment_text 		= htmlentities(mysqli_real_escape_string($db, $_POST['comment']));
$comment_start_time = $_POST['start_time'];
$comment_end_time 	= $_POST['end_time'];

$temporal_comment	= 0; // initialize to 0, set later if true
$has_video			= 0; // initialize to 0, set later if true

$video_filename     = ''; // init to empty, set later
$rec_vid_fn_only    = '';
$existingVidChoice  = ''; // init to empty, set later
$existingVidChosen  = 0;

$videofilepathfordb = ''; // init to empty, set later

error_log("form action received in submit_comment.php is $action");
error_log("value of the user-existing-video from post is: " . $_POST['user-existing-video']);

if ( ($authorIDform != $authorIDsession) && ($authorIDsession != $authorIDget) ) {
    error_log('comment creation failed, author ids are different');
    addError("An error occured, please try again");
    return;
}
else {
    $authorIDchecked = $authorIDsession;
}

// check if we have a filename for an uploaded video
if ( isset($_POST['file-name']) ) {
    $filenametrim = trim($_POST['file-name']);
    if ( !empty($filenametrim) ) {
        $video_filename = UPLOAD_DIR . 'temp/' . $filenametrim;;
        $parts = pathinfo($video_filename);
        $rec_vid_fn_only = $parts['basename'];
    }
}

// check if user selected to use an existing video in the comment
if ( isset($_POST['user-existing-video']) && !empty($_POST['user-existing-video'])  ) {
	$existingVidChoice = $_POST['user-existing-video'];
    $existingVidChosen = 1;
}


if ( isset($comment_start_time) && (isset($comment_end_time)) ) {
	$temporal_comment = 1;
    
    if ( ($comment_end_time == 0) && ($comment_end_time == 0) ) {
        $temporal_comment = 0;
    }
}

// if there is a recorded video filename or an existing video selected in the dropdown box
if ( $rec_vid_fn_only || $existingVidChoice ) {
	// set has_video flag    
	$has_video = 1;
    
    // if user selected an upload or a recording, use that video file name
	//if ($existingVidChoice && $rec_vid_fn_only ) {
    if ( $rec_vid_fn_only ) {
		$videofilepathfordb = 'comment' . DIRECTORY_SEPARATOR . $rec_vid_fn_only;
	}
    // if no upload selected but user chose an existing vid
    else if (empty($rec_vid_fn_only) && !empty($existingVidChoice)) {
        $videofilepathfordb = 'video' . DIRECTORY_SEPARATOR . $existingVidChoice;
        $existingVidChosen = 1;
    }
    else {
        $videofilepathfordb = 'comment' . DIRECTORY_SEPARATOR . $rec_vid_fn_only;
    }
}

error_log("video_filename: $video_filename rec_vid_fn_only: $rec_vid_fn_only existingVidChoice: $existingVidChoice existingVidChosen: $existingVidChosen videofilepathfordb: $videofilepathfordb");

// database insertion code was here
$redirectLocation = "";

switch ($action) {
    case "new"   :
    case "reply" :
        $commentID = insertCommentIntoDatabase($sourceID, $parentID, $authorIDchecked, $comment_text, $comment_start_time, $comment_end_time, $temporal_comment, $has_video, $videofilepathfordb);
        
        if ( !$commentID  || !isset($commentID) ) {
            // fail, comment wasn't inserted in to our database
            error_log('ERROR in submit_comment.php while inserting new comment');
            addError("Error creating new comment");
            $redirectLocation = "index.php?v=$sourceID&pID=$authorIDchecked";
            header("Location: " . SITE_BASE . $redirectLocation);
            return;
        }
        
        // successful comment created in database from here
            
        // check for if comment has video data
        if (!$has_video) {
            // comment successfully created, didn't have any video info, so no thumbnail image needed
            error_log("comment id: $commentID created successfully with no video thumbnail necessary");
            $redirectLocation = "index.php?v=$sourceID&pID=$authorIDchecked";
            header("Location: " . SITE_BASE . $redirectLocation);
            return;
        }
        
        // comment has video data, need to create thumbnails
        
        //$target_path = UPLOAD_DIR . "comment" . DIRECTORY_SEPARATOR . $commentID . ".webm";
        $video_filename = "../uploads/temp/"    . $rec_vid_fn_only;
        $target_path    = "../uploads/comment/" . $rec_vid_fn_only;

        if ( $existingVidChosen == 1 ) {
            $target_path    = "../uploads/video/" . $existingVidChoice;
            error_log("existing video chosen, trying to create thumbnail for $videofilepathfordb in upload directory: " . UPLOAD_DIR);
            //createThumbnail(UPLOAD_DIR . $videofilepathfordb, $commentID, 1);
            createThumbnail($target_path, $commentID, 1);
        }
        else {
            // need to copy comment video
            // copy the video (existing or uploaded) to the comments directory
            error_log("no existing video chosen, so copying $video_filename to $target_path inside submit_comment");
            
            if ( copy($video_filename, $target_path) ) {
                // successful copy
                // $videofilepathfordb = $target_path;
                createThumbnail($target_path, $commentID, 1);
                
                // the user uploaded a new video, we need to get rid of the temp one
                error_log("calling unlink on file: $video_filename");    
                unlink($video_filename);
            }
            else {
                error_log("ERROR COPYING FILE $videofilepathfordb FROM TEMP TO COMMENT FOLDER INSIDE SUBMIT_COMMENT.php");
            }
        }

        
        // can use generic redirect as all new comment insertions should go back to the original source
        $redirectLocation = "index.php?v=$sourceID&pID=$authorIDchecked";
        break;
    
    case "edit":
        // $parentIDfromForm is actually the commentID of the comment to be edited
        if (editCommentInDatabase($parentIDfromForm, $authorIDchecked, $comment_text, $comment_start_time, $comment_end_time, $temporal_comment, $has_video, $videofilepathfordb)) {
            // set commentID to the comment we edited to trigger the video thumbnail function below 
            $commentID = $parentIDfromForm;
            
            // check for if comment has video data
            if (!$has_video) {
                // comment successfully created, didn't have any video info, so no thumbnail image needed
                error_log("comment id: $commentID edited successfully with no video thumbnail necessary");
                $redirectLocation = "index.php?v=$sourceID&pID=$authorIDchecked";
                header("Location: " . SITE_BASE . $redirectLocation);
                return;
            }
            
            // comment has video data, need to create thumbnails
            
            //$target_path = UPLOAD_DIR . "comment" . DIRECTORY_SEPARATOR . $commentID . ".webm";
            $video_filename = "../uploads/temp/"    . $rec_vid_fn_only;
            $target_path    = "../uploads/comment/" . $rec_vid_fn_only;
    
            if ( $existingVidChosen == 1 ) {
                $target_path    = "../uploads/video/" . $existingVidChoice;
                error_log("existing video chosen when editing a comment, trying to create thumbnail for $videofilepathfordb in upload directory: " . UPLOAD_DIR);
                //createThumbnail(UPLOAD_DIR . $videofilepathfordb, $commentID, 1);
                createThumbnail($target_path, $commentID, 1);
            }
            else {
                // need to copy comment video
                // copy the video (existing or uploaded) to the comments directory
                error_log("no existing video chosen when editing a comment, so copying $video_filename to $target_path inside submit_comment");
                
                if ( copy($video_filename, $target_path) ) {
                    // successful copy
                    // $videofilepathfordb = $target_path;
                    createThumbnail($target_path, $commentID, 1);
                    
                    // the user uploaded a new video, we need to get rid of the temp one
                    error_log("calling unlink on file while editing comment: $video_filename");    
                    unlink($video_filename);
                }
                else {
                    error_log("ERROR COPYING FILE $videofilepathfordb FROM TEMP TO COMMENT FOLDER INSIDE SUBMIT_COMMENT.php while editing");
                }
            }
            
            
            error_log("comment id: $commentID edited successfully");
            $redirectLocation = "index.php?v=$sourceID&pID=$authorIDchecked";
            header("Location: " . SITE_BASE . $redirectLocation);
            return;
            
        }
        else {
            $redirectLocation = "index.php?v=$sourceID&pID=$authorIDchecked";
            addError("Couldn't edit comment"); 
            header("Location: " . SITE_BASE . $redirectLocation);
            return;
        }
        break;

    case "create":
        $newvidid = createNewSourceVideoInDatabase($authorIDchecked, $rec_vid_fn_only, $comment_text);
        if ($newvidid) {
            // move to uploads/video
            //$target_path = VIDCOMMENT_DIR . $rec_vid_fn_only . ".webm";
            // testing
            $target_path = "../uploads/video/" . $rec_vid_fn_only;
            $video_filename = "../uploads/temp/" . $rec_vid_fn_only;
            $whereami = shell_exec('pwd');
            error_log("OUTPUT FROM SUBMIT_COMMENT.php about where I am: $whereami ***************");
            // end testing
            error_log("attempting to copy $video_filename to $target_path inside submit_comment's create case");
            if ( copy($video_filename, $target_path) ) {
               // successful copy of file, need to delete the temp file
               error_log("calling unlink on file: $video_filename"); 
               unlink($video_filename);
               
               // redirect to just created source video
               $redirectLocation = "index.php?v=$newvidid&pID=$authorIDsession";
            }
            $redirectLocation = "index.php?v=$newvidid&pID=$authorIDsession";
        }
        else {
            error_log('ERROR in submit_comment.php while creating new video source');
            addError("Error creating new video source");
            $redirectLocation = "menteecreate.php?pID=$authorIDchecked";
        }
        
        break;
        
    default:
        
        break;
}

header("Location: " . SITE_BASE . $redirectLocation);



function insertCommentIntoDatabase($sourceID, $parentID, $authID, $comment, $start, $end, $temporal, $hasvid, $vidfile) {
	global $db;
	/* Insert into database and pull out the comment id it just created
 	*/
    mysqli_real_escape_string($db, $vidfile);
    
    mysqli_query($db, "BEGIN");
    mysqli_query($db, "START TRANSACTION");

    $now = date('Y-m-d G:i:s');

    //$sql = "INSERT INTO video_comment (source_id, parent_id,
    // author,text_comments,comment_start_time,comment_end_time, date) VALUES
    // ('$videoNumber', '$parentID',  '$author', '$comment', '$start',  '$end',
    // '$now')";
    
    
    //  WHY aren't we using a prepared statement????
    $sql = "INSERT INTO video_comment (source_id, parent_id, author_id, text_comments, comment_start_time, comment_end_time, date, temporal_comment, has_video, video_filename) VALUES ('$sourceID', '$parentID','$authID','$comment','$start','$end',DEFAULT,'$temporal','$hasvid','$vidfile')";

	error_log('sql statement for inserting comment into database: ' . $sql);
    $result = mysqli_query($db, $sql);

    $commentIDreturned = mysqli_insert_id($db);

    $sql = "SELECT comment_id FROM video_comment ORDER BY comment_id DESC LIMIT 1";
    $result = mysqli_query($db, $sql);

    while ( $row = mysqli_fetch_assoc($result) ) {
        $commentID = $row['comment_id'];
    }

    if ( $result ) {
        mysqli_query($db, "COMMIT");
		return $commentID;
    }
    else {
        mysqli_query($db, "ROLLBACK");
		return NULL;
    }
	
}

/**
 * This inserts a new row in to the video_source table, essentially creating a new
 * video for mentor's to evaluate and comment on, uses a default duration value in the DB of '50000'
 * @param $partid the participant's id
 * @param $vidtitle the filename of the video that is to be shown as the main video, ie) the uploaded video from the user
 * @param $textcomm any supplemental text comment that the author wants to include
 */
function createNewSourceVideoInDatabase($partid, $vidtitle, $textcomm) {
    global $db;
    
    // basic escaping, much better to use prepared statement
    $participantid = intval($partid);
    $videofilename = htmlentities(mysqli_real_escape_string($db, $vidtitle));
    $textcomment = htmlentities(mysqli_real_escape_string($db, $textcomm));
    
    /* Insert into database and pull out the id it just created
    */
    
    mysqli_query($db, "BEGIN");
    mysqli_query($db, "START TRANSACTION");
    
    //  WHY aren't we using a prepared statement????
    $sql = "INSERT INTO video_source (source_id, participant_id, title, duration, comment, created) values (DEFAULT, $participantid, '$videofilename', '50000', '$textcomment', DEFAULT)";

    error_log('sql statement for inserting new video source into database: ' . $sql);
    $result = mysqli_query($db, $sql);
    
    $commentIDreturned = mysqli_insert_id($db);

    /*
    $sql = "SELECT source_id FROM video_source ORDER BY source_id DESC LIMIT 1";
    $result = mysqli_query($db, $sql);

    while ( $row = mysqli_fetch_assoc($result) ) {
        $videosource_id = $row['source_id'];
    }
    */
    
    if ( $commentIDreturned != 0 ) {
        mysqli_query($db, "COMMIT");
        return $commentIDreturned;
    }
    else {
        mysqli_query($db, "ROLLBACK");
        return NULL;
    }
    
}



/**
 * Edits a comment given the comment ID
 * 
 * returns a boolean of true if the comment was edited, false if it wasn't
 */
function editCommentInDatabase($commentID, $authID, $comment, $start, $end, $temporal, $hasvid, $vidfile) {
    global $db;
    $commentID = intval($commentID);
    $authID = intval($authID);
   
    /* create a prepared statement */
    $query = "UPDATE video_comment
              SET text_comments = ?, comment_start_time = ?, comment_end_time = ?, temporal_comment = ?, has_video = ?, video_filename = ? WHERE comment_id = ? AND author_id = ?";
                  
    $stmt = mysqli_stmt_init($db);
    
    
    if ( !mysqli_stmt_prepare($stmt, $query)) {
        error_log("Failed to prepare statement in " . __FUNCTION__);
        print "Failed to prepare statement";
        return FALSE;
    }
    else {

        /* bind parameters */
        mysqli_stmt_bind_param($stmt, "siiiisii", $comment, $start, $end, $temporal, $hasvid, $vidfile, $commentID, $authID);

        /* execute query */
        mysqli_stmt_execute($stmt);
        
        // if we affected a row, that's good
        // now we need to pull the results back from the database
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            mysqli_stmt_close($stmt);
            return TRUE;
        }
        
        return FALSE;
    }
}


?>