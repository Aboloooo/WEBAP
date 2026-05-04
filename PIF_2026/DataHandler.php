<?php
// Check if all required POST parameters exist
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
    $temperature    = (float)$_POST['temperature'];
    $humidity       = (float)$_POST['humidity'];
    $pressure       = (float)$_POST['pressure'];
    $light          = (float)$_POST['light'];
    $air_quality    = (int)$_POST['gas'];
} else {
    die("Error: Missing required POST parameters.");
}

require_once(__DIR__ . '/db_config.php');
$connection = createDatabaseConnection();

if (strpos($timestamp, '.') !== false) {
    $timestamp = explode('.', $timestamp)[0];
}

$station_query = "SELECT Station_id FROM Station WHERE Serial_number = ?";
$stmt = $connection->prepare($station_query);
$stmt->bind_param("s", $station_serial);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $station_id = $row['Station_id'];
    $stmt->close();

    $insert_query = "INSERT INTO Measurement (Timestamp, Temperature, Humidity, Air_pressure, Light_intensity, Air_quality, Station_id) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stat = $connection->prepare($insert_query);

    $stat->bind_param("sddddii", $timestamp, $temperature, $humidity, $pressure, $light, $air_quality, $station_id);

    if ($stat->execute()) {
        echo "Data inserted successfully. Measurement ID: " . $stat->insert_id;
    } else {
        echo "Error inserting data: " . $stat->error;
    }
    $stat->close();
} else {
    echo "Error: Station with Serial_number '$station_serial' not found in Station table. Please add it first.";
    $stmt->close();
}

$connection->close();
