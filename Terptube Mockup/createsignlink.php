<?php

	include('include/createsignlink_header.php');

?>

	
	
	<div id="file-uploader-demo1">	
		<p>Upload a file</p>	
		<noscript>			
			<p>Please enable JavaScript to use file uploader.</p>
			<!-- or put a simple form for upload here -->
		</noscript>         
	</div>
	
	<!--------------------------VIDEO BELOW ---------------------->
	<div class="source-media-container" style="width:auto;height:auto;" >
                    <video class="video" id="comment-video" width="320" height="240" controls="controls" >
  				      Your browser does not support the video tag.
	                </video>
	               <div style="clear:both"></div>		
	</div>
	
	<!----------------canvas ----------------------------------------------->
	            
	             <div>

					<canvas  id="canvas" width="320" height="20" stlye="background:#f7f5f6;float:left;"> <!-- style="display:none;" -->

							This text is displayed if your browser does not support HTML5 Canvas.

					</canvas>

				 </div>
                
	<!--------------Add signlink form ----------->
	            <form style="margin-top:30px;">
	            	 <input type="button" id="toggle-sl-creation" value="Create Signlink" style="margin-bottom:10px;float:left">
	                   <div id="add-signlink">
	                   		<label>Start Time:</label>
	                   		<input type="text" name="signlink-start" size="6">
	                   		<label>End Time:</label>
	                   		<input type="text" name="signlink-end" size="6"><br/><br/>
	                   		<label>Associate Link:</label>
	                   		<input type="text" name="signlink-link"><br/>
	                   		<input type="button" value="Post" id="postSignlink">
	                   </div>
	                   <div style="clear:both"></div>
	                    <!--  description of video text ---->	
	                	<input tyle="text" class="video-text-description" />
	                	
	            </form>
   
    
</body>
</html>