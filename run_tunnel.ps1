# run_tunnel.ps1
$root = $PSScriptRoot
Set-Location $root

Write-Host "==========================================================" -ForegroundColor Green
Write-Host "  Phupha Club - Starting Cloudflare Tunnel" -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Green
Write-Host ""

$cloudflared = Join-Path $root "cloudflared.exe"
if (-not (Test-Path $cloudflared)) {
    Write-Host "[INFO] Downloading cloudflared.exe..." -ForegroundColor Yellow
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile $cloudflared -UseBasicParsing
}

Write-Host "Starting tunnel on port 80..." -ForegroundColor Cyan

$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.FileName = $cloudflared
$psi.Arguments = "tunnel --url http://localhost:80"
$psi.RedirectStandardError = $true
$psi.UseShellExecute = $false
$psi.CreateNoWindow = $true

$proc = [System.Diagnostics.Process]::Start($psi)

$tunnelUrl = ""
$synced = $false

while (-not $proc.HasExited) {
    $line = $proc.StandardError.ReadLine()
    if ($line) {
        Write-Host $line
        if (-not $synced -and $line -match 'https://([a-zA-Z0-9-]+\.trycloudflare\.com)') {
            $synced = $true
            $tunnelUrl = "https://" + $matches[1] + "/phupha-club/web_code"
            
            Write-Host ""
            Write-Host ">>> SERVER URL: $tunnelUrl" -ForegroundColor Yellow
            Write-Host ">>> Syncing to GitHub..." -ForegroundColor Yellow
            
            $tunnelTxt = Join-Path $root "current_tunnel.txt"
            [System.IO.File]::WriteAllText($tunnelTxt, $tunnelUrl)
            
            git add current_tunnel.txt
            git commit -m "Auto-update tunnel URL"
            git push origin main
            
            Write-Host ""
            Write-Host "==========================================================" -ForegroundColor Green
            Write-Host "  SUCCESS! Cloud URL auto-synced to mobile app!" -ForegroundColor Green
            Write-Host "  Web Link: $tunnelUrl" -ForegroundColor Cyan
            Write-Host "  (Keep this window open while using the server)" -ForegroundColor Magenta
            Write-Host "==========================================================" -ForegroundColor Green
            Write-Host ""
        }
    }
}
