@echo off
setlocal
cd /d "%~dp0"

REM ---- Drawful v1 : double-click launcher for Windows ----
REM Keep this window open while you play. Close it to stop the game.

where node >nul 2>nul
if errorlevel 1 (
  echo.
  echo   Node.js is not installed, or not on your PATH.
  echo   Install the LTS build from https://nodejs.org  then run this file again.
  echo.
  pause
  exit /b 1
)

if not exist "node_modules" (
  echo.
  echo   First run: installing dependencies. This happens once...
  echo.
  call npm install
  if errorlevel 1 (
    echo.
    echo   npm install failed. Check your internet connection and try again.
    echo.
    pause
    exit /b 1
  )
)

REM Change the port here if 3000 is already in use.
set PORT=3000

echo.
echo   Starting Drawful v1 on port %PORT% ...
echo.
node server.js

echo.
echo   Server stopped.
pause
