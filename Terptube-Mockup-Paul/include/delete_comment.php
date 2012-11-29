<?php

require_once('../setup.php');
require_once(INC_DIR . 'config.inc.php');
require_once(INC_DIR . 'functions.inc.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /** TODO: needs a nonce parameter on this form * */

    $commentID = intval($_POST['cID']);

    $status = deleteCommentByID($commentID);

    // comment was successfully deleted from database
    if ($status != -1) {
        $retarr = array('status' => 'success', 'id' => $commentID);
        echo json_encode($retarr);
    }
    else {
        $retarr = array('status' => 'failure', 'id' => $commentID);
        echo json_encode($retarr);
    }
}
else {
    echo 'fail';
}
?>
