$phpIni = "C:\xampp\php\php.ini"
(Get-Content $phpIni) -replace 'xdebug.start_with_request\s*=\s*yes', 'xdebug.start_with_request = no' | Out-File -encoding ASCII $phpIni
Write-Output "Xdebug desactivado."