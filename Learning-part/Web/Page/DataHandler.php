<?php

// Add this at the very top for debugging
error_log("POST data: " . print_r($_POST, true));
file_put_contents('debug.log', print_r($_POST, true), FILE_APPEND);

// Check both POST and raw input
$post_data = $_POST;

// If POST is empty, try to parse raw input
if (empty($post_data)) {
    $raw_input = file_get_contents('php://input');
    parse_str($raw_input, $post_data);
}

if (
    isset(
        $post_data['station_serial'],
        $post_data['timestamp'],
        $post_data['temperature'],
        $post_data['humidity'],
        $post_data['pressure'],
        $post_data['light'],
        $post_data['gas']
    )
) {
    echo "<br>Sensor Data Received:<br>";

    $station_serial = htmlspecialchars($post_data['station_serial']);
    $timestamp      = htmlspecialchars($post_data['timestamp']);
    $temperature    = htmlspecialchars($post_data['temperature']);
    $humidity       = htmlspecialchars($post_data['humidity']);
    $pressure       = htmlspecialchars($post_data['pressure']);
    $light          = htmlspecialchars($post_data['light']);
    $gas            = htmlspecialchars($post_data['gas']);

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
    print_r($post_data);
    echo "</pre>";
}
