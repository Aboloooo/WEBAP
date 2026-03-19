<?php
//trying to adapt the to sensor data
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
    /*  $insertCC = $connection->prepare(
            "INSERT INTO CollectionContains (Collection_id, Measurement_id) VALUES (?, ?)"
        ); 
        $insertCC->bind_param('ii', $collectionId, $Measurement_id);
        $insertCC->execute();
        */
} else {
    echo "Missing data!";
}
