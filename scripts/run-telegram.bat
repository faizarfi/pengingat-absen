@echo off
cd /d "%~dp0.."
title Bot Telegram Admin Listener

if exist "C:\xampp\php\php.exe" (
    set "PHPRC=C:\xampp\php"
    set "PATH=C:\xampp\php;%PATH%"
)
echo ======================================================
echo    BOT TELEGRAM ADMIN - PENGINGAT ABSENSI BPS
echo ======================================================
echo.
echo [INFO] Menjalankan listener Telegram (Polling)...
echo [INFO] Bot siap menerima perintah dari HP Admin.
echo.
php artisan telegram:poll
pause
