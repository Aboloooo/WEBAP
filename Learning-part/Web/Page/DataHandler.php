<?php
if (
    isset(
        $_POST['station_serial'],
        $_POST['timestamp'],
        $_POST['temperature'],
        $_POST['humidity'],
        $_POST['pressure'],
        $_POST['light'],
        $_POST['gas']
    )
) {
    $station_serial = $_POST['station_serial'];
    $timestamp      = $_POST['timestamp'];
    $temperature    = $_POST['temperature'];
    $humidity       = $_POST['humidity'];
    $pressure       = $_POST['pressure'];
    $light          = $_POST['light'];
    $air_quality    = $_POST['gas'];
} else {
    die("Error: Missing required POST parameters.");
}

$host = 'localhost';
$username = 'root';
$password = '';
$port = '3306';
$certificate_file_path = '';
$database = 'PIF_2026';
$connection = mysqli_connect($host, $username, $password, $database);
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}
$stat = $connection->prepare("INSERT INTO Measurement (Timestamp, Temperature, Humidity, Air_pressure, Light_intensity, Air_quality, Station_id) 
                        VALUES (?, ?, ?, ?, ?, ?, (SELECT Station_id FROM Station WHERE Serial_number = ?))");
$stat->bind_param("sddddii", $timestamp, $temperature, $humidity, $pressure, $light, $air_quality, $station_serial);
if ($stat->execute()) {
    echo "Data inserted successfully.";
} else {
    echo "Error inserting data: " . $stat->error;
}
