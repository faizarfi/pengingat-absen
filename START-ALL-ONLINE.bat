@echo off
title Starter Pengingat Absen BPS (Online Edition)
echo ======================================================
echo    PENGINGAT ABSENSI BPS - STARTER LENGKAP ONLINE
echo ======================================================
echo.
echo [1/5] Menjalankan Server Laravel (http://localhost:8000)...
start "Laravel Server" cmd /k "php artisan serve"

echo [2/5] Menjalankan Cloudflare Tunnel (HTTPS Online Publik)...
start "Cloudflare Tunnel" cmd /k "cloudflared.exe tunnel --url http://localhost:8000"

echo [3/5] Menjalankan WA Desktop Agent (Otomasi WhatsApp)...
start "WA Desktop Agent" cmd /k "cd wa-desktop-agent && python agent.py"

echo [4/5] Menjalankan Bot Telegram Admin (Remote Trigger HP)...
start "Telegram Bot Listener" cmd /k "php artisan telegram:poll"

echo [5/5] Menjalankan Laravel Scheduler (Cronjob Otomatis Setiap Menit)...
start "Laravel Scheduler" cmd /k "php artisan schedule:work"

echo.
echo ======================================================
echo  SEMUA LAYANAN SUDAH BERJALAN DI JENDELA TERPISAH!
echo  - Cek jendela "Cloudflare Tunnel" untuk melihat URL HTTPS publik Anda!
echo  - Telegram Bot: @bps_reminder_bot
echo  - Scheduler Otomatis: AKTIF (30 mnt sblm masuk/pulang)
echo ======================================================
pause
