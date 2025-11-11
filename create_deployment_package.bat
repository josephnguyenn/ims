@echo off
REM IMS Deployment Package Creator
REM Creates a deployment-ready package of your application

echo ========================================
echo IMS Deployment Package Creator
echo ========================================
echo.

set PROJECT_DIR=c:\xampp\htdocs\tappomarket\ims
set PACKAGE_DIR=c:\xampp\htdocs\tappomarket\ims_deployment_package
set TIMESTAMP=%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%

echo Creating deployment package...
echo.

REM Create package directory
if not exist "%PACKAGE_DIR%" mkdir "%PACKAGE_DIR%"

REM Clean up old packages
if exist "%PACKAGE_DIR%\ims_deployment_%TIMESTAMP%.zip" del "%PACKAGE_DIR%\ims_deployment_%TIMESTAMP%.zip"

echo Step 1: Cleaning up temporary files...
cd /d "%PROJECT_DIR%"

REM Remove development files
if exist "storage\logs\*.log" del /q "storage\logs\*.log"
if exist "bootstrap\cache\*.php" del /q "bootstrap\cache\*.php"

echo Step 2: Installing production dependencies...
call composer install --optimize-autoloader --no-dev --quiet

echo Step 3: Exporting database...
call export_database.bat

echo Step 4: Creating deployment archive...
echo.
echo Please manually create a ZIP file with these files/folders:
echo.
echo INCLUDE:
echo   - app/
echo   - bootstrap/
echo   - config/
echo   - database/ (including the backup folder)
echo   - public/
echo   - resources/
echo   - routes/
echo   - storage/
echo   - .env.production
echo   - artisan
echo   - composer.json
echo   - composer.lock
echo   - DEPLOYMENT.md
echo   - DEPLOYMENT_CHECKLIST.md
echo   - DEPLOYMENT_READY.md
echo   - deploy.sh
echo.
echo EXCLUDE:
echo   - .git/
echo   - .gitignore
echo   - node_modules/
echo   - vendor/
echo   - .env (will be created on server)
echo   - tests/
echo   - phpunit.xml
echo.
echo Recommended: Use 7-Zip or WinRAR to create the archive
echo.
echo ========================================
echo Package preparation completed!
echo ========================================
echo.
echo Next steps:
echo 1. Create ZIP file as described above
echo 2. Review DEPLOYMENT_READY.md
echo 3. Follow DEPLOYMENT.md guide
echo 4. Upload to your server
echo.
pause
