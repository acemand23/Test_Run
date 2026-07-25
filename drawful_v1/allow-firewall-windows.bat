@echo off
setlocal

REM ---- Drawful v1 : open the Windows Firewall so phones can connect ----
REM Run this ONCE. Right-click this file and choose "Run as administrator".
REM Change 3000 below if you changed the port in start-windows.bat.

set PORT=3000

net session >nul 2>nul
if errorlevel 1 (
  echo.
  echo   This needs administrator rights.
  echo   Right-click this file and choose "Run as administrator".
  echo.
  pause
  exit /b 1
)

netsh advfirewall firewall delete rule name="Drawful v1" >nul 2>nul
netsh advfirewall firewall add rule name="Drawful v1" dir=in action=allow protocol=TCP localport=%PORT%

echo.
echo   Done. Windows Firewall now allows phones on your Wi-Fi to reach port %PORT%.
echo.
pause
