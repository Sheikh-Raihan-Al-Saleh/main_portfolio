@echo off
setlocal
title Portfolio Studio Launcher

REM ============================================================
REM  One-click launcher: starts Laravel + Vite (if not already
REM  running), waits for the app to respond, then opens browser.
REM ============================================================

set "APPDIR=%~dp0"

REM --- 1. Start Laravel (php artisan serve) if port 8000 is free ---
netstat -ano | findstr /R /C:":8000 .*LISTENING" >nul 2>&1
if errorlevel 1 (
    start "Portfolio - Laravel" /min cmd /c "cd /d "%APPDIR%" && php artisan serve --host=127.0.0.1 --port=8000"
)

REM --- 2. Start Vite dev server if it is not already running ---
netstat -ano | findstr /R /C:":517[0-9] .*LISTENING" >nul 2>&1
if errorlevel 1 (
    REM Remove a stale hot marker (left over from a previous Vite run)
    REM so Laravel falls back to the built assets in public/build.
    if exist "%APPDIR%public\hot" (
        del "%APPDIR%public\hot" >nul 2>&1
    )
    start "Portfolio - Vite" /min cmd /c "cd /d "%APPDIR%" && npm run dev"
)

REM --- 3. Wait for Laravel to answer (up to 30 seconds) ---
set /a tries=0

:waitloop
powershell -NoProfile -Command "try { Invoke-WebRequest -Uri 'http://127.0.0.1:8000' -UseBasicParsing -TimeoutSec 2 | Out-Null; exit 0 } catch { exit 1 }" >nul 2>&1
if not errorlevel 1 goto open

set /a tries+=1
if %tries% geq 30 goto failed
timeout /t 1 /nobreak >nul
goto waitloop

:open
start "" http://127.0.0.1:8000/
exit /b 0

:failed
echo.
echo Laravel did not respond within 30 seconds.
echo Check the minimized "Portfolio - Laravel" window for errors.
echo.
pause
exit /b 1
