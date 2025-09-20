@echo off
REM Batch script to create databases and run migrations for all tenants
setlocal enabledelayedexpansion
set config_file=config\tenants.php
set php=php
set artisan=artisan

REM Get tenant names from config/tenants.php
for /f "tokens=4 delims='"" skip=0" %%A in ('findstr /C:"'name' =>" %config_file%') do (
    set tenant=%%A
    if not "!tenant!"=="" (
        echo Creating and migrating for tenant: !tenant!
        %php% %artisan% tenant:create !tenant!
        if errorlevel 1 (
            echo Error for tenant: !tenant!
            exit /b 1
        )
    )
)
echo All tenants processed.
endlocal