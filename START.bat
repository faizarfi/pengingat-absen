@echo off
cd /d "%~dp0"
title Pengingat Absensi BPS - Launcher Terpadu

:MENU
cls
echo ======================================================
echo    PENGINGAT ABSENSI BPS - LAUNCHER UTAMA
echo ======================================================
echo.
echo   [1] Jalankan Semua Layanan Lokal (Standar)
echo       - Server Laravel (http://localhost:8000)
echo       - WA Desktop Agent (Background Mode)
echo       - Bot Telegram Admin
echo       - Laravel Scheduler Otomatis
echo.
echo   [2] Jalankan Semua + Online Publik (Cloudflare Tunnel)
echo       - Bisa diakses dari HP luar kantor via HTTPS
echo.
echo   [3] Jalankan Hanya WA Agent (Background Mode)
echo.
echo   [4] Jalankan Hanya Bot Telegram Admin
echo.
echo   [5] Buka Dashboard Admin di Web Browser
echo.
echo   [0] Keluar
echo.
echo ======================================================
set /p pilihan="Pilih menu (1-5, atau 0): "

if "%pilihan%"=="1" goto START_ALL
if "%pilihan%"=="2" goto START_ONLINE
if "%pilihan%"=="3" goto START_AGENT
if "%pilihan%"=="4" goto START_TELEGRAM
if "%pilihan%"=="5" goto OPEN_DASHBOARD
if "%pilihan%"=="0" exit
echo Pilihan tidak valid!
timeout /t 2 >nul
goto MENU

:START_ALL
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
goto MENU

:START_ONLINE
echo.
echo [1/5] Menjalankan Server Laravel (http://localhost:8000)...
start "Laravel Server" cmd /k "php artisan serve"
echo [2/5] Menjalankan Cloudflare Tunnel Online...
start "Cloudflare Tunnel" cmd /k "scripts\run-tunnel.bat"
echo [3/5] Menjalankan WA Desktop Agent (Background Mode)...
start "WA Desktop Agent" cmd /k "scripts\run-agent.bat"
echo [4/5] Menjalankan Bot Telegram Admin...
start "Telegram Bot Listener" cmd /k "scripts\run-telegram.bat"
echo [5/5] Menjalankan Laravel Scheduler Otomatis...
start "Laravel Scheduler" cmd /k "php artisan schedule:work"
echo.
echo ======================================================
echo  SEMUA LAYANAN ONLINE SUDAH BERJALAN!
echo  - Cek jendela "Cloudflare Tunnel" untuk URL HTTPS publik
echo ======================================================
pause
goto MENU

:START_AGENT
start "WA Desktop Agent" cmd /k "scripts\run-agent.bat"
goto MENU

:START_TELEGRAM
start "Telegram Bot Listener" cmd /k "scripts\run-telegram.bat"
goto MENU

:OPEN_DASHBOARD
start http://localhost:8000/admin
goto MENU
