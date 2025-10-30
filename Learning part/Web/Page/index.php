<?php
include_once("../MyLibrary.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- CDN jQuery pull -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- my vanila js script -->
    <script src="../MyScript.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>index</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?php
    NavigationBarE("Home");
    ?>

    <section id="Home">
        <button id="increamentBtn">Counter+</button>
        <h2 id="result">10</h2>

        <h1>Home_page</h1>

    </section>
    <section id="Service">
        <h1>Service page</h1>
    </section>
    <section id="About">
        <h1>About page</h1>
    </section>
    <section id="Contact">
        <h1>Contact page</h1>
    </section>

</body>

</html>