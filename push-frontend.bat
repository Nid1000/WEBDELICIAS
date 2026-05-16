@echo off
cd /d C:\Users\usuario\Downloads\WEBNIDA

echo.
echo ===== COMENZANDO PUSH DEL FRONTEND =====
echo.

echo 1. Verificando estado actual...
git status --short

echo.
echo 2. Cambiando a rama WEBNIDA-FRONTEND...
git checkout WEBNIDA-FRONTEND

echo.
echo 3. Añadiendo cambios del frontend...
git add frontend/

echo.
echo 4. Verificando cambios a subir...
git diff --cached --name-only | find /c /v ""

echo.
echo 5. Confirmando cambios...
git commit -m "Actualización completa del frontend - WEBNIDA" -m "Co-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>"

echo.
echo 6. Subiendo a GitHub...
git push origin WEBNIDA-FRONTEND

echo.
echo ===== PUSH COMPLETADO EXITOSAMENTE =====
echo.
echo Últimos 5 commits:
git log --oneline -5

echo.
echo URL de la rama: https://github.com/Nid1000/APPNI/tree/WEBNIDA-FRONTEND
echo.
pause
