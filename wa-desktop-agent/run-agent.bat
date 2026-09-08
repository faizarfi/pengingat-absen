@echo off
cd /d "%~dp0"
title WA Desktop Agent v3.0 (Background Mode)
echo ======================================================
echo    WA DESKTOP AGENT v3.0 — PENGINGAT ABSENSI
echo ======================================================
echo.
echo [INFO] Menjalankan WA Desktop Agent (Background Mode)...
echo [INFO] Log harian tersimpan di folder logs/
echo.
node wa-agent.js
pause
