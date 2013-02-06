<?php
    require_once('setup.php');
    require_once(INC_DIR . 'config.inc.php');
    require_once(INC_DIR . 'header.php');
    require_once(INC_DIR . 'functions.inc.php');
    
?>

<style>
   
    h1 {
        font-family: Tahoma, sans-serif;
        font-size: 24px;
        padding: 20px;
    }
    h3 {
        font-family: Tahoma, sans-serif;
        font-size: 16px;
        line-height: 150%;
        padding-left: 20px;
    }
    .infotext {
        padding-left: 45px;
        line-height: 2em;
        font-size: 1.2em;
        font-family: 'Tahoma, Arial, Helvetica, sans-serif';
        color: #333;
    }
    .infotext span {
        color: #008000;
        font-weight: bold;
        border-bottom: 1px solid #008000;
        padding: 0 10px;
    }
    .infotext span.important {
        color: #f00;
        font-weight: bold;
        border-bottom: 1px solid red;
        padding: 5px;
        margin: 10px 0;
    }
    #start-form {
        position: relative;
        left: 15%;
        margin-top: 45px;
        font-size: 1.2em;
    }
    #start-form input[type='text'] {
        padding-left: 10px;
        padding-right: 10px;
        color: #444;
    }
    #start-form label, input {
        height: 25px;
        margin: 10px 0;
    }
    #start-form label {
        display: block;
        max-width: 400px;
        min-width: 300px;
        float: left;
    }
    #start-form input, select {
        min-width: 300px;
        max-width: 400px;
        font-size: 1.2em;
    }
    #start-form select {
        font-size: 1.0em;
    }
    #start-form input[type='submit'] {
        margin-top: 35px; 
        background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(rgb(180, 255, 180)), to(rgb(10,255,10)));
        display: inline-block;
        padding: 5px 30px 30px;
        color: #eee;
        text-decoration: none;
        font-weight: bold;
        line-height: 1.2em;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        -moz-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
        -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
        text-shadow: 0 -1px 5px rgba(0,0,0,0.8);
        border-bottom: 1px solid rgba(0,0,0,0.25);
        position: relative;
        cursor: pointer;
    }
    
</style>

<div class="container">
    <img src="images/eku.jpg" title="Eastern Kentucky University" alt="Eastern Kentucky University" style="width:10%;padding-left:15%;" />
    <img src="images/imdclogosmall.png" title="Inclusive Media and Design Centre" alt="IMDC lab at Ryerson University" style="width:10%;clear:both;padding-left:15%;" />
    <br />
    <h1>Start a new TerpTube test</h1>
    <div class="infotext">
    <?php 
        if ( isset($_SESSION['participantID']) ) {
            echo "<p>You currently have a participant id of: <span>" . $_SESSION['participantID'] . "</span> under supervisor: <span>" . $_SESSION['supervisorName'] . "</span> acting as a: <span>" . $_SESSION['role'] . "</span></p>";
            echo "<p><span class='important'>Pressing submit will clear these values!</span></p>";
        } 
        else {
            echo '<p>You currently are <span class="important">not</span> assigned a participand ID or a supervisor</p>'; 
        }
    ?>
    </div>
    <form id="start-form" action="init.php" enctype="multipart/form-data" method="post">
        <label>Enter the supervisor's name</label>
        <input type="text" id="supervisor" name="supervisor" required><br />
        <label>Enter the particpant's id:</label>
        <input type="number" size="10" id="participantid" min="4" max="2147483647" name="participantid" required><br />
        <label>Select the role of the participant</label>
        <select name="role">
            <option value="Mentor">Mentor</option>
            <option value="Mentee">Mentee</option>
        </select>
        <br />
        <input type="submit" value="Submit">
    </form>

    <script>
        $(document).ready( function() {
            
        });
    </script>

</div> <!-- end container -->
</html>