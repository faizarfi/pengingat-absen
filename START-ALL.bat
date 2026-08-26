@echo off
title Starter Pengingat Absen BPS
echo ======================================================
echo    PENGINGAT ABSENSI BPS - STARTER LENGKAP
echo ======================================================
echo.
echo [1/4] Menjalankan Server Laravel (http://localhost:8000)...
start "Laravel Server" cmd /k "php artisan serve"

echo [2/4] Menjalankan WA Desktop Agent (Otomasi WhatsApp)...
start "WA Desktop Agent" cmd /k "cd wa-desktop-agent && python agent.py"

echo [3/4] Menjalankan Bot Telegram Admin (Remote Trigger HP)...
start "Telegram Bot Listener" cmd /k "php artisan telegram:poll"

echo [4/4] Menjalankan Laravel Scheduler (Cronjob Otomatis Setiap Menit)...
start "Laravel Scheduler" cmd /k "php artisan schedule:work"

echo.
echo ======================================================
echo  SEMUA LAYANAN SUDAH BERJALAN DI JENDELA TERPISAH!
echo  - Dashboard: http://localhost:8000/admin
echo  - Telegram Bot: @bps_reminder_bot
echo  - Scheduler Otomatis: AKTIF (30 mnt sblm masuk/pulang)
echo ======================================================
pause
