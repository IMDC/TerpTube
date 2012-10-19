<?php

?>

<!doctype html>
<html>
    <head>
        <title>Signlink Studio @ IDI</title>

        <!-- CSS files -->
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/reset.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/index.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="<?php echo SITE_BASE ?>css/playr.css" type="text/css" media="screen" />

        <!-- jquery -->
        <script type="text/javascript" src="<?php echo SITE_BASE ?>/js/jquery-1.8.js"></script>

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

    </head>
    <body>