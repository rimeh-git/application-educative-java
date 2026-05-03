@echo off
echo ========================================
echo Execution de PHPStan niveau 8
echo ========================================
echo.

vendor\bin\phpstan analyse --memory-limit=1G

echo.
echo ========================================
echo Analyse terminee
echo ========================================
pause
