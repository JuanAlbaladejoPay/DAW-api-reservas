$phpIni = "C:\xampp\php\php.ini"
(Get-Content $phpIni) -replace '^(?!;)(.*zend_extension\s*=\s*xdebug.*)', ';$1' | Out-File -encoding ASCII $phpIni
Write-Output "Xdebug apagado."