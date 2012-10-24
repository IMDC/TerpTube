<?php
   require('include/functions.inc.php');

   // setting timezone required for accurate date/time display
   date_default_timezone_set('America/Toronto'); 
   
   // this is the vidpath for the server at nuitblanche ##martinfixme
   $vidpath = "streams/";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
   <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
   <title>Streams</title>
   <link rel="stylesheet" href="css/main.css" type="text/css" media="screen" />
   <link rel="stylesheet" href="advance/mediaboxAdvBlack21.css" type="text/css" media="screen" />
   <script type="text/javascript" src="advance/mootools-1.2.5-core-yc.js"></script>
   <script type="text/javascript" src="advance/quickie.js"></script>
   <script type="text/javascript" src="advance/mediaboxAdv-1.3.4b.js"></script>
   <script>

          
  </script>
</head>   

<body>
	<div class="wrapper">
   <div class="centered" id="signtitle">
      <h1>Streams</h1>
   </div>
      <div id="vid-list" class="centered">
   	   <div id="vid-list-container" class="centered">

   	      <?php
               $vidoutputHTML = "";

               $files = glob('streams/tempVideos/*.webm');
               usort($files, function($a, $b) {
                  return filemtime($a) < filemtime($b);
               });

               // scan the directory and output a img thumbnail and link for all flv files
               foreach ($files as $streamFilePath) {
                  $jpgmatch = strip_ext($streamFilePath) . ".jpg";
                  
                  $pattern = '/^streams\/([0-9]+)_/';
                  preg_match($pattern, $streamFilePath, $matches);
                  $vidIDnum = $matches[1];

                  $created_thumbnail = "";
                  // make sure there is a matching .jpg file with the same name
                  if (!file_exists($jpgmatch)) {
                     $created_thumbnail = createThumbnail($streamFilePath, "144x112");
                     //echo $created_thumbnail;
                  }
                  else {
                     $created_thumbnail = $jpgmatch;
                  }

                  //$jpgEmatch = htmlspecialchars($jpgmatch);
                  $jpgEmatch = htmlspecialchars($created_thumbnail);
                  //echo $jpgEmatch;
                  $joutput = '
                     <div class="player">
                        <span style="font-size:8pt;display:block;height:17px">' . trim($streamFilePath, "streams/") . '</span>
                        <a rel="lightbox[flash 640 480]" href="' . $streamFilePath . '"><img src="' . $jpgEmatch . '" /></a><br />
			<span>' . date('g:i a, M j',filemtime($streamFilePath)) . '</span>
                     </div>
                    ';
                 echo $joutput;
               
               }
            ?>
            <div style="clear:both;" />
   	   </div> <!-- end vid-list-container div -->
   	</div> <!-- end vid-list div -->
   </div> <!-- end wrapper div -->
</body>
</html>
