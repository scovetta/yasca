<?

$a = $_GET['a'];
mysql_query("select * from foo where t = '$a'");

$sql = "SELECT * FROM table WHERE field ='$_GET[\"input\"]'";

$sql = "SELECT * FROM table WHERE field =" . $_GET["input"];

\mysql_query("SELECT SomeStoredProcName('$_GET[\"input\"])'");

\mysql_query("SELECT SomeStoredProcName( '" . $_GET["input"] . "') ");

\mysql_query("SELECT SomeStoredProcName( '{$_GET['input']}') ");
