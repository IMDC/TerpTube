<?php

$path = "/media/storage/projects/sls/public_html";

/**
 * Strip the extension part of a file, return only the filename
 * @param $filename the full name of the file
 * @return string with the extension part removed
 */
function strip_ext($filename) {
    // strip extension and get only video name
    //$fnameonly = substr($filename, 0, strrpos($filename, '.'));
    
    $parts = pathinfo($filename);
    $thefnameonly = $parts['filename'];
    return $thefnameonly;
}

/**
 *  Uses the gd image library to overlay a play button image 
 * onto the jpg thumbnail files generated by ffmpeg 
 *
 * @param string $videoPath  the full path to the video file eg) /nuitBlancheFiles/dirname002/dirname002.mp4
 * @param string $size  the size of a video thumbnail, WxH eg) 144x112 
 */
/*
 * The following function was altered to add the thumbnail to the thumb folder
 * It was taken from the signlinkcms so for the original look there. The folder structure differed from the one in the signlinkcms folder
 * The function returns only a file name, because the calling form knows where the files will be placed ex. test.jpg
 * Freeze time is the time ffmpeg will snap the photo for the thumbnail
 */
function createThumbnail($videoPath, $thumbName, $freezeTime, $size = "144x112") {
    // $videoPath is of the format "/streams/489_134141341341.flv"
    // change this to alter the default thumbnail image

    $default_thumbnail = IMAGES_DIR . "default_videothumb.png";

    $videoPath  = escapeshellcmd($videoPath);
    $size       = escapeshellcmd($size);
    $fnameonly  = strip_ext($videoPath);

    // $newthumbjpg = "../uploads/comment/thumb/" . $thumbName . ".jpg";
	$newthumbjpg = UPLOAD_DIR . "comment/thumb/" . $thumbName . ".jpg";
	
    //$tempthumbjpg = $fnameonly . 'temp.jpg';
    // $tempthumbjpg = "../comment/thumb/" . $fnameonly . 'temp.jpg';
	$tempthumbjpg = UPLOAD_DIR . "comment/thumb/" . $fnameonly . 'temp.jpg';

    // $stringToExecuteRegular = "ffmpeg/ffmpeg -i " . $videoPath . " -ss " . $freezeTime . " -f image2 -vframes 1 -s " . $size . " " . $tempthumbjpg;
	$stringToExecuteRegular = FFMPEG_PATH . "ffmpeg -i " . $videoPath . " -ss " . $freezeTime . " -f image2 -vframes 1 -s " . $size . " " . $tempthumbjpg;

    $escaped_command = escapeshellcmd($stringToExecuteRegular);
    error_log($escaped_command);

    //echo $escaped_command;
    //$output = shell_exec($escaped_command); 
    $output = shell_exec($escaped_command);
    error_log($output);

    // make sure new JPG file exists
    if (!file_exists($tempthumbjpg)) {
        error_log("**functions.inc.php ** No image file created for " . $videoPath . ", does ffmpeg have correct permissions and dir writeable?", 0);
        return $default_thumbnail;
    }

    // use the newly created thumbnail
    $image = imagecreatefromjpeg($tempthumbjpg);

    if (!$image) {
        //echo '<p>no image file created, does ffmpeg have correct permissions and dir writeable?</p>';
        error_log("**functions.inc.php ** No image file created for " . $videoPath . ", does ffmpeg have correct permissions and dir writeable?", 0);
        return $default_thumbnail;
    }

    // declare the path to our play button image
    $pathToDefImage = "../images/play_btn.png";
    //$pathToDefImage = IMAGES_DIR . "play_btn.png";


    $watermark = imagecreatefrompng($pathToDefImage);

    if (!$watermark) {
        error_log("**ERROR functions.inc.php** - no watermark made, check the path to the play_btn image", 0);
        return $default_thumbnail;
    }

    imagealphablending($image, true);
    imagealphablending($watermark, true);

    // render play button .png file on top of thumb.jpg file
    imagecopy($image, $watermark, imagesx($image) / 2 - 22, imagesy($image) / 2 - 22, 0, 0, imagesx($watermark), imagesy($watermark));

    // create new thumbnail with play button overlayed on top in the same folder
    if (!imagejpeg($image, $newthumbjpg)) {
        //print "**ERROR** - Error creating new thumbnail jpeg file, check directory permissions";
        error_log("**ERROR functions.inc.php** - Error creating new thumbnail jpeg file for " . $videoPath . ", check directory permissions", 0);
        return $default_thumbnail;
    }

    unlink($tempthumbjpg);
    imagedestroy($image);
    imagedestroy($watermark);
	
    //echo basename($newthumbjpg);
    
    return basename($newthumbjpg);
}

