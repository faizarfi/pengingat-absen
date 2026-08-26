@echo off
title Telegram Bot Admin Listener
echo ======================================================
echo    BOT TELEGRAM ADMIN - PENGINGAT ABSENSI BPS
echo ======================================================
echo.
echo [INFO] Menghubungkan ke Telegram Bot (@bps_reminder_bot)...
echo [INFO] Tekan Ctrl+C untuk menghentikan listener.
echo.
php artisan telegram:poll
pause
