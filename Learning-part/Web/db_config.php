<?php
function createDatabaseConnection(): mysqli
{
    $host = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';
    $database = getenv('DB_NAME') ?: 'PIF_2026';
    $port = (int) (getenv('DB_PORT') ?: 3306);

    mysqli_report(MYSQLI_REPORT_OFF);
    $connection = @mysqli_connect($host, $username, $password, $database, $port);

    if (!$connection) {
        error_log('Database connection failed: ' . mysqli_connect_error());
        http_response_code(500);
        die('Database connection failed. Check the server database settings.');
    }

    mysqli_set_charset($connection, 'utf8mb4');
    return $connection;
}
