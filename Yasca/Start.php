<?php
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    print <<<'EOT'
Sorry, but Yasca requires PHP 5.4.0 or later with multibyte support.

    Windows: http://windows.php.net/download/
      Linux: Use your distribution's package manager
    Mac OSX: Use brew, port, to install from PHP source code


EOT;
    exit(1);
} else {
    include('Main.php');
}
?>