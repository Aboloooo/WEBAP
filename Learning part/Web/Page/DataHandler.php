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
