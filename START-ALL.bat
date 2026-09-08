@echo off
cd /d "%~dp0"
title Starter Pengingat Absen BPS
echo ======================================================
echo    PENGINGAT ABSENSI BPS - STARTER LENGKAP
echo ======================================================
echo.
echo [1/4] Menjalankan Server Laravel (http://localhost:8000)...
start "Laravel Server" cmd /k "php artisan serve"

echo [2/4] Menjalankan WA Desktop Agent (Background Mode)...
start "WA Desktop Agent" cmd /k "scripts\run-agent.bat"

echo [3/4] Menjalankan Bot Telegram Admin...
start "Telegram Bot Listener" cmd /k "scripts\run-telegram.bat"

echo [4/4] Menjalankan Laravel Scheduler Otomatis...
start "Laravel Scheduler" cmd /k "php artisan schedule:work"

echo.
echo ======================================================
echo  SEMUA LAYANAN SUDAH BERJALAN DI JENDELA TERPISAH!
echo  - Dashboard: http://localhost:8000/admin
echo  - Telegram Bot: @bps_reminder_bot
echo ======================================================
pause
