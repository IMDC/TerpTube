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
    ul.testlinklist {
        margin-top: 20px;
        width: 400px;
        height: auto;
        left: 100px;
        position: relative;
    }
    ul.testlinklist li {
        cursor: pointer;
        padding: 10px 5px;
        border-bottom: 1px solid rgb(186,186,189);
    }
    ul.testlinklist li a:hover {
        background: rgba(186,186,189,0.5);
    }
    
</style>
<div class="container">
    <img src="images/eku.jpg" title="Eastern Kentucky University" alt="Eastern Kentucky University" style="width:10%;padding-left:15%;" />
    <img src="images/imdclogosmall.png" title="Inclusive Media and Design Centre" alt="IMDC lab at Ryerson University" style="width:10%;clear:both;padding-left:15%;" />
    <br />
    <div style="padding-left: 20px;">
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
            
            echo "<h1 style='color:#ff0000;font-weight:bold;border-bottom:1px solid #ff0000;'>An error has occured, please go back and try again</h1><br /><br />";
            echo "<a href='start.php'>Go back</a>";
            return;
        }
        
        echo "<h1 style='color:#00ff00;font-weight:bold;border-bottom:1px solid #00ff00;'>Success</h1><br /><h3> Supervisor: $supername </h3><h3>Participant ID: $particID </h3><h3>Role: $userrole</h3>";
        
        $_SESSION['participantID'] = $particID;
        $_SESSION['supervisorName'] = $supername;
        $_SESSION['role'] = $userrole;
        
        echo "<ul class='testlinklist'>";
        
        if ($userrole == "Mentor") {
            echo "<li><a href='index.php?v=1&pID=$particID' target='_blank'>Mentor Orientation 1</li>";
            echo "<li><a href='index.php?v=2&pID=$particID' target='_blank'>Mentor Orientation 2</li>";
            echo "<li><a href='index.php?v=3&pID=$particID' target='_blank'>Mentor Test</a></li>";
        }
        else if ($userrole == "Mentee") {
            echo "<li><a href='index.php?v=4&pID=$particID' target='_blank'>Mentee Orientation</a></li>";
            echo "<li><a href='menteecreate.php?pID=$particID' target='_blank'>Mentee Test</a></li>";
        }
        
        echo '</ul>';
        echo '<br /><br />';
        echo '<p><a href="start.php">Finish Test</a></p>';
        
    }
?>
            </div>
        </div>
    </body>
</html>
