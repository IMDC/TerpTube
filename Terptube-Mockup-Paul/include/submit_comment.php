<?php
require_once ('../setup.php');
require_once (INC_DIR . 'config.inc.php');
require_once (INC_DIR . 'functions.inc.php');

global $db;

define('CREATE_NEW_COMMENT', 0);
define('EDIT_COMMENT', 1);

/** params needed for db **/

$sourceID 		    = intval($_POST['v']);
// get parent id of post, will be 0 if a top-level comment, other id for a nested
// comment
$parentID 			= intval($_GET['pID']);
// $parentID = intval($_POST['pID']);
$authorID 			= "0"; //hardcoded for now 
$comment_text 		= mysqli_real_escape_string($db, $_POST['comment']);;
$comment_start_time = $_POST['start_time'];
$comment_end_time 	= $_POST['end_time'];
//$date;
$temporal_comment	= 0; // initialize to 0, set later if true
$has_video			= 0; // initialize to 0, set later if true

$video_filename     = ''; // init to empty, set later
$existingVidChoice  = ''; // init to empty, set later

addError("value of post file-name is: " . $_POST['file-name'] . "|END");

// check if we have a filename for an uploaded video
if ( isset($_POST['file-name']) ) {
    $filenametrim = trim($_POST['file-name']);
    if ( !empty($filenametrim) ) {
        $video_filename = UPLOAD_DIR . 'temp/' . $filenametrim;;
    }
}

// check if user selected to use an existing video in the comment
if ( isset($_POST['user-existing-video']) ) {
	$existingVidChoice = $_POST['user-existing-video'];
}

// are we creating new, or editing existing?
// currently unused
$action = intval($_POST['action']);


if ( isset($comment_start_time) && (isset($comment_end_time)) ) {
	$temporal_comment = 1;
}

// if there is a video filename or a value selected in the dropdown box
if ( $video_filename || $existingVidChoice ) {
	$has_video = 1;
	
    
	if (existingVidChoice && $video_filename ) {
		$video_filename = $existingVidChoice;
	}
}

addError("video_filename: $video_filename existingVidChoice: $existingVidChoice");
error_log("video_filename: $video_filename existingVidChoice: $existingVidChoice");

// database insertion code was here




$commentID = insertCommentIntoDatabase($sourceID, $parentID, $authorID, $comment_text, $comment_start_time, $comment_end_time, $temporal_comment, $has_video, $video_filename);


if ($commentID) { // successful comment insertion into database
	
	/* file uploads to scripts/uploads
	 upload to sever	*/
	
	//$target_path = "../uploads/comment/" . $comment_id . ".mp4";
	$target_path = VIDCOMMENT_DIR . $commentID . ".webm";
	
	//$target_path = $target_path . basename( $fileName);
	
	//   if (!copy($fileName, $target_path)) {
	//    	echo "failed to copy $file...\n";
	//   }
	
	/****************GENERATE THUMBNAIL ******************************/
	if ( isset($video_filename) ) {
	    if ( copy($video_filename, $target_path) ) {// $_FILES['uploadedfile']['tmp_name']
	        createThumbnail($target_path, $commentID, 2);
	        unlink($video_filename);
	    }
	}
	
	
	// TODO: add uploaded video to list of existing vids for that user?
	
	
	// header("Location: " . SITE_BASE . "index.php?v=$sourceID");
	// require_once (INC_DIR . 'footer.php');
	
}
else {
	error_log('ERROR in submit_comment.php');
	addError("Error creating new comment");
}

header("Location: " . SITE_BASE . "index.php?v=$sourceID");





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


?>