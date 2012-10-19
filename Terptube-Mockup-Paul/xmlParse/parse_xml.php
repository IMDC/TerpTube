<?php
   require('../include/config.inc.php');
   require('../include/functions.inc.php');
   
   //specify the xml file to parse
   $xml = simplexml_load_file("nuit.xml");
   
   //grab the source video duarion and name
   $sourceVideoName = $xml->movie->attributes()->src;
   $souceVideoDuration = $xml->movie->attributes()->duration;
   
   /*
    * GEt the higest signlink video id and incriment one to write the next video in the database.
   * In a transaction because it must know which video number it can use
   */
  mysqli_query($db,"BEGIN");
  mysqli_query($db,"START TRANSACTION");
   
   		$sql = "Select source_id From video_source ORDER BY source_id DESC LIMIT 1";
  		$result = mysqli_query($db, $sql);
  		
  		
  		if(mysqli_num_rows($result) > 0){
  			while ($row = mysqli_fetch_assoc($result)) { 
   	 			 $video_number = $row['source_id'] + 1;
   			}
  		}
  		else{
  			$video_number = 1;
  		}
   
   		$sql = "INSERT INTO video_source (source_id, title, duration, comment) VALUES ('$video_number',  '$sourceVideoName', '$souceVideoDuration', 'comment video')";
   		$result = mysqli_query($db, $sql);
   
  if($result){
   	 mysqli_query($db,"COMMIT");
  }
  else{
   	 mysqli_query($db,"ROLLBACK");
  }
   
   /*
   * This file is used to input a new video and its signicons into the database
   * The page created to add a comment and view side by side will only have to know the video number
   * 
   */
  
  
  /*************************************SIGNLINK PORTION Input into db and create thumb *************************************************/ 
  //grab the signicon information
  $signicon = $xml->movie->signicons->signicon;
  
  //loop through all the signicons and grab the metadata
  foreach( $signicon as $value )
  {
      $startTime = $value->movietime->attributes()->start;
      $startTime = intval( $startTime/1000);
      $endTime = $value->movietime->attributes()->end;
      $endTime = intval( $endTime/1000);
      $frameTime = $value->movietime->attributes()->frametime;
      $imageTime = $value->movietime->attributes()->frametime;
  	  $url = $value->url;
  	  $linkLabel = $value->label;
  	  
  	  $sql = "INSERT INTO video_signlink (source_id, start_time, end_time, frame_time, url, label) VALUES ('$video_number', '$startTime' , '$endTime', '$frameTime', '$url', '$linkLabel')";
  	  $result = mysqli_query($db, $sql);
  }
  
  //generate thumbs for video and names them the signlink id.jpg
  $sql = "SELECT * FROM video_signlink WHERE source_id = '$video_number'";
  $result = mysqli_query($db, $sql);
  
  while ($row = mysqli_fetch_assoc($result)) {
  	  $thumbnail = createSignlinkThumb('../uploads/video/' . $sourceVideoName, $row['signlink_id'], $row['frame_time']);
  	  echo $thumbnail . "<br/>";
  }
  
  
  /****************************** CAPTION PORTION ******************************************************/
  
  //grab the caption information for the video
  $caption = $xml->movie->captions->list->caption;
  
  //loop through all the captions and grab the metadata
  foreach( $caption as $value )
  {
  	 $startTime = $value->time->attributes()->start;
  	 $startTime = intval( $startTime/1000);
  	 $endTime = $value->time->attributes()->end;
  	 $endTime = intval( $endTime/1000);
  	 $text = $value->text;
    
   	 $sql = "INSERT INTO video_caption (source_id, start_time, end_time, text) VALUES ('$video_number', '$startTime' , '$endTime', '$text')";
  	 $result = mysqli_query($db, $sql);
  }
  
  mysqli_close($db);
  
  // $sql = "INSERT INTO video_comment (source_id, author, text_comments, comment_start_time, comment_end_time, date) VALUES ('$video_number', '$author' , '$endTime', '$text')";
  //   $result = mysqli_query($db, $sql);
?>