function createSignlinkThumb($videoPath, $thumbName, $freezeTime, $size = "144x112") {
    // $videoPath is of the format "/streams/489_134141341341.flv"

    $default_thumbnail = "default_videothumb.png";

    $videoPath = escapeshellcmd($videoPath);
    $size = escapeshellcmd($size);
    //$fnameonly = strip_ext(thumbName);

    $newthumbjpg = '../uploads/signlink/' . $thumbName . '.jpg';

    $stringToExecuteRegular = "../include/ffmpeg/ffmpeg -i " . $videoPath . " -frames " . $freezeTime . " -f image2 -vframes 1 -s " . $size . " " . $newthumbjpg;

    $escaped_command = escapeshellcmd($stringToExecuteRegular);
    $output = shell_exec($escaped_command);

    // use the newly created thumbnail
    $image = imagecreatefromjpeg($newthumbjpg);

    //make sure new JPG file exists
    if (!file_exists($newthumbjpg)) {
        //echo returned . "<br/>";
        return $default_thumbnail;
    }

    if (!$image) {
        error_log("**functions.inc.php ** No image file created for " . $videoPath . ", does ffmpeg have correct permissions and dir writeable?", 0);
        return $default_thumbnail;
    }

    imagedestroy($image);
    return basename($newthumbjpg);
}

/**
 * Checks for existence of a .jpg file with the same name as the comment id
 * in thumbnail directory specified in the setup.php file
 * If a jpg file is not found for a comment, it returns a default
 * thumbnail image
 * 
 * @param $commid the id of the comment
 * @param $idonly if set to 1 will return the id only, not the full path of the file
 * @return the string name of the comment.jpg file, or a default thumbnail file
 */
function getVideoThumbnail( $commid, $vidfilepath, $idonly=1 ) {
	$cid = intval($commid);	
	
	$thumb = THUMBNAIL_DIR . $cid . ".jpg";
	error_log("trying to find comment id: $cid in getVideoThumbnail function");
	if (!file_exists($thumb)) {
		error_log("thumbnail not found for comment id: " . $cid . " when searching in $thumb");
	
		$target_path = "../uploads/" . $vidfilepath;
        error_log("attempting to create new thumbnail from file: $target_path for comment: $cid in getVideoThumbnail function");
		$newthumb = createThumbnail($target_path, $cid, 1);
		if (!$newthumb)
		  return THUMBNAIL_DEFAULT;
        
        return THUMBNAIL_DIR . $cid . ".jpg";
	}
	
	if ( intval($idonly) == 1 )
		return $cid . ".jpg";
	else
		return $thumb;
	
}

/**
 * This function does not remove a comment from the database!
 * It simply sets the 'deleted' field in the database to 1 to indicate it's deletion
 * This is for the purposes of tracking any content created by participants in the future
 * 
 * @param commid the comment id as an integer
 * @return the id of the deleted comment on success, otherwise will return false
 */
function deleteCommentByID($commid) {
    global $db;

    $id = intval($commid);
    
    
    /* create a prepared statement */
    if ($stmt = mysqli_prepare($db, "Update video_comment set deleted=1 where comment_id=?")) {

        /* bind parameters for markers */
        mysqli_stmt_bind_param($stmt, "i", $id);

        /* execute query */
        mysqli_stmt_execute($stmt);
        
        if (mysqli_stmt_affected_rows($stmt) >= 1) {
            mysqli_stmt_close($stmt);	
            return $id;
        }

        error_log("Could not set deleted value of comment id: $id");
		
		/* close statement */
        mysqli_stmt_close($stmt);
        
        return false;
    }

    
}

/**
 * This function removes the entire database row associated with a comment id from the video_comment table
 * 
 * @param commid the ocmment id as an integer
 * @return the comment id that was deleted on success, otherwise will return false on failure
 */
function removeCommentFromDatabaseByID($commid) {
    global $db;

    $id = intval($commid);
    
    
    /* create a prepared statement */
    if ($stmt = mysqli_prepare($db, "delete from video_comment where comment_id=?")) {

        /* bind parameters for markers */
        mysqli_stmt_bind_param($stmt, "i", $id);

        /* execute query */
        mysqli_stmt_execute($stmt);
        
        if (mysqli_stmt_affected_rows($stmt) >= 1) {
            mysqli_stmt_close($stmt);	
            return $id;
        }

        error_log("Could not delete video comment id: $id from the database");
		
		/* close statement */
        mysqli_stmt_close($stmt);
        
        return false;
    }

    
}

