@echo off
REM IMS Database Export Script for Windows
REM This script exports your database for deployment

echo ========================================
echo IMS Database Export Script
echo ========================================
echo.

REM Set variables
set DB_HOST=localhost
set DB_USER=root
set DB_NAME=tappomarket_ims
set BACKUP_DIR=c:\xampp\htdocs\tappomarket\ims\database_backup
set TIMESTAMP=%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set BACKUP_FILE=%BACKUP_DIR%\ims_backup_%TIMESTAMP%.sql

REM Create backup directory if it doesn't exist
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo Exporting database...
echo Database: %DB_NAME%
echo Backup file: %BACKUP_FILE%
echo.

REM Export database with UTF-8mb4 encoding
c:\xampp\mysql\bin\mysqldump -u %DB_USER% -h %DB_HOST% --default-character-set=utf8mb4 --routines --triggers --single-transaction %DB_NAME% > "%BACKUP_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo Database exported successfully!
    echo ========================================
    echo.
    echo Backup file: %BACKUP_FILE%
    echo File size: 
    dir "%BACKUP_FILE%" | find "ims_backup"
    echo.
    echo You can now upload this file to your server.
) else (
    echo.
    echo ========================================
    echo Error: Database export failed!
    echo ========================================
    echo.
    echo Please check:
    echo 1. MySQL is running
    echo 2. Database name is correct
    echo 3. User has proper permissions
)

echo.
pause
