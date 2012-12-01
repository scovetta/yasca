<%@ page language="java" %>
<html>
    <head>
    </head>

    <body>
	<% if ("true".equalsIgnoreCase(request.getParameter("debug"))) { %>
	    Some debugging information here.
	<% } %>
    </body>
</html>