<?php

/* database connection */
define('DB_USER', 'joeTest');
define('DB_PASSWORD', 'cltu53R');
define('DB_PORT', '3306');		// 3306
define('DB_HOST', 'localhost');		//localhost
define('DB_NAME', 'terptube_mockup');

/* uploads directory */
define('UPLOAD_DIR', 'uploads/');

/* width of column */
define('BLOCK_WIDTH', '145');

/* salt */
define('CMSSALT','8d%18:7dao#".]109a0djf1l86<14');

/* salt length */
define('SALT_LENGTH', 9);

global $db;
$db = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);
if (!$db) {
	//printf("Connect failed: %s\n", mysqli_connect_error());
	//exit();
	echo 'no!!!!';
	die('Could not connect: ' . mysqli_connect_error());
}
if (mysqli_connect_errno()) {
	echo 'no!!!!';
	die('Connect failed: ' . mysqli_connect_error());
}
?>
