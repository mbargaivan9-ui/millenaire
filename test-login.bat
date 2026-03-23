@echo off
REM Test login endpoint
PowerShell -Command "
$credentials = @{
    'admin' = 'Admin@2025!',
    'teacher' = 'Teacher@2025!',
    'parent' = 'Parent@2025!',
    'student' = 'Student@2025!'
}

foreach ($role in $credentials.Keys) {
    $email = \"$role@millenaire.cm\"
    $password = $credentials[$role]
    
    Write-Host \"Testing login for $email with password $password\" -ForegroundColor Cyan
    
    $response = Invoke-WebRequest -Uri 'http://localhost:8080/login' `
        -Method Post `
        -Body @{
            email = $email
            password = $password
        } `
        -SessionVariable session `
        -ErrorAction SilentlyContinue
    
    if ($null -ne $response -and $response.StatusCode -eq 200 -or $response.StatusCode -eq 302) {
        Write-Host \"✓ $email login successful (Status: $($response.StatusCode))\" -ForegroundColor Green
    } else {
        Write-Host \"✗ $email login FAILED\" -ForegroundColor Red
    }
}
"