function convertJSONCommentArrayToHTML($commarr) {
    $fullCommentArray = json_decode($commarr, true);
    $outputText = '';
    foreach ($fullCommentArray as $eacharr) {
        foreach ($eacharr as $key=>$val) {
            $outputText += "$key : $val";
        }
    }
    return $outputText;
}


/**
 * Returns an array of all comment details stored in an array, given a comment id
 * Uses the video_comment table to return this info, will return 'deleted' comments
 * @param $commentid the id of the comment
 * @param $json set to 1 if you want json encoded result returned
 * @return an array object filled with comments that are associative arrays
 */
function getCommentByID($commentid, $json=0) {
    global $db;
    $commentID = intval($commentid);
    
    /* create a prepared statement */
    //$query = "Select * from video_comment where source_id=? order by date asc";
    $query = "SELECT vc.comment_id, vc.source_id, vc.parent_id, vc.author_id, 
                vc.text_comments, vc.comment_start_time, vc.comment_end_time, 
                vc.date, vc.deleted, vc.temporal_comment, vc.has_video, 
                vc.video_filename, p.name, p.created, p.role, p.avatar 
              FROM video_comment vc, 
                   participants p 
              WHERE vc.comment_id=? 
                    AND p.id=vc.author_id";
    $stmt = mysqli_stmt_init($db);
    if ( !mysqli_stmt_prepare($stmt, $query)) {
        error_log("Failed to prepare statement in " . __FUNCTION__);
        print "Failed to prepare statement"; 
    }
    else {

        /* bind parameters */
        mysqli_stmt_bind_param($stmt, "i", $commentID);

        /* execute query */
        mysqli_stmt_execute($stmt);
        
        /* bind results */
        mysqli_stmt_bind_result($stmt, $commID, $source_id, $authID, $parentID, $textcont, 
                                    $start, $end, $commdate, $deleted, $tempcommentbool, 
                                    $hasvideobool, $videofilename, $partname, $partdatecreated, 
                                    $partrole, $partavatarfilename);
        
        $commentArray = array();
        while (mysqli_stmt_fetch($stmt)) {
            $singleCommentArray = array("id" => $commID, 
                                        "sourceid" => $source_id,
                                        "author" => $authID, 
                                        "parentid" => $parentID,
                                        "text" => htmlentities($textcont), 
                                        "starttime" => $start, 
                                        "endtime" => $end, 
                                        "date" => $commdate,
                                        "isdeleted" => $deleted,
                                        "istemporalcomment" => $tempcommentbool,
                                        "hasvideo" => $hasvideobool,
                                        "videofilename" => $videofilename,
                                        "authorname" => $partname,
                                        "authorjoindate" => $partdatecreated,
                                        "authorrole" => $partrole,
                                        "authoravatarfilename" => $partavatarfilename
                                     );
                                        
            array_push($commentArray, $singleCommentArray);
        }  
        
        /* close statement */
        mysqli_stmt_close($stmt);
        
        if ( intval($json) == 1) {
            $commentArray= json_encode($commentArray);
        }
        return $commentArray;
    }
}




/**
 * Returns an array of all comment details stored in an array, given a source video id
 * Uses the video_comment table to return this info, will return 'deleted' comments
 * @param $sourceid the id of the source video
 * @param $json set to 1 if you want json encoded result returned
 * @return an array object filled with comments that are associative arrays
 */
