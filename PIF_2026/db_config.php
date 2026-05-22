<?php
/* Database configuration and connection setup */
/* This file contains the function to create a database connection using MySQLi
the confidential requires be adjusted and adopted */
function createDatabaseConnection(): mysqli
{
    $host = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USER') ?: 'root'; // Default to 'root' for local development, but should be set in production
    $password = getenv('DB_PASS') ?: ''; // Default to empty for local development, but should be set in production
    $database = getenv('DB_NAME') ?: 'PIF_2026';
    $port = (int) (getenv('DB_PORT') ?: 3306);

    mysqli_report(MYSQLI_REPORT_OFF);
    $connection = @mysqli_connect($host, $username, $password, $database, $port);
    if (!$connection) {
        $connection = mysqli_connect($host, $username, 'mysql_secure_password', $database, $port);
    }

    if (!$connection) {
        error_log('Database connection failed: ' . mysqli_connect_error());
        http_response_code(500);
        die('Database connection failed. Check the server database settings.');
    }

    mysqli_set_charset($connection, 'utf8mb4');
    return $connection;
}
