@echo off
echo ========================================
echo Menu PHPStan - Analyse de code
echo ========================================
echo.
echo 1. Analyse complete (strict)
echo 2. Analyse complete (avec exclusions)
echo 3. Analyse Controllers uniquement
echo 4. Analyse Services uniquement
echo 5. Analyse Entities uniquement
echo 6. Analyse Repositories uniquement
echo 7. Comparer strict vs avec exclusions
echo 8. Quitter
echo.
set /p choice="Choisissez une option (1-8): "

if "%choice%"=="1" goto strict
if "%choice%"=="2" goto ignores
if "%choice%"=="3" goto controllers
if "%choice%"=="4" goto services
if "%choice%"=="5" goto entities
if "%choice%"=="6" goto repositories
if "%choice%"=="7" goto compare
if "%choice%"=="8" goto end

:strict
echo.
echo ========================================
echo Analyse complete STRICTE
echo ========================================
vendor\bin\phpstan analyse --memory-limit=1G --error-format=table
goto end

:ignores
echo.
echo ========================================
echo Analyse complete AVEC EXCLUSIONS
echo ========================================
vendor\bin\phpstan analyse -c phpstan-with-ignores.neon --memory-limit=1G --error-format=table
goto end

:controllers
echo.
echo ========================================
echo Analyse CONTROLLERS uniquement
echo ========================================
vendor\bin\phpstan analyse src/Controller --memory-limit=1G --error-format=table
goto end

:services
echo.
echo ========================================
echo Analyse SERVICES uniquement
echo ========================================
vendor\bin\phpstan analyse src/Service --memory-limit=1G --error-format=table
goto end

:entities
echo.
echo ========================================
echo Analyse ENTITIES uniquement
echo ========================================
vendor\bin\phpstan analyse src/Entity --memory-limit=1G --error-format=table
goto end

:repositories
echo.
echo ========================================
echo Analyse REPOSITORIES uniquement
echo ========================================
vendor\bin\phpstan analyse src/Repository --memory-limit=1G --error-format=table
goto end

:compare
echo.
echo ========================================
echo Comparaison STRICT vs AVEC EXCLUSIONS
echo ========================================
echo.
echo --- STRICT ---
vendor\bin\phpstan analyse --memory-limit=1G 2>&1 | findstr /C:"Found" /C:"errors" /C:"No errors"
echo.
echo --- AVEC EXCLUSIONS ---
vendor\bin\phpstan analyse -c phpstan-with-ignores.neon --memory-limit=1G 2>&1 | findstr /C:"Found" /C:"errors" /C:"No errors"
goto end

:end
echo.
echo ========================================
echo Analyse terminee
echo ========================================
pause
