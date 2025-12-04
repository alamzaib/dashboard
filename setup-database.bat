@echo off
echo Setting up MySQL database for Dashboard...
echo.

REM Update .env file
php setup-mysql.php

echo.
echo Creating database...
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Error: Could not create database. Please make sure MySQL is running in XAMPP.
    echo You can also create the database manually using phpMyAdmin or MySQL command line.
    pause
    exit /b 1
)

echo.
echo Database created successfully!
echo.
echo Running migrations...
php artisan migrate

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Error: Migration failed. Please check your database configuration.
    pause
    exit /b 1
)

echo.
echo Creating test users...
php artisan db:seed --class=TestUsersSeeder

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Error: Seeding failed.
    pause
    exit /b 1
)

echo.
echo ========================================
echo Setup completed successfully!
echo ========================================
echo.
echo Created 20 test users:
echo   Email format: testuser1@example.com to testuser20@example.com
echo   Password: test1234
echo.
pause

