@echo off
title Phupha Club - Auto Cloudflare Tunnel
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0run_tunnel.ps1"
pause
