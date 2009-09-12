@echo off
taskkill /im soffice.bin /f
taskkill /im soffice.exe /f
start "soffice" "C:/OpenOffice.org/program/soffice.exe" -headless -norestore -accept="socket,host=localhost,port=2002;urp;"