@echo off
echo ============================================
echo  BlogVerse Pro - Running Tests with Coverage
echo ============================================

set XDEBUG_MODE=coverage
php artisan config:clear --ansi
php artisan test --coverage --min=100 %*

echo ============================================
echo  Done.
echo ============================================
