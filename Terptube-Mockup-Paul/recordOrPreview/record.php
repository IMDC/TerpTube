<?php
require_once("../setup.php");
    if (isset($_POST["flashVars"]) && $_POST["flashVars"]!="")
{
	$flashVars = $_POST["flashVars"];	
	$flashVarsParsed = "";
	foreach ($flashVars as $key => $value) {
		$flashVarsParsed.=$key."=".$value."&";
	}
	$flashVarsParsed = substr($flashVarsParsed,0, strlen($flashVarsParsed)-1);
}
?>

				<div id="flashContent">
	            <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="620" id="WebcamRecorderClient">
	                <param name="movie" value="<?php echo SITE_BASE ?>/recordOrPreview/WebcamRecorderClient.swf" />
	                <param name="quality" value="high" />
	                <param name="bgcolor" value="#ffffff" />
	                <param name="allowScriptAccess" value="sameDomain" />
	                <param name="allowFullScreen" value="true" />
	                <param name="FlashVars" value='<?php echo $flashVarsParsed?>' />
	                <param name="wMode" value="transparent" />
	                <!--[if !IE]>-->
	                <object type="application/x-shockwave-flash" data="<?php echo SITE_BASE ?>/recordOrPreview/WebcamRecorderClient.swf" width="100%" height="620">
	                    <param name="quality" value="high" />
	                    <param name="bgcolor" value="#ffffff" />
	                    <param name="allowScriptAccess" value="sameDomain" />
	                    <param name="allowFullScreen" value="true" />
	                    <param name="FlashVars" value='<?php echo $flashVarsParsed?>' />
	                    <param name="wMode" value="transparent" />
	                <!--<![endif]-->
	                <!--[if gte IE 6]>-->
	                	<p> 
	                		Either scripts and active content are not permitted to run or Adobe Flash Player version
	                		11.1.0 or greater is not installed.
	                	</p>
	                <!--<![endif]-->
	                    <a href="http://www.adobe.com/go/getflashplayer">
	                        <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash Player" />
	                    </a>
	                <!--[if !IE]>-->
	                </object>
	                <!--<![endif]-->
	            </object>
	        </div>