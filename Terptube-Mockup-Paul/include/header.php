<?php

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="CACHE-CONTROL" CONTENT="NO-CACHE">
        <title>Signlink Studio @ IDI</title>

        <!-- CSS files -->
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/reset.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/jquery-ui-1.9.0.custom.min.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/index-slimmer.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/playr.css" type="text/css" media="screen" />

        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/record-or-preview/common.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/record-or-preview/densityBar.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/record-or-preview/record.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/record-or-preview/preview.css" type="text/css" media="screen" />
		
        <!-- jquery -->
        <script type="text/javascript" src="<?php echo SITE_BASE ?>js/jquery-1.8.2.js"></script>
        
		<script type="text/javascript" src="<?php echo SITE_BASE ?>js/jquery-ui-1.9.0.custom.min.js"></script>
		<script type="text/javascript" src="<?php echo SITE_BASE ?>js/toolbox.flashembed.min.js"></script>
		
		<!-- Modernizr -->
		<script type="text/javascript" src="<?php echo SITE_BASE ?>js/modernizr.js"></script>    
        <!--  jacarousel -->
        <script type="text/javascript" src="<?php echo SITE_BASE ?>js/jcarousel/jquery.jcarousel.js"></script>
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>js/jcarousel/skin.css" type="text/css" media="screen" />

        <!-- flowplayer -->
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/flowplayer/flow.css" type="text/css" media="screen" />
        <script src="http://releases.flowplayer.org/js/flowplayer-3.2.10.min.js"></script>
        <script src="<?php echo SITE_BASE ?>js/jquery_function.js"></script>
        <script src="<?php echo SITE_BASE ?>js/captioner.js"></script>

        <!-- ---------------------AJAX UPLOAD!!!!-------------------------------- -->
        <script src="<?php echo SITE_BASE ?>js/ajaxupload/fileuploader.js" type="text/javascript"></script>
        <link href="<?php echo SITE_BASE ?>css/ajaxupload/fileuploader.css" rel="stylesheet" type="text/css">


        <script type="text/javascript">
            window.addEventListener("load",function(eventData) {
                captionator.captionify();
            });
        </script>
    	<script type="text/javascript" src="<?php echo SITE_BASE ?>js/index/common_function.js"></script>
		<script type="text/javascript" src="<?php echo SITE_BASE ?>js/index/martinFunctions.js"></script>
		<script type="text/javascript" src="<?php echo SITE_BASE ?>js/index/signlink.js"></script>
		<script type="text/javascript" src="<?php echo SITE_BASE ?>js/index/comment.js"></script>
		<script type="text/javascript" src="<?php echo SITE_BASE ?>js/recordOrPreview/videoManipulation.js"></script>
		<script type="text/javascript" src="<?php echo SITE_BASE ?>js/recordOrPreview/densityBar.js"></script>
        
    </head>
    <body>