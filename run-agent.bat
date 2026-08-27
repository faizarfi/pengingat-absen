@echo off
cd /d "%~dp0wa-desktop-agent"
title WA Desktop Agent Runner
echo ======================================================
echo    WA DESKTOP AGENT — PENGINGAT ABSENSI OTOMATIS
echo ======================================================
echo.
echo [INFO] Menjalankan WA Desktop Agent...
python agent.py
pause
