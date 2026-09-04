@echo off
echo ========================================
echo   Riget Zoo Adventures - Starting Server
echo ========================================
echo.
echo Starting PHP development server...
echo Visit: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

cd /d "%~dp0"
php -S localhost:8000 -t public

pause
