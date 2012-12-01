<?

?><?= $_GET["foo"] ?><?



echo $PHP_SELF;     // dangerous!
echo $_SERVER['PHP_SELF'];  // dangerous!
echo $HTTP_SERVER_VARS['PHP_SELF'];         // dangerous!

echo $_SERVER['EVIL'];      // dangerous!

