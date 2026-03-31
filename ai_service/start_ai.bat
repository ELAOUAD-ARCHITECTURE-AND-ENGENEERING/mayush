@echo off
echo ===================================================
echo Mayush Visual Search AI - Microservice Setup runner
echo ===================================================

echo Step 1: Installing required Python packages...
pip install -r requirements.txt

echo.
echo Step 2: Starting the AI Background Service...
echo Please leave this window open while testing Visual Search!
python app.py
pause
