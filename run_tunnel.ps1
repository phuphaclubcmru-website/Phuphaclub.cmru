# run_tunnel.ps1 - สคริปต์เปิด Cloudflare Tunnel พร้อมซิงค์ลิงก์ให้อัตโนมัติ
$ErrorActionPreference = 'Continue'
$root = $PSScriptRoot

Write-Host "==========================================================" -ForegroundColor Green
Write-Host "  Phupha Club - ระบบเชื่อมต่อเซิร์ฟเวอร์อัตโนมัติ" -ForegroundColor Green
Write-Host "==========================================================" -ForegroundColor Green
Write-Host ""

# ตรวจสอบว่ามี cloudflared.exe หรือไม่
$cloudflared = Join-Path $root "cloudflared.exe"
if (-not (Test-Path $cloudflared)) {
    Write-Host "[INFO] กำลังดาวน์โหลด cloudflared.exe..." -ForegroundColor Yellow
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile $cloudflared -UseBasicParsing
    Write-Host "[INFO] ดาวน์โหลดสำเร็จ!" -ForegroundColor Green
}

# ไฟล์ log ชั่วคราวสำหรับตรวจจับลิงก์
$logFile = Join-Path $root "tunnel_temp.log"
if (Test-Path $logFile) { Remove-Item $logFile -Force }

Write-Host "กำลังเริ่มการเชื่อมต่อกับ Cloudflare..." -ForegroundColor Cyan
$psi = New-Object System.Diagnostics.ProcessStartInfo
$psi.FileName = $cloudflared
$psi.Arguments = "tunnel --url http://localhost:80"
$psi.RedirectStandardError = $true
$psi.RedirectStandardOutput = $true
$psi.UseShellExecute = $false
$psi.CreateNoWindow = $true

$proc = [System.Diagnostics.Process]::Start($psi)

$tunnelUrl = ""
$startTime = [DateTime]::Now

# วนลูปอ่าน log จนกว่าจะเจอดังลิงก์ trycloudflare.com (รอสูงสุด 30 วินาที)
while (-not $proc.HasExited -and [string]::IsNullOrEmpty($tunnelUrl)) {
    if (([DateTime]::Now - $startTime).TotalSeconds -gt 30) { break }
    
    $line = $proc.StandardError.ReadLine()
    if ($line) {
        if ($line -match 'https://([a-zA-Z0-9-]+\.trycloudflare\.com)') {
            $tunnelUrl = "https://" + $matches[1] + "/phupha-club/web_code"
            break
        }
    }
}

if (-not [string]::IsNullOrEmpty($tunnelUrl)) {
    Write-Host ""
    Write-Host "[OK] ได้รับลิงก์เซิร์ฟเวอร์แล้ว: $tunnelUrl" -ForegroundColor Green
    Write-Host "[SYNC] กำลังส่งลิงก์ไปที่ระบบคลาวด์เพื่อให้แอพในมือถือเชื่อมต่ออัตโนมัติ..." -ForegroundColor Yellow
    
    # บันทึกไฟล์ current_tunnel.txt
    $tunnelTxt = Join-Path $root "current_tunnel.txt"
    $tunnelUrl | Out-File -FilePath $tunnelTxt -Encoding utf8 -NoNewline -Force
    
    # ส่งขึ้น GitHub
    try {
        Set-Location $root
        git add current_tunnel.txt 2>&1 | Out-Null
        git commit -m "Auto-update tunnel url to $tunnelUrl" 2>&1 | Out-Null
        git push origin main 2>&1 | Out-Null
        Write-Host "[SUCCESS] ซิงค์เข้ามือถือเรียบร้อยแล้ว!" -ForegroundColor Green
    } catch {
        Write-Host "[WARNING] ไม่สามารถซิงค์ขึ้น GitHub ได้ แต่ลิงก์ยังใช้งานได้ปกติ" -ForegroundColor Yellow
    }

    Write-Host ""
    Write-Host "==========================================================" -ForegroundColor Green
    Write-Host "  ระบบพร้อมใช้งานแล้ว!" -ForegroundColor Green
    Write-Host "  - มือถือสามารถเปิดแอพใช้งานได้ทันที (เชื่อมต่ออัตโนมัติ)" -ForegroundColor White
    Write-Host "  - ลิงก์บนเว็บ: $tunnelUrl" -ForegroundColor Cyan
    Write-Host "  - (ห้ามปิดหน้าต่างนี้ตลอดเวลาที่ใช้งานระบบ)" -ForegroundColor Magenta
    Write-Host "==========================================================" -ForegroundColor Green
    Write-Host ""
} else {
    Write-Host "[ERROR] ไม่สามารถรับลิงก์จาก Cloudflare ได้ กรุณาตรวจสอบว่าเปิด Apache ใน XAMPP แล้วหรือไม่" -ForegroundColor Red
}

# ปล่อยให้ cloudflared ทำงานต่อไปจนกว่าผู้ใช้จะปิดหน้าต่าง
try {
    while (-not $proc.HasExited) {
        $line = $proc.StandardError.ReadLine()
        # อ่านต่อไปเพื่อไม่ให้ buffer เต็ม
        Start-Sleep -Milliseconds 500
    }
} finally {
    if (-not $proc.HasExited) {
        $proc.Kill()
    }
}

