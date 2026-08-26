@echo off
title Cloudflare Tunnel - Pengingat Absen Online
echo ======================================================
echo    MEMBUAT WEBSITE PENGINGAT ABSEN BPS ONLINE
echo ======================================================
echo.
echo [INFO] Menghubungkan ke Cloudflare Tunnel...
echo [INFO] Tunggu beberapa detik sampai muncul URL https://...trycloudflare.com
echo [INFO] URL tersebut bisa langsung dibuka dari HP di mana saja!
echo.
cloudflared.exe tunnel --url http://localhost:8000
pause
