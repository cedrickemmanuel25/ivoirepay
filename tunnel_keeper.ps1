while($true) {
    Write-Host "Lancement de LocalTunnel..." -ForegroundColor Cyan
    npx localtunnel --port 8000 --subdomain ivoirepay-api-backend
    Write-Host "Le tunnel s'est arrêté. Relancement dans 3 secondes..." -ForegroundColor Yellow
    Start-Sleep -Seconds 3
}
