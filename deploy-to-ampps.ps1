# OpenRetail ERP — Deploy to AMPPS (clean copy, no dev files)
$src = "C:\Users\manor\MyProject\OpenRetail ERP"
$dst = "C:\Program Files\Ampps\www\openretail"

Write-Host "Cleaning destination..." -ForegroundColor Yellow
if (Test-Path $dst) { Remove-Item -Recurse -Force $dst }
New-Item -ItemType Directory -Force $dst | Out-Null

# Folders to copy
$folders = @('app','bootstrap','config','database','lang','public','resources\views','routes','storage','vendor')

Write-Host "Copying required folders..." -ForegroundColor Cyan
foreach ($f in $folders) {
    $s = Join-Path $src $f
    $d = Join-Path $dst $f
    if (Test-Path $s) {
        Write-Host "  -> $f"
        Copy-Item -Recurse $s $d
    }
}

# Root files only (no dev configs)
$files = @('artisan','composer.json','composer.lock','index.php','.env.example')
Write-Host "Copying root files..." -ForegroundColor Cyan
foreach ($f in $files) {
    $s = Join-Path $src $f
    if (Test-Path $s) {
        Write-Host "  -> $f"
        Copy-Item $s (Join-Path $dst $f)
    }
}

# Create required empty storage dirs
$storageDirs = @(
    'storage\app\public',
    'storage\logs',
    'storage\framework\cache\data',
    'storage\framework\sessions',
    'storage\framework\views',
    'bootstrap\cache'
)
foreach ($d in $storageDirs) {
    New-Item -ItemType Directory -Force (Join-Path $dst $d) | Out-Null
}

# Remove .env if exists so installer runs fresh
Remove-Item (Join-Path $dst '.env') -Force -ErrorAction SilentlyContinue
Remove-Item (Join-Path $dst 'storage\installed.lock') -Force -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "Done! Deployed to: $dst" -ForegroundColor Green
Write-Host "Open: http://localhost/openretail/public/install.php" -ForegroundColor Green

# Show folder size
$size = (Get-ChildItem $dst -Recurse -File | Measure-Object -Property Length -Sum).Sum
Write-Host ("Size: {0:N0} MB" -f ($size/1MB)) -ForegroundColor Cyan
