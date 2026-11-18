@echo off
setlocal
REM Local preview of the PHP site (repository root IS the site).

cd /d "%~dp0"

where php >nul 2>nul
if %ERRORLEVEL%==0 (
    echo Open http://127.0.0.1:8090/ in your browser. Stop with Ctrl+C.
    php -S 127.0.0.1:8090 -t "%CD%" "%CD%\router.dev.php"
    goto :eof
)

set PORTABLE=%LOCALAPPDATA%\Temp\opencode\php-8.4\php.exe
if exist "%PORTABLE%" (
    echo PHP not found in PATH; using portable PHP from Temp.
    echo Open http://127.0.0.1:8090/ in your browser. Stop with Ctrl+C.
    "%PORTABLE%" -c "%LOCALAPPDATA%\Temp\opencode\php-8.4\php.ini" -S 127.0.0.1:8090 -t "%CD%" "%CD%\router.dev.php"
    goto :eof
)

echo [ERROR] PHP not found in PATH or in Temp.
echo Install PHP 8 from https://windows.php.net/download/ ^(VS17 x64^),
echo add php.exe to PATH, and run this file again.
pause
