SET phpIni="%~dp0php.ini-win"
php.exe -c %phpIni% tester -p php-cgi.exe -j 20 -log "%~dp0test.log" %*
