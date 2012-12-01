@echo off
cd %~dp0
set mypath=%~dp0
java -Xmx500m -Xms500m -Dpixy.home="%mypath%\" -jar pixy.jar -a -A -y xss:file:sql %*
del graphs\*.dot > nul 2>&1
del graphs\*.txt > nul 2>&1
