<%@page import="java.io.File"%>
<%@ page language="java" contentType="text/html; charset=ISO-8859-1"
	pageEncoding="ISO-8859-1"%>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>Recorded Videos</title>
<link rel="stylesheet"
	href="jscripts/fancybox/jquery.fancybox-1.3.4.css" type="text/css"
	media="screen" />
<script type="text/javascript" src="jscripts/jquery-1.4.3.min.js"></script>
<script type="text/javascript"
	src="jscripts/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript"
	src="jscripts/fancybox/jquery.easing-1.3.pack.js"></script>
</head>
<body>
	<h1>Recorded videos:</h1>
	<div id="vid-list" class="centered">
		<div id="vid-list-container" class="centered">
			<%
			File dir = new File("streams");
			File[] files = dir.listFiles();

			for (File file : files)
			{
			%>
				<div class="vid-div">
				<object width="600" height="320" data="http://releases.flowplayer.org/swf/flowplayer-3.2.11.swf" type="application/x-shockwave-flash">
					<param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.11.swf" />
					<param name="allowfullscreen" value="true" />
					<param name="allowscriptaccess" value="always" />
					<param name="flashvars" value='config={"clip":{"url":"<% file.getPath(); %>"}]}' />
				</object>
			</div>
			<%
			}
            %>
		</div>
		<!-- end vid-list-container div -->
	</div>
</body>
</html>