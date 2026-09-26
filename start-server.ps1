# Riget Zoo Adventures - Server Starter (PowerShell)

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Riget Zoo Adventures - Starting Server" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Starting PHP development server..." -ForegroundColor Green
Write-Host "Visit: http://localhost:8000" -ForegroundColor Yellow
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Red
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "To start the Stripe webhook server, run:" -ForegroundColor Green
Write-Host "stripe listen --forward-to localhost:8000/webhook/stripe" -ForegroundColor Cyan
Write-Host ""

Set-Location $PSScriptRoot
php -S localhost:8000 -t public
