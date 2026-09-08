@echo off
cd /d "%~dp0..\wa-desktop-agent"
title WA Desktop Agent v3.0 (Background Mode)
echo ======================================================
echo    WA DESKTOP AGENT v3.0 - PENGINGAT ABSENSI
echo ======================================================
echo.
echo [INFO] Menjalankan agent di background via whatsapp-web.js...
echo [INFO] Log harian tersimpan di folder wa-desktop-agent\logs\
echo.
node wa-agent.js
pause