function getAllCommentsForSourceID($sourceID, $json=0) {
    global $db;
    $sID = intval($sourceID);
    
    /* create a prepared statement */
    //$query = "Select * from video_comment where source_id=? order by date asc";
    $query = "SELECT vc.comment_id, vc.source_id, vc.parent_id, vc.author_id, 
                vc.text_comments, vc.comment_start_time, vc.comment_end_time, 
                vc.date, vc.deleted, vc.temporal_comment, vc.has_video, 
                vc.video_filename, p.name, p.created, p.role, p.avatar 
              FROM video_comment vc, 
                   participants p 
              WHERE source_id=? 
                    AND p.id=vc.author_id 
              ORDER BY date, 
                       vc.parent_id 
                   ASC";
    $stmt = mysqli_stmt_init($db);
    if ( !mysqli_stmt_prepare($stmt, $query)) {
        error_log("Failed to prepare statement in " . __FUNCTION__);
        print "Failed to prepare statement"; 
    }
    else {

        /* bind parameters */
        mysqli_stmt_bind_param($stmt, "i", $sID);

        /* execute query */
        mysqli_stmt_execute($stmt);
        
        /* bind results */
        mysqli_stmt_bind_result($stmt, $commID, $source_id, $authID, $parentID, $textcont, 
                                    $start, $end, $commdate, $deleted, $tempcommentbool, 
                                    $hasvideobool, $videofilename, $partname, $partdatecreated, 
                                    $partrole, $partavatarfilename);
        
        $commentArray = array();
        while (mysqli_stmt_fetch($stmt)) {
            $singleCommentArray = array("id" => $commID, 
                                        "sourceid" => $source_id,
                                        "author" => $authID, 
                                        "parentid" => $parentID,
                                        "text" => htmlentities($textcont), 
                                        "starttime" => $start, 
                                        "endtime" => $end, 
                                        "date" => $commdate,
                                        "isdeleted" => $deleted,
                                        "istemporalcomment" => $tempcommentbool,
                                        "hasvideo" => $hasvideobool,
                                        "videofilename" => $videofilename,
                                        "authorname" => $partname,
                                        "authorjoindate" => $partdatecreated,
                                        "authorrole" => $partrole,
                                        "authoravatarfilename" => $partavatarfilename
                                     );
                                        
            array_push($commentArray, $singleCommentArray);
        }  
        
        /* close statement */
        mysqli_stmt_close($stmt);
        
        if ( intval($json) == 1) {
            $commentArray= json_encode($commentArray);
        }
        return $commentArray;
    }
}


/**
 * Returns an array of top level comment details stored in an array, given a source video id
 * Uses the video_comment table to return this info, will not return 'deleted' comments
 * @param $sourceid the id of the source video
 * @return an array object filled with comments that are associative arrays
 */
function getTopLevelCommentsForSourceID($sourceID) {
    global $db;
    $sID = intval($sourceID);
    
    /* create a prepared statement */
    //$query = "Select comment_id, author_id, text_comments, comment_start_time, comment_end_time, date, temporal_comment, has_video, video_filename from video_comment where source_id=? AND deleted=0 AND parent_id=0";
    $query = "SELECT vc.comment_id, vc.source_id, vc.parent_id, vc.author_id, 
                vc.text_comments, vc.comment_start_time, vc.comment_end_time, 
                vc.date, vc.deleted, vc.temporal_comment, vc.has_video, 
                vc.video_filename, p.name, p.created, p.role, p.avatar 
              FROM video_comment vc, 
                   participants p 
              WHERE source_id=? 
                    AND p.id=vc.author_id
                    AND vc.parent_id=0
                    AND vc.deleted=0";
    $stmt = mysqli_stmt_init($db);
    if ( !mysqli_stmt_prepare($stmt, $query)) {
        error_log("Failed to prepare statement in " . __FUNCTION__);
        print "Failed to prepare statement"; 
    }
    else {

        /* bind parameters */
        mysqli_stmt_bind_param($stmt, "i", $sID);

        /* execute query */
        mysqli_stmt_execute($stmt);
        
        /* bind results */
        mysqli_stmt_bind_result($stmt, $commID, $source_id, $parentID, $authID, $textcont, 
                                    $start, $end, $commdate, $deleted, $tempcommentbool, 
                                    $hasvideobool, $videofilename, $partname, $partdatecreated, 
                                    $partrole, $partavatarfilename);
        
        $commentArray = array();
        while (mysqli_stmt_fetch($stmt)) {
            $singleCommentArray = array("id" => $commID, 
                                        "sourceid" => $source_id,
                                        "author" => $authID,
                                        "parentid" => $parentID,
                                        "text" => htmlentities($textcont), 
                                        "starttime" => $start, 
                                        "endtime" => $end, 
                                        "date" => $commdate,
                                        "isdeleted" => $deleted,
                                        "istemporalcomment" => $tempcommentbool,
                                        "hasvideo" => $hasvideobool,
                                        "videofilename" => $videofilename,
                                        "authorname" => $partname,
                                        "authorjoindate" => $partdatecreated,
                                        "authorrole" => $partrole,
                                        "authoravatarfilename" => $partavatarfilename
                                        );
                                        
            array_push($commentArray, $singleCommentArray);
        }
        
        /* close statement */
        mysqli_stmt_close($stmt);
        
        return $commentArray;

    }
}

