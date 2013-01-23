<?php
    require_once('setup.php');
    require_once(INC_DIR . 'config.inc.php');
    require_once(INC_DIR . 'header.php');
    require_once(INC_DIR . 'functions.inc.php');
    
    // clear any old session
    session_destroy();
    session_start();

    global $db;

    $supername = mysqli_real_escape_string($db, $_POST['supervisor']);
    $particID  = intval($_POST['participantid']);
    $userrole = mysqli_real_escape_string($db, $_POST['role']);
    $avatarfilepath = DEFAULT_AVATAR_FILENAME;
    
    $now = date('Y-m-d G:i:s');
    
    if ($userrole == "Mentor" || $userrole == "Mentee") {
        // proceed
    }
    else {
        echo error;
    }
    
    
?>
<style>
    h1, form, input {
        padding: 20px;
    }
    
    h1 {
        font-family: Tahoma;
        font-size: 24px;
    }
    h3 {
        font-family: Tahoma;
        font-size: 16px;
        line-height: 150%;
        padding-left: 20px;
    }
    p {
        display: block;
        padding: 40px;
    }
    
</style>
<div class="container">
    
<?php
    /* create a prepared statement */
    $query = "INSERT INTO participants (id, created, name, supervisor, role, avatar) values (?, DEFAULT, 'participant', ?, ?, ?)";
    $stmt = mysqli_stmt_init($db);
    if ( !mysqli_stmt_prepare($stmt, $query)) {
        error_log("Failed to prepare statement in 'init.php'");
        print "Failed to prepare statement";
    }
    else {

        /* bind parameters */
        mysqli_stmt_bind_param($stmt, "isss", $particID, $supername, $userrole, $avatarfilepath);

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
        
        echo "<h1>Success</h1><h3>particpantid: $particID </h3><h3> supervisor: $supername </h3><h3>role: $userrole</h3>";
        
        $_SESSION['participantID'] = $particID;
        $_SESSION['supervisorName'] = $supername;
        $_SESSION['role'] = $userrole;
        
        if ($userrole == "Mentor") {
            echo "<p><a href='index.php?v=1&pID=$particID'>Proceed as Mentor</a></p>";
        }
        else if ($userrole == "Mentee") {
            echo "<p><a href='menteecreate.php?pID=$particID'>Proceed as Mentee</a></p>";
        }
        
    }
?>
        </div>
    </body>
</html>
