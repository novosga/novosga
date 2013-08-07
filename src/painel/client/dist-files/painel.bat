::
:: NovoSGA - Painel Client
::
:: Script bat para verificar disponibilidade da rede antes
:: de executar o painel. Usado quando precisar executar o 
:: painel automaticamente apos logon.
::
:: @author rogeriolino
::

@echo off


:: pegando default gateway
@For /f "tokens=3" %%* in (
   'route.exe print ^|findstr "\<0.0.0.0\>"'
   ) Do @Set "gateway=%%*"

echo gateway

setlocal enableextensions enabledelayedexpansion
set /a "x = 1"
:: maximo de tentativas
set limit=99999

echo Verificando conexao de rede

:loop
ping.exe %gateway% -n 1
if not errorlevel 1 (
    goto sucesso
)
set /a "x = x + 1"
if %x% leq %limit% (
    goto loop
) else (
    goto erro
)

:erro
echo Rede indisponivel.
goto fim

:sucesso
echo Rede OK! Chamando jar
start javaw -splash:data/ui/img/splash.png -jar {distname}.jar
exit 0

:fim
