@echo off
title Starter Pengingat Absen BPS
echo ======================================================
echo    PENGINGAT ABSENSI BPS - STARTER LENGKAP
echo ======================================================
echo.
echo [1/3] Menjalankan Server Laravel (http://localhost:8000)...
start "Laravel Server" cmd /k "php artisan serve"

echo [2/3] Menjalankan WA Desktop Agent (Otomasi WhatsApp)...
start "WA Desktop Agent" cmd /k "cd wa-desktop-agent && python agent.py"

echo [3/3] Menjalankan Bot Telegram Admin (Remote Trigger HP)...
start "Telegram Bot Listener" cmd /k "php artisan telegram:poll"

echo.
echo ======================================================
echo  SEMUA LAYANAN SUDAH BERJALAN DI JENDELA TERPISAH!
echo  - Dashboard: http://localhost:8000/admin
echo  - Telegram Bot: @bps_reminder_bot
echo ======================================================
pause
