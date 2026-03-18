<?php
echo "Hello, World!";

if ($_POST['submit'] && isset($_POST['name'], $_POST['lastName'], $_POST['favoriteNumber'])) {

    $_POST['name'] = htmlspecialchars($_POST['name']);
    $_POST['lastName'] = htmlspecialchars($_POST['lastName']);
    $_POST['favoriteNumber'] = htmlspecialchars($_POST['favoriteNumber']);
    echo "<br>";
    echo "Name: " . $_POST['name'] . "<br>";
    echo "Last Name: " . $_POST['lastName'] . "<br>";
    echo "Favorite Number: " . $_POST['favoriteNumber'] . "<br>";
}

//trying to adapt the to sensor data
if (isset($_POST['station_serial'], $_POST['time'], $_POST['temperature'], $_POST['humidity'], $_POST['pressure'], $_POST['light'], $_POST['gas'])) {

    $_POST['station_serial'] = htmlspecialchars($_POST['station_serial']);
    $_POST['time'] = htmlspecialchars($_POST['time']);
    $_POST['temperature'] = htmlspecialchars($_POST['temperature']);
    $_POST['humidity'] = htmlspecialchars($_POST['humidity']);
    $_POST['pressure'] = htmlspecialchars($_POST['pressure']);
    $_POST['light'] = htmlspecialchars($_POST['light']);
    $_POST['gas'] = htmlspecialchars($_POST['gas']);
    echo "<br>";
    echo "Name: " . $_POST['station_serial'] . "<br>";
    echo "Last Name: " . $_POST['time'] . "<br>";
    echo "Favorite Number: " . $_POST['temperature'] . "<br>";
    echo "Name: " . $_POST['humidity'] . "<br>";
    echo "Last Name: " . $_POST['pressure'] . "<br>";
    echo "Favorite Number: " . $_POST['light'] . "<br>";
    echo "Favorite Number: " . $_POST['gas'] . "<br>";
}
