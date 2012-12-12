<?php
    require_once('setup.php');
    require_once(INC_DIR . 'config.inc.php');
    require_once(INC_DIR . 'header.php');
    require_once(INC_DIR . 'functions.inc.php');
    
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
    
</style>

<div class="container">
    
    <h1>Start a new test</h1>
    <?php 
        if ( isset($_SESSION['participantID']) ) {
            echo "<h3>You currently have a participant id of: " . $_SESSION['participantID'] . " under supervisor: " . $_SESSION['supervisorName'] . " acting as a: " . $_SESSION['role'] . "</h3>";
            echo "<h3>Pressing submit will clear these values</h3>";
        } 
        else {
            echo '<h3>You currently are not assigned a participand ID or a supervisor</h3>'; 
        }
    ?>
    
    <form id="start-form" action="init.php" enctype="multipart/form-data" method="post">
        <label>Enter the supervisor's name</label>
        <input type="text" id="supervisor" name="supervisor" required>
        <label>Enter the particpant's id:</label>
        <input type="number" size="10" id="participantid" min="4" name="participantid" required>
        <select name="role">
            <option value="Mentor">Mentor</option>
            <option value="Mentee">Mentee</option>
        </select>
        <input type="submit" value="submit">
    </form>

    <script>
        $(document).ready( function() {
             
        });
    </script>

</div> <!-- end container -->

