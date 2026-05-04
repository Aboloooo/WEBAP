# ========== CONFIGURATION - CHANGE THESE ==========
$StationSerial = "WST-202601-001"
$PhpUrl = "http://localhost/WEBAP/WEBAP-28/Learning-part/Web/DataHandler.php"
# ==================================================

function Generate-WeatherData {
    $now = Get-Date
    $timestamp = $now.ToString("yyyy-MM-dd HH:mm:ss.") + "{0:D3}" -f $now.Millisecond
    
    $temperature = [math]::Round((Get-Random -Minimum 150 -Maximum 350) / 10, 1)
    $humidity = [math]::Round((Get-Random -Minimum 300 -Maximum 900) / 10, 1)
    $pressure = [math]::Round((Get-Random -Minimum 98000 -Maximum 103000) / 100, 2)
    $light = [math]::Round((Get-Random -Minimum 0 -Maximum 1200), 1)
    $gas = Get-Random -Minimum 100 -Maximum 900
    
    # Day/Night effect (6 AM to 6 PM is daytime)
    $hour = $now.Hour
    if ($hour -lt 6 -or $hour -gt 18) {
        $light = [math]::Round((Get-Random -Minimum 0 -Maximum 50), 1)
    } elseif ($hour -gt 10 -and $hour -lt 14) {
        $light = [math]::Round((Get-Random -Minimum 800 -Maximum 1200), 1)
    }
    
    # If temperature is high, humidity drops
    if ($temperature -gt 28) {
        $humidity = [math]::Round($humidity * 0.7, 1)
    }
    
    return @{
        station_serial = $StationSerial
        timestamp = $timestamp
        temperature = $temperature
        humidity = $humidity
        pressure = $pressure
        light = $light
        gas = $gas
    }
}

function Send-WeatherData {
    param($Data)
    
    try {
        $response = Invoke-WebRequest -Uri $PhpUrl -Method Post -Body $Data -UseBasicParsing -TimeoutSec 5
        return @{
            Success = $true
            StatusCode = $response.StatusCode
            Response = $response.Content
        }
    }
    catch {
        return @{
            Success = $false
            Error = $_.Exception.Message
        }
    }
}

# Main menu
Clear-Host
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   WEATHER STATION DATA SIMULATOR" -ForegroundColor White
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Configuration:" -ForegroundColor Yellow
Write-Host "  Station: $StationSerial" -ForegroundColor Gray
Write-Host "  PHP URL: $PhpUrl" -ForegroundColor Gray
Write-Host ""
Write-Host "Options:" -ForegroundColor Green
Write-Host "  1. Send single data packet" -ForegroundColor White
Write-Host "  2. Send continuously (every 2 seconds)" -ForegroundColor White
Write-Host "  3. Send 5 test packets" -ForegroundColor White
Write-Host "  4. Exit" -ForegroundColor White
Write-Host ""

$choice = Read-Host "Your choice (1-4)"

if ($choice -eq "1") {
    Write-Host "`nSending single data packet..." -ForegroundColor Cyan
    
    $data = Generate-WeatherData
    $result = Send-WeatherData -Data $data
    
    Write-Host "`nData Sent:" -ForegroundColor White
    Write-Host "  Temperature: $($data.temperature)°C" -ForegroundColor Cyan
    Write-Host "  Humidity: $($data.humidity)%" -ForegroundColor Green
    Write-Host "  Pressure: $($data.pressure) hPa" -ForegroundColor Yellow
    Write-Host "  Light: $($data.light) lux" -ForegroundColor Magenta
    Write-Host "  Air Quality: $($data.gas)" -ForegroundColor Red
    
    if ($result.Success) {
        Write-Host "`nSuccess! HTTP $($result.StatusCode)" -ForegroundColor Green
        Write-Host "Response: $($result.Response)" -ForegroundColor White
    } else {
        Write-Host "`nFailed: $($result.Error)" -ForegroundColor Red
    }
}
elseif ($choice -eq "2") {
    Write-Host "`nStarting continuous transmission (every 10 seconds)..." -ForegroundColor Cyan
    Write-Host "Press Ctrl+C to stop`n" -ForegroundColor Yellow
    
    $count = 0
    
    while ($true) {
        $count++
        $data = Generate-WeatherData
        $result = Send-WeatherData -Data $data
        $time = Get-Date -Format "HH:mm:ss"
        
        if ($result.Success) {
            Write-Host "[$time] #$count | T:$($data.temperature)°C H:$($data.humidity)% P:$($data.pressure)hPa L:$($data.light)lx G:$($data.gas) - OK" -ForegroundColor Green
        } else {
            Write-Host "[$time] #$count | ERROR: $($result.Error)" -ForegroundColor Red
        }
        
        Start-Sleep -Seconds 2
    }
}
elseif ($choice -eq "3") {
    Write-Host "`nSending 5 test packets..." -ForegroundColor Cyan
    
    for ($i = 1; $i -le 5; $i++) {
        $data = Generate-WeatherData
        $result = Send-WeatherData -Data $data
        
        if ($result.Success) {
            Write-Host "[$i/5] T:$($data.temperature)°C H:$($data.humidity)% - OK" -ForegroundColor Green
        } else {
            Write-Host "[$i/5] ERROR: $($result.Error)" -ForegroundColor Red
        }
        
        Start-Sleep -Seconds 2
    }
    
    Write-Host "`nCompleted!" -ForegroundColor Cyan
}
else {
    Write-Host "Goodbye!" -ForegroundColor Cyan
}