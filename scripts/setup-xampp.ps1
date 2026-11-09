param(
    [string]$XamppPath = 'C:\xampp',
<<<<<<< HEAD
    [int]$MySqlPort = 3306
=======
    [int]$MySqlPort = 3306,
    [string]$MySqlClientPath = ''
>>>>>>> master
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

function ConvertTo-PlainText([Security.SecureString]$Value) {
    $pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($Value)
    try {
        return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    } finally {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
    }
}

function Invoke-MySqlQuery([string]$Query) {
<<<<<<< HEAD
    $output = & $script:MySqlPath "--defaults-extra-file=$script:DefaultsFile" '--default-character-set=utf8mb4' '--batch' '--skip-column-names' '-e' $Query 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "MySQL command failed: $($output -join [Environment]::NewLine)"
=======
    $previousErrorActionPreference = $ErrorActionPreference
    try {
        $ErrorActionPreference = 'Continue'
        $output = & $script:MySqlPath "--defaults-extra-file=$script:DefaultsFile" '--default-character-set=utf8mb4' '--batch' '--skip-column-names' '-e' $Query 2>&1
        $exitCode = $LASTEXITCODE
    } finally {
        $ErrorActionPreference = $previousErrorActionPreference
    }
    if ($exitCode -ne 0) {
        $message = $output -join [Environment]::NewLine
        if ($message -match 'caching_sha2_password') {
            throw "The selected MySQL client cannot load the server's caching_sha2_password plugin. Start XAMPP MySQL/MariaDB on its configured port, or rerun with -MySqlClientPath set to the matching MySQL client if you intend to use a separate MySQL Server. Details: $message"
        }
        throw "MySQL command failed: $message"
>>>>>>> master
    }
    return ($output -join [Environment]::NewLine).Trim()
}

function Invoke-MySqlFile([string]$Path) {
    $startInfo = New-Object System.Diagnostics.ProcessStartInfo
    $startInfo.FileName = $script:MySqlPath
    $startInfo.Arguments = "--defaults-extra-file=`"$script:DefaultsFile`" --default-character-set=utf8mb4"
    $startInfo.UseShellExecute = $false
    $startInfo.CreateNoWindow = $true
    $startInfo.RedirectStandardInput = $true
    $startInfo.RedirectStandardOutput = $true
    $startInfo.RedirectStandardError = $true
    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $startInfo
    if (-not $process.Start()) {
        throw 'Could not start the XAMPP MySQL client.'
    }
    $stdoutTask = $process.StandardOutput.ReadToEndAsync()
    $stderrTask = $process.StandardError.ReadToEndAsync()
    $sql = [IO.File]::ReadAllText($Path, [Text.Encoding]::UTF8)
    $process.StandardInput.Write($sql)
    $process.StandardInput.Close()
    $process.WaitForExit()
    $stdout = $stdoutTask.GetAwaiter().GetResult()
    $stderr = $stderrTask.GetAwaiter().GetResult()
    if ($process.ExitCode -ne 0) {
        throw "MySQL import failed: $stderr`n$stdout"
    }
}

