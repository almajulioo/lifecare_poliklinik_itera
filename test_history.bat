@echo off
REM Test /app/history-debug page
curl -s "http://127.0.0.1:8000/app/history-debug" > history_response.html
echo Check history_response.html for page content
