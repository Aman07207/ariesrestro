@echo off
REM Starts the Reverb WebSocket server for live chef/waiter/customer updates.
REM Keep this window open. Without it the app still works via 10s auto-refresh.
cd /d "%~dp0"
php artisan reverb:start --debug