function ConvertTo-SqlUtf8([string]$Value) {
    $hex = [BitConverter]::ToString([Text.Encoding]::UTF8.GetBytes($Value)).Replace('-', '')
    return "CONVERT(0x$hex USING utf8mb4)"
}

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$phpPath = Join-Path $XamppPath 'php\php.exe'
<<<<<<< HEAD
$mySqlPath = Join-Path $XamppPath 'mysql\bin\mysql.exe'
$htdocsPath = Join-Path $XamppPath 'htdocs'
$targetPath = Join-Path $htdocsPath 'SetupDino'
$script:MySqlPath = $mySqlPath
=======
$mySqlPath = if ([string]::IsNullOrWhiteSpace($MySqlClientPath)) {
    Join-Path $XamppPath 'mysql\bin\mysql.exe'
} else {
    $MySqlClientPath
}
$htdocsPath = Join-Path $XamppPath 'htdocs'
$targetPath = Join-Path $htdocsPath 'SetupDino'
if ([IO.Path]::GetFullPath($projectRoot).TrimEnd('\').Equals([IO.Path]::GetFullPath($htdocsPath).TrimEnd('\'), [StringComparison]::OrdinalIgnoreCase)) {
    $targetPath = $projectRoot
}
$sameInstallPath = [IO.Path]::GetFullPath($projectRoot).TrimEnd('\').Equals([IO.Path]::GetFullPath($targetPath).TrimEnd('\'), [StringComparison]::OrdinalIgnoreCase)
$siteUrl = if ([IO.Path]::GetFullPath($targetPath).TrimEnd('\').Equals([IO.Path]::GetFullPath($htdocsPath).TrimEnd('\'), [StringComparison]::OrdinalIgnoreCase)) {
    'http://localhost/'
} else {
    'http://localhost/SetupDino/'
}
$script:MySqlPath = $mySqlPath
if (-not [string]::IsNullOrWhiteSpace($MySqlClientPath)) {
    Write-Host "Using MySQL client: $mySqlPath"
}
>>>>>>> master

if ($MySqlPort -lt 1 -or $MySqlPort -gt 65535) {
    throw 'MySQL port must be between 1 and 65535.'
}

foreach ($requiredPath in @($phpPath, $mySqlPath, $htdocsPath, (Join-Path $projectRoot 'database\schema.sql'), (Join-Path $projectRoot 'database\seed.sql'), (Join-Path $projectRoot '.env.example'))) {
    if (-not (Test-Path -LiteralPath $requiredPath)) {
        throw "Required XAMPP or project file was not found: $requiredPath"
    }
}

$phpVersion = & $phpPath -r 'echo PHP_VERSION_ID;' 2>&1
if ($LASTEXITCODE -ne 0 -or [int]($phpVersion -join '') -lt 80100) {
    throw 'SetupDino requires PHP 8.1 or later in XAMPP.'
}

$composerCommand = Get-Command composer -ErrorAction SilentlyContinue
if (-not (Test-Path -LiteralPath (Join-Path $projectRoot 'vendor\autoload.php')) -and $null -eq $composerCommand) {
    throw 'Composer dependencies are missing. Install Composer and add it to PATH before running this setup script.'
}

$modules = & $phpPath -m 2>&1
if ($LASTEXITCODE -ne 0) {
    throw "Could not run XAMPP PHP at $phpPath."
}
<<<<<<< HEAD
$missingModules = @('pdo_mysql', 'fileinfo', 'mbstring') | Where-Object { $modules -notcontains $_ }
=======
$missingModules = @(@('pdo_mysql', 'fileinfo', 'mbstring') | Where-Object { $modules -notcontains $_ })
>>>>>>> master
if ($missingModules.Count -gt 0) {
    throw "Enable these PHP extensions in $XamppPath\php\php.ini, restart Apache, and run setup again: $($missingModules -join ', ')"
}

if (Test-Path -LiteralPath $targetPath) {
    $confirmation = Read-Host "$targetPath already exists. Update its application files and back up/replace its .env? Type YES to continue"
    if ($confirmation -cne 'YES') {
        Write-Host 'Setup cancelled; existing files were not changed.'
        exit 0
    }
}

$rootPasswordSecure = Read-Host 'Enter the XAMPP MySQL root password (leave blank if root has no password)' -AsSecureString
$rootPassword = ConvertTo-PlainText $rootPasswordSecure
if ($rootPassword.Contains("`n") -or $rootPassword.Contains("`r")) {
    throw 'The MySQL root password cannot contain a newline.'
}

$adminName = (Read-Host 'Name for the first administrator').Trim()
$adminEmail = (Read-Host 'Email for the first administrator').Trim()
if ($adminName.Length -lt 2 -or $adminName.Length -gt 120) {
    throw 'Administrator name must be between 2 and 120 characters.'
}
if ($adminEmail -notmatch '^[^@\s]+@[^@\s]+\.[^@\s]+$' -or $adminEmail.Length -gt 190) {
    throw 'Enter a valid administrator email address (maximum 190 characters).'
}
$adminPasswordSecure = Read-Host 'Create an administrator password (at least 12 characters)' -AsSecureString
$adminPassword = ConvertTo-PlainText $adminPasswordSecure
if ($adminPassword.Length -lt 12) {
    throw 'Administrator password must contain at least 12 characters.'
}

$bytes = New-Object byte[] 32
$rng = [Security.Cryptography.RandomNumberGenerator]::Create()
try {
    $rng.GetBytes($bytes)
    $applicationPassword = [BitConverter]::ToString($bytes).Replace('-', '').ToLowerInvariant()
} finally {
    $rng.Dispose()
}

$tempDirectory = Join-Path $env:TEMP ('SetupDino-' + [Guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $tempDirectory | Out-Null
$script:DefaultsFile = Join-Path $tempDirectory 'mysql.cnf'
$utf8NoBom = New-Object -TypeName System.Text.UTF8Encoding -ArgumentList $false

try {
    $identity = [Security.Principal.WindowsIdentity]::GetCurrent().Name
    & icacls.exe $tempDirectory '/inheritance:r' '/grant:r' "${identity}:(OI)(CI)F" | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw 'Could not restrict access to temporary setup files.'
    }

    $defaults = "[client]`nuser=root`nhost=localhost`nport=$MySqlPort`n"
    if ($rootPassword -ne '') {
        $escapedRootPassword = $rootPassword.Replace('\', '\\').Replace('"', '\"')
        $defaults += "password=`"$escapedRootPassword`"`n"
    }
    [IO.File]::WriteAllText($script:DefaultsFile, $defaults, $utf8NoBom)

    if ((Invoke-MySqlQuery 'SELECT 1') -ne '1') {
        throw 'Could not verify the MySQL connection.'
    }

    $databaseExists = (Invoke-MySqlQuery "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name = 'SetupDino'") -eq '1'
    $migrationDirectory = Join-Path $projectRoot 'database\migrations'
    if (-not $databaseExists) {
        Invoke-MySqlQuery 'CREATE DATABASE SetupDino CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci' | Out-Null
        $grantSql = @"
CREATE USER IF NOT EXISTS 'SetupDino_app'@'localhost' IDENTIFIED BY '$applicationPassword';
ALTER USER 'SetupDino_app'@'localhost' IDENTIFIED BY '$applicationPassword';
GRANT SELECT, INSERT, UPDATE, DELETE ON SetupDino.* TO 'SetupDino_app'@'localhost';
"@
        $grantFile = Join-Path $tempDirectory 'application-user.sql'
        [IO.File]::WriteAllText($grantFile, $grantSql, $utf8NoBom)
        Invoke-MySqlFile $grantFile
        Invoke-MySqlFile (Join-Path $projectRoot 'database\schema.sql')
        Invoke-MySqlFile (Join-Path $projectRoot 'database\seed.sql')
    } else {
        $tableExists = (Invoke-MySqlQuery "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'SetupDino' AND table_name = 'products'") -eq '1'
        $usersTableExists = (Invoke-MySqlQuery "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'SetupDino' AND table_name = 'users'") -eq '1'
        if (-not $tableExists -or -not $usersTableExists) {
            throw 'The existing SetupDino database is missing required tables. Back it up and import database/schema.sql manually before continuing.'
        }
        $emailExpression = ConvertTo-SqlUtf8 $adminEmail
        $existingAdminAccount = Invoke-MySqlQuery "SELECT COUNT(*) FROM SetupDino.users WHERE email = $emailExpression"
        if ($existingAdminAccount -eq '1') {
            $replaceAdmin = Read-Host 'This email is already registered. Replace its name/password and grant admin access? Type YES to continue'
            if ($replaceAdmin -cne 'YES') {
                throw 'Administrator account update cancelled.'
            }
        }
        $sellerColumnExists = (Invoke-MySqlQuery "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = 'SetupDino' AND table_name = 'products' AND column_name = 'seller_id'") -eq '1'
        $roleType = Invoke-MySqlQuery "SELECT column_type FROM information_schema.columns WHERE table_schema = 'SetupDino' AND table_name = 'users' AND column_name = 'role'"
        if (-not $sellerColumnExists) {
            $migrationFile = Join-Path $migrationDirectory '001_seller_products.sql'
            if (-not (Test-Path -LiteralPath $migrationFile)) {
                throw "Seller migration file not found: $migrationFile"
            }
            $migrationConfirmation = Read-Host 'Seller support is missing from this database. Confirm you have a backup, then type YES to apply the migration'
            if ($migrationConfirmation -cne 'YES') {
                throw 'Database migration cancelled. Back up the database, then rerun this script.'
            }
            Invoke-MySqlFile $migrationFile
        } elseif ($roleType -notmatch "'seller'") {
            Invoke-MySqlQuery "ALTER TABLE SetupDino.users MODIFY role ENUM('customer', 'seller', 'admin') NOT NULL DEFAULT 'customer'" | Out-Null
        }

        $grantSql = @"
CREATE USER IF NOT EXISTS 'SetupDino_app'@'localhost' IDENTIFIED BY '$applicationPassword';
ALTER USER 'SetupDino_app'@'localhost' IDENTIFIED BY '$applicationPassword';
GRANT SELECT, INSERT, UPDATE, DELETE ON SetupDino.* TO 'SetupDino_app'@'localhost';
"@
        $grantFile = Join-Path $tempDirectory 'application-user.sql'
        [IO.File]::WriteAllText($grantFile, $grantSql, $utf8NoBom)
        Invoke-MySqlFile $grantFile
    }

    $env:SETUPDINO_ADMIN_PASSWORD = $adminPassword
    try {
        $passwordHash = (& $phpPath -r 'echo password_hash(getenv("SETUPDINO_ADMIN_PASSWORD"), PASSWORD_DEFAULT);' 2>&1) -join ''
        if ($LASTEXITCODE -ne 0 -or $passwordHash -notmatch '^\$2[ayb]\$|^\$argon2') {
            throw 'Could not create the administrator password hash using XAMPP PHP.'
        }
    } finally {
        Remove-Item Env:\SETUPDINO_ADMIN_PASSWORD -ErrorAction SilentlyContinue
    }

    $adminSql = @"
USE SetupDino;
INSERT INTO users (name, email, password_hash, role)
VALUES ($(ConvertTo-SqlUtf8 $adminName), $(ConvertTo-SqlUtf8 $adminEmail), '$passwordHash', 'admin')
ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role = 'admin';
"@
    $adminFile = Join-Path $tempDirectory 'administrator.sql'
    [IO.File]::WriteAllText($adminFile, $adminSql, $utf8NoBom)
    Invoke-MySqlFile $adminFile

<<<<<<< HEAD
    if (-not (Test-Path -LiteralPath $targetPath)) {
        New-Item -ItemType Directory -Path $targetPath | Out-Null
    }
    & robocopy.exe $projectRoot $targetPath /E /COPY:DAT /DCOPY:DAT /R:2 /W:1 /XD .git .idea /XF .env | Out-Host
    if ($LASTEXITCODE -ge 8) {
        throw "Could not copy project files into $targetPath (robocopy exit code $LASTEXITCODE)."
=======
    if ($sameInstallPath) {
        Write-Host "Project files already reside at $targetPath; skipping copy."
    } else {
        if (-not (Test-Path -LiteralPath $targetPath)) {
            New-Item -ItemType Directory -Path $targetPath | Out-Null
        }
        & robocopy.exe $projectRoot $targetPath /E /COPY:DAT /DCOPY:DAT /R:2 /W:1 /XD .git .idea /XF .env | Out-Host
        if ($LASTEXITCODE -ge 8) {
            throw "Could not copy project files into $targetPath (robocopy exit code $LASTEXITCODE)."
        }
>>>>>>> master
    }

    $envPath = Join-Path $targetPath '.env'
    if (Test-Path -LiteralPath $envPath) {
        $backupPath = "$envPath.backup-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
        Copy-Item -LiteralPath $envPath -Destination $backupPath
        Write-Host "Previous .env backed up to $backupPath"
    }
    $envContent = @"
DB_HOST=localhost
DB_PORT=$MySqlPort
DB_NAME=SetupDino
DB_USER=SetupDino_app
DB_PASSWORD=$applicationPassword
"@
    [IO.File]::WriteAllText($envPath, $envContent + "`n", $utf8NoBom)

    $autoload = Join-Path $targetPath 'vendor\autoload.php'
    if (-not (Test-Path -LiteralPath $autoload)) {
        $oldPath = $env:PATH
        try {
            $env:PATH = "$XamppPath\php;$oldPath"
            & $composerCommand.Source install ("--working-dir=$targetPath") --no-interaction --prefer-dist
            if ($LASTEXITCODE -ne 0) {
                throw 'Composer could not install the project dependencies.'
            }
        } finally {
            $env:PATH = $oldPath
        }
    }

    $verifyDefaults = "[client]`nuser=SetupDino_app`nhost=localhost`nport=$MySqlPort`npassword=`"$applicationPassword`"`n"
    [IO.File]::WriteAllText($script:DefaultsFile, $verifyDefaults, $utf8NoBom)
    if ((Invoke-MySqlQuery 'SELECT COUNT(*) FROM SetupDino.users WHERE role = ''admin''') -eq '0') {
        throw 'The administrator account was not created successfully.'
    }

    Write-Host ''
    Write-Host 'SetupDino is installed in XAMPP.'
    Write-Host "Project: $targetPath"
    Write-Host 'Database: SetupDino'
    Write-Host "Admin email: $adminEmail"
<<<<<<< HEAD
    Write-Host 'Start Apache and MySQL in the XAMPP Control Panel, then sign in at http://localhost/SetupDino/login.php.'
=======
    Write-Host "Start Apache and MySQL in the XAMPP Control Panel, then sign in at $($siteUrl)login.php."
>>>>>>> master
    Write-Host 'The admin password is the one you entered above. The generated database password is stored in the project .env file.'
} finally {
    $rootPassword = $null
    $adminPassword = $null
    $rootPasswordSecure = $null
    $adminPasswordSecure = $null
    $applicationPassword = $null
    $passwordHash = $null
    if (Test-Path -LiteralPath $tempDirectory) {
        Remove-Item -LiteralPath $tempDirectory -Recurse -Force
    }
}
