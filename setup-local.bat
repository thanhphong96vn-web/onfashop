@echo off
echo ========================================
echo   ONFAShop - Local Setup Script
echo ========================================
echo.

REM Check if .env exists
if not exist .env (
    echo [INFO] Creating .env file...
    echo.
    echo Please create .env file manually using CREATE_ENV_FILE.md as reference
    echo.
    pause
    exit /b 1
) else (
    echo [OK] .env file exists
)

echo.
echo [STEP 1] Installing Composer dependencies...
call composer install
if errorlevel 1 (
    echo [ERROR] Composer install failed!
    pause
    exit /b 1
)

echo.
echo [STEP 2] Installing NPM dependencies...
call npm install
if errorlevel 1 (
    echo [ERROR] NPM install failed!
    pause
    exit /b 1
)

echo.
echo [STEP 3] Generating application key...
php artisan key:generate
if errorlevel 1 (
    echo [WARNING] Key generation failed. You may need to run: php artisan key:generate
)

echo.
echo [STEP 4] Creating storage link...
php artisan storage:link
if errorlevel 1 (
    echo [WARNING] Storage link creation failed. You may need to run: php artisan storage:link
)

echo.
echo [STEP 5] Clearing cache...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo.
echo ========================================
echo   Setup Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Make sure XAMPP Apache and MySQL are running
echo 2. Create database 'onfashop' in phpMyAdmin
echo 3. Run: npm run dev (for development)
echo 4. Access: http://localhost/onfashop
echo.
echo For detailed instructions, see SETUP_LOCAL.md
echo.
pause

