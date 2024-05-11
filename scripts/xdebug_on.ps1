$phpIni = "C:\xampp\php\php.ini"
(Get-Content $phpIni) -replace 'xdebug.start_with_request\s*=\s*no', 'xdebug.start_with_request = yes' | Out-File -encoding ASCII $phpIni
Write-Output "Xdebug activado."