function printCommentVideoSource($commentArray) {
    // if the comment has a video filename, it means it came from a prepopulated existing source   
    /* 
    if ($commentArray["videofilename"]) {
        $thesource = REL_UPLOAD_DIR . "video" . DIRECTORY_SEPARATOR . $commentArray["videofilename"];
        return "<source src='$thesource' type='video/webm' />";
    }
    else { 
        $thesource = VIDCOMMENT_DIR . $commentArray["id"];
        // have to add the webm extension here   
        return "<source src='$thesource.webm' type='video/webm' />";
    }
    */
    $thesource = REL_UPLOAD_DIR . $commentArray["videofilename"];
    return "<source src='$thesource' type='video/webm' />";
        
}


/**
 * Returns the avatar filename stored in the participants table for a given participant id.
 * If no avatar filename is found, it returns a default value that is set in setup.php
 * @param $partID the participant id
 * @return the value for the avatar field as a string
 */
function getAvatarFilenameForParticipantID($partID) {
    global $db;
    $partid  = intval($partID);
    
    /* create a prepared statement */
    $query = "Select avatar from participants where id=? LIMIT 1";
    $stmt = mysqli_stmt_init($db);
    if ( !mysqli_stmt_prepare($stmt, $query)) {
        error_log("Failed to prepare statement in " . __FUNCTION__);
        print "Failed to prepare statement";
    }
    else {

        /* bind parameters */
        mysqli_stmt_bind_param($stmt, "i", $partid);

        /* execute query */
        mysqli_stmt_execute($stmt);
        
        if (mysqli_stmt_affected_rows($stmt)<=0) {
            mysqli_stmt_close($stmt);
            // return DEFAULT_AVATAR_FILENAME;
            return DEFAULT_AVATAR_FILENAME;
            error_log("no avatar found for participant id: " . $partid);
        }
        
        /* bind results */
        mysqli_stmt_bind_result($stmt, $avatarfilename);
        
        //$resultsArray = array("id" => $partID, "avatarfilename" => $avatarfilename); 
        
        /* close statement */
        mysqli_stmt_close($stmt);
        
        return $avatarfilename;
    }
}


/**
 * This function returns all replies to a selected top level comment that was made about a given source id
 * @param $thesourceID the id of the source video the top level comment was made on
 * @param $thecommentID the id of the top level comment 
 */
function getCommentRepliesForSourceID($thesourceID, $thecommentID) {
    global $db;
    $sourceID  = intval($thesourceID);
	$commentID = intval($thecommentID);
    
    /* create a prepared statement */
    //$query = "Select comment_id, author_id, text_comments, comment_start_time, comment_end_time, date, temporal_comment, has_video, video_filename from video_comment where source_id=? AND parent_id =? AND deleted=0";
    $query = "Select vc.comment_id, vc.author_id, vc.text_comments, vc.comment_start_time, 
                     vc.comment_end_time, vc.date, vc.temporal_comment, vc.has_video, 
                     vc.video_filename, p.name, p.created, p.role, p.avatar
              from video_comment vc, 
                   participants p
              where p.id=vc.author_id
                AND source_id=? 
                AND parent_id=? 
                AND deleted=0";
    $stmt = mysqli_stmt_init($db);
    if ( !mysqli_stmt_prepare($stmt, $query)) {
        error_log("Failed to prepare statement in " . __FUNCTION__);
        print "Failed to prepare statement";
    }
    else {

        /* bind parameters */
        mysqli_stmt_bind_param($stmt, "ii", $sourceID, $commentID);

        /* execute query */
        mysqli_stmt_execute($stmt);
        
        /* bind results */
        mysqli_stmt_bind_result($stmt, $commID, $authID, $textcont, $start, $end, $commdate, 
                                   $tempcommentbool, $hasvideobool, $videofilename, $partname,
                                   $partdatecreated, $partrole, $partavatarfilename);
        
        $commentArray = array();
        while (mysqli_stmt_fetch($stmt)) {
            $singleCommentArray = array("id" => $commID, 
                                        "author" => $authID, 
                                        "text" => htmlentities($textcont), 
                                        "starttime" => $start, 
                                        "endtime" => $end, 
                                        "date" => $commdate,
										"istemporalcomment" => $tempcommentbool,
										"hasvideo" => $hasvideobool,
										"videofilename" => $videofilename,
                                        "authorname" => $partname,
                                        "authorjoindate" => $partdatecreated,
                                        "authorrole" => $partrole,
                                        "authoravatarfilename" => $partavatarfilename
                                        );
                                        
            array_push($commentArray, $singleCommentArray);
        }
        
        /* close statement */
        mysqli_stmt_close($stmt);
        
        return $commentArray;

    }
}


