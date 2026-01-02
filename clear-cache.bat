@echo off
echo ========================================
echo     CLEAR CACHE LARAVEL
echo ========================================

cd /d C:\xampp\htdocs\onfashop

echo.
echo Clearing cache...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo.
echo ========================================
echo        DONE!
echo ========================================
pause

