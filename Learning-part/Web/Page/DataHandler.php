<?php

// Add this at the very top for debugging
error_log("POST data: " . print_r($_POST, true));
file_put_contents('debug.log', print_r($_POST, true), FILE_APPEND);

// Check both POST and raw input
$_POST = $_POST;

// If POST is empty, try to parse raw input
if (empty($_POST)) {
    $raw_input = file_get_contents('php://input');
    parse_str($raw_input, $_POST);
}

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
    echo "<br>Sensor Data Received:<br>";

    $station_serial = htmlspecialchars($_POST['station_serial']);
    $timestamp      = htmlspecialchars($_POST['timestamp']);
    $temperature    = htmlspecialchars($_POST['temperature']);
    $humidity       = htmlspecialchars($_POST['humidity']);
    $pressure       = htmlspecialchars($_POST['pressure']);
    $light          = htmlspecialchars($_POST['light']);
    $gas            = htmlspecialchars($_POST['gas']);

    echo "Station Serial: $station_serial <br>";
    echo "Timestamp: $timestamp <br>";
    echo "Temperature: $temperature <br>";
    echo "Humidity: $humidity <br>";
    echo "Pressure: $pressure <br>";
    echo "Light: $light <br>";
    echo "Gas: $gas <br>";
} else {
    echo "Missing data!";
    echo "<br>Received POST data: <pre>";
    print_r($_POST);
    echo "</pre>";
}
