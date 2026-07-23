<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'qr_menu';
$port = getenv('DB_PORT') ?: 3306;

$conn = mysqli_init();

if ($host !== 'localhost') {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
}

if (!mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, ($host !== 'localhost') ? MYSQLI_CLIENT_SSL : 0)) {
    die("Connect Error (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
}

$conn->set_charset("utf8mb4");

/**
 * Builds a URL from the application's web root without hardcoding the local
 * project directory. It works whether the project is deployed at the domain
 * root or inside a subdirectory.
 */
function app_url(string $path = ''): string
{
    static $baseUrl = null;

    if ($baseUrl === null) {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
        $scriptFile = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') ?: '';
        $projectRoot = realpath(__DIR__ . '/..') ?: '';
        $relativeScript = '';

        if ($scriptFile !== '' && $projectRoot !== '' && str_starts_with($scriptFile, $projectRoot)) {
            $relativeScript = ltrim(str_replace('\\', '/', substr($scriptFile, strlen($projectRoot))), '/');
        }

        $baseUrl = $relativeScript !== '' && str_ends_with($scriptName, $relativeScript)
            ? substr($scriptName, 0, -strlen($relativeScript))
            : '/';
        $baseUrl = rtrim($baseUrl, '/') . '/';
    }

    return $baseUrl . ltrim($path, '/');
}
?>