function getExistingVideosForSourceID($theid) {
	global $db;
	$sourceid = intval($theid);
	
	/* create a prepared statement */
    $query = "Select ev.title from evids_to_sourcevids as es, existing_video as ev where es.source_id = ? AND es.existingvideo_id = es.source_id";
    $stmt = mysqli_stmt_init($db);
    if ( !mysqli_stmt_prepare($stmt, $query)) {
        error_log("Failed to prepare statement in 'getExistingVideosForSourceID'");
        print "Failed to prepare statement";
    }
    else {

        /* bind parameters */
        mysqli_stmt_bind_param($stmt, "i", $sourceid);

        /* execute query */
        mysqli_stmt_execute($stmt);
        
        /* bind results */
        mysqli_stmt_bind_result($stmt, $existingVideoTitle);
        
        $existingVideoTitles = array();
        while (mysqli_stmt_fetch($stmt)) {                
            array_push($existingVideoTitles, $existingVideoTitle);
        }
        
        /* close statement */
        mysqli_stmt_close($stmt);
        
        return $existingVideoTitles;

    }
}

/**
 * Basics of an error output using sessions
 */
function checkError() {
	if ( isset($_SESSION['error']) ) {
		$errorArray = $_SESSION['error'];
		
		if ( !empty($errorArray) ) {
					
			$errorString = '';
			
			foreach ($errorArray as $errstring) {
				$errorString .= "<p class='error-red'>$errstring</p>";
			}
			$divstring = "<div class='error-wrap'><p>An error has occured, please try again.</p>$errorString</div><div class='cleardiv'></div>";
			
			// clear errors now 
			unset($_SESSION['error']);
			return $divstring;
		}
		else {
			unset($_SESSION['error']);
			return;
		}
	}
}


function addError($errorString) {
	// grab existing errors from session var
	/*
	$arr = $_SESSION['error'];
	
	// add new one
	array_push($arr, $errorString);
    
    $_SESSION['error'] = $arr;
    */
    
    $_SESSION['error'][] = $errorString;
}


/**
 * Prints the 'Edit' and 'Delete' links for a comment
 */
function printCommentTools($cID) {  
    $cid = intval($cID);

    $output = "<div class='comment-tools-div'>
                    <ul>
                        <li><a href='#' id='edit-$cid' class='comment-edit-link'>Edit</a></li>
                        <li><a href='#' id='delete-$cid' class='comment-delete-link'>Delete</a></li>
                    </ul>         
                </div>";
    return $output;
}


/**Return a <track> element that points to a captions file
 * in the uploads/caption/ directory corresponding to 
 * the given video id in the video_source table
 * 
 * @global type $db database connection
 * @param type $videonum id of a video from the video_source table
 * @return type a string containing a complete html5 video <track> element
 */
function getCaptionFileForSourceVideo($videonum, $label='') {
    // select filename from caption_file where video_source_id = $videonum
    global $db;
    $vidnumber = intval($videonum);
    $label = mysqli_real_escape_string($db, $label);
    
    /* create a prepared statement */
    $query = "select filename from caption_file where video_source_id = ?";
    $stmt = mysqli_stmt_init($db);
    if ( !mysqli_stmt_prepare($stmt, $query)) {
        error_log("Failed to prepare statement in 'getCaptionFileForSourceVideo'");
        print "Failed to prepare statement";
        return;
    }
    else {
        /* bind parameters */
        mysqli_stmt_bind_param($stmt, "i", $vidnumber);

        /* execute query */
        if (!mysqli_stmt_execute($stmt)) {
            $errnum = mysqli_stmt_errno($stmt);
            $errmsg = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            error_log("ERROR: $errnum, $errmsg");
            echo $errmsg;
            
            echo "<h1>An error has occured, please go back and try again</h1>";
            echo "<a href='start.php'>Go back</a>";
            return;
        }
        
        /* bind results */
        mysqli_stmt_bind_result($stmt, $captionFile);
        
        mysqli_stmt_close($stmt);
        
        if ( empty($captionFile) ) {
            return;
        }
        $output = "<track id='enTrack' kind='captions' src='uploads/caption/$captionFile' type='text/vtt' srcLang='en' label='$label' default />";
        return $output;
    // <track id="enTrack" kind="captions" src="uploads/caption/upc.vtt" type="text/vtt" srclang="en" label="English Subtitles" default />
    }
}


?>
