<?php 
   
  require('../include/config.inc.php');
  require('../include/functions.inc.php');
  
  $fileName =  '../uploads/reply/temp/' . $_POST['file'];
  
  $now = date('Y-m-d G:i:s');
  $reply = $_POST['reply'];
  $parent_comment =  $_POST['pID'];
  $comment_id = -1;
  
  /*********Insert into database and pull out the comment id it just created ********/
  
  mysqli_query($db, "BEGIN");
  mysqli_query($db, "START TRANSACTION");
  
  
  		$sql = "INSERT INTO video_comment (source_id, parent_id, author,text_comments,comment_start_time,comment_end_time, date) VALUES (1, '$parent_comment',  'joe', '$reply', '0',  '0', '$now')";
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

   
   //if the user specified a file and we retrieved the quiery succesfully
   if($comment_id != -1 && isset($_POST['file']))
   {
   	
   	   $target_path = "../uploads/reply/" . $comment_id . ".mp4";
   	   
   	   if(copy($fileName, $target_path)) { // $_FILES['uploadedfile']['tmp_name']
   	   	createThumbnail("../uploads/reply/" . $comment_id . ".mp4", $comment_id, 2);
   	   }
   	 
   }
  
  //$sql =  "SELECT * FROM video_comment WHERE" ;
  $arr = array ('a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5);
  
  echo json_encode($arr); // {"a":1,"b":2,"c":3,"d":4,"e":5}
 
?>