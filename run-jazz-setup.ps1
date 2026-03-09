# Run this with Docker already up (docker compose up).
# Open a NEW terminal, cd to project folder, then: .\run-jazz-setup.ps1

Write-Host "Running Phinx migrations..." -ForegroundColor Cyan
docker compose exec php php /app/vendor/bin/phinx migrate

Write-Host "`nSeeding Jazz data..." -ForegroundColor Cyan
docker compose exec php php /app/vendor/bin/phinx seed:run -s JazzSeeder
docker compose exec php php /app/vendor/bin/phinx seed:run -s JazzSettingsSeeder

Write-Host "`nDone. Refresh localhost/jazz in your browser." -ForegroundColor Green
