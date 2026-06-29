@echo off
REM ============================================================
REM  Sky Finance - Backup automatico do banco de dados
REM  Gera um .sql completo (estrutura + dados) via mysqldump,
REM  com rotacao automatica dos arquivos antigos.
REM
REM  Agende no Agendador de Tarefas do Windows (ver instrucoes
REM  no final deste arquivo).
REM ============================================================

REM ----------------------- CONFIGURACAO -----------------------
REM Caminho do mysqldump (XAMPP padrao)
set "MYSQLDUMP=C:\xampp\mysql\bin\mysqldump.exe"

REM Credenciais do banco (iguais ao conn/conn.php)
set "DB_USER=root"
set "DB_PASS="
set "DB_NAME=projeto"

REM Pasta onde os backups serao salvos.
REM DICA: para sobreviver a falha de disco, aponte para uma pasta
REM sincronizada na nuvem, ex: "%USERPROFILE%\OneDrive\SkyFinanceBackups"
set "BACKUP_DIR=C:\SkyFinanceBackups"

REM Quantos backups manter (40 = ~10 dias com backup a cada 6h)
set "MANTER=40"
REM ------------------------------------------------------------

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

REM Timestamp no formato AAAA-MM-DD_HH-MM-SS (independente do locale)
for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd_HH-mm-ss"') do set "STAMP=%%i"

set "ARQUIVO=%BACKUP_DIR%\skyfinance_%STAMP%.sql"

REM Executa o dump (--result-file grava no encoding correto, sem mojibake)
if "%DB_PASS%"=="" (
  "%MYSQLDUMP%" -u %DB_USER% --databases %DB_NAME% --single-transaction --routines --events --default-character-set=utf8mb4 --result-file="%ARQUIVO%"
) else (
  "%MYSQLDUMP%" -u %DB_USER% -p%DB_PASS% --databases %DB_NAME% --single-transaction --routines --events --default-character-set=utf8mb4 --result-file="%ARQUIVO%"
)

if errorlevel 1 (
  echo [%STAMP%] ERRO ao gerar backup >> "%BACKUP_DIR%\backup.log"
) else (
  echo [%STAMP%] OK: skyfinance_%STAMP%.sql >> "%BACKUP_DIR%\backup.log"
)

REM Rotacao: apaga os mais antigos, mantendo apenas os %MANTER% recentes
for /f "skip=%MANTER% delims=" %%f in ('dir /b /o-d "%BACKUP_DIR%\skyfinance_*.sql" 2^>nul') do del "%BACKUP_DIR%\%%f"

exit /b 0

REM ============================================================
REM  COMO AGENDAR (a cada 6 horas)
REM  ------------------------------------------------------------
REM  Abra o Prompt de Comando COMO ADMINISTRADOR e rode:
REM
REM    schtasks /Create /TN "SkyFinance Backup" /TR "C:\xampp\htdocs\Sky_Finance\sql\backup_auto.bat" /SC HOURLY /MO 6 /ST 00:00 /F
REM
REM  Isso roda as 00:00, 06:00, 12:00 e 18:00 todos os dias.
REM  Para remover:  schtasks /Delete /TN "SkyFinance Backup" /F
REM  Para testar agora:  schtasks /Run /TN "SkyFinance Backup"
REM
REM  COMO RESTAURAR um backup:
REM    C:\xampp\mysql\bin\mysql.exe -u root < "C:\SkyFinanceBackups\skyfinance_AAAA-MM-DD_HH-MM-SS.sql"
REM  (o arquivo ja recria o banco 'projeto' com CREATE DATABASE IF NOT EXISTS)
REM ============================================================
