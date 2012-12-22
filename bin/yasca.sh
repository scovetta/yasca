#!/bin/sh

# Detect the operating system (Linux vs. OSX)
lowercase(){
  echo "$1" | sed "y/ABCDEFGHIJKLMNOPQRSTUVWXYZ/abcdefghijklmnopqrstuvwxyz/"
}

OS=`lowercase \`uname\``
if [ "$OS" == "darwin" ]; then
  PHP=bin/php_osx
else
  PHP=php
fi

ROOT=$(cd "$(dirname "$0")"; pwd)
cd $ROOT/..

# Start Yasca
$PHP Yasca/Start.php $*
