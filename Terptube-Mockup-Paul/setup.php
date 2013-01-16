<?php

// set default timezone
date_default_timezone_set("America/New_York");

// define paths
define('SITE_BASE', '/projects/sls/terpmock-paul/');
define('APP_DIR', __DIR__ . DIRECTORY_SEPARATOR);

// includes directory
define('INC_DIR', APP_DIR . 'include' . DIRECTORY_SEPARATOR);

// images directory
define('IMAGES_DIR', SITE_BASE . 'images' . DIRECTORY_SEPARATOR);

// default thumbnail image
define('THUMBNAIL_DEFAULT', IMAGES_DIR . 'default_videothumb.png');

// upload directory
define('UPLOAD_DIR', APP_DIR . 'uploads' . DIRECTORY_SEPARATOR);

// non-absolute upload directory
define('REL_UPLOAD_DIR', SITE_BASE . 'uploads' . DIRECTORY_SEPARATOR);

// encoded videocomment storage directory
define('VIDCOMMENT_DIR', REL_UPLOAD_DIR . 'comment' . DIRECTORY_SEPARATOR);

// thumbnail directory
//define('THUMBNAIL_DIR', VIDCOMMENT_DIR . 'thumb' . DIRECTORY_SEPARATOR);
define('THUMBNAIL_DIR', 'uploads/comment/thumb' . DIRECTORY_SEPARATOR);

// log directory
define('LOG_DIR', APP_DIR . "logs" . DIRECTORY_SEPARATOR);

// ffmpeg error log
define('FFMPEG_LOG_FILE', LOG_DIR . "ffmpeg-error.log" );

// ffmpeg included binary path
define('FFMPEG_PATH', INC_DIR . 'ffmpeg' . DIRECTORY_SEPARATOR);

// Default avatar filename
define('DEFAULT_AVATAR_FILENAME', 'genericAvatar.gif');

?>