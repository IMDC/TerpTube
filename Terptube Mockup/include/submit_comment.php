<?php 
   
  require_once('../include/config.inc.php');
  require_once('../include/functions.inc.php');
  

  $start = $_POST['start_time'];
 
  $end = $_POST['end_time'];
  
  $videoNumber = $_POST['v'];
  $author = "joe";
  $comment = $_POST['comment'];
  //$fileName = $_FILES['uploadedfile']['name']; 
  $fileName = '../uploads/temp/' . $_POST['file-name'];
  $pID = $_POST['pID'];
  
  
  /*********Insert into database and pull out the comment id it just created ********/
 
  mysqli_query($db, "BEGIN");
  mysqli_query($db, "START TRANSACTION");
  	
  	$now = date('Y-m-d G:i:s');
  
  	$sql = "INSERT INTO video_comment (source_id, parent_id, author,text_comments,comment_start_time,comment_end_time, date) VALUES ('$videoNumber', '$pID',  '$author', '$comment', '$start',  '$end', '$now')";
  	$result = mysqli_query($db, $sql);  
	
	$sql = "SELECT comment_id FROM video_comment ORDER BY comment_id DESC LIMIT 1";
	$result = mysqli_query($db, $sql);  
	
	while ($row = mysqli_fetch_assoc($result)) { 
   	 	 $comment_id = $row['comment_id'];
   	}
  
  if($result){
   	 mysqli_query($db, "COMMIT");
  }
  else{
   	 mysqli_query($db, "ROLLBACK");
  }
  
  /*file uploads to scripts/uploads  
  upload to sever	*/
  
  $target_path = "../uploads/comment/" . $comment_id . ".mp4";
  
  //$target_path = $target_path . basename( $fileName);
  
//   if (!copy($fileName, $target_path)) {
//    	echo "failed to copy $file...\n";
//   }
  
   /****************GENERATE THUMBNAIL ******************************/
  if(isset( $fileName))
  {
 	 if(copy($fileName, $target_path)) { // $_FILES['uploadedfile']['tmp_name']
   		  createThumbnail("../uploads/comment/" . $comment_id . ".mp4", $comment_id, 2);
          unlink($fileName);
  	 }
  } 	
    
  
  header( "Location: ../index.php?v=$videoNumber" ) ;
  require_once('../include/footer.php');

  
?>