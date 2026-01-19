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
    <!-- bank of icons -->
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>index</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?php
    NavigationBarE();
    ?>

    <section id="Home">
        <h1>Home_page</h1>
        <h2><?= $_SESSION["userLogin"] ? "Welcome " . $_SESSION["username"] . "!" : "" ?></h2>
        <?php if ($_SESSION["userLogin"]) {
        ?>
            <button id="logout" onclick="Logout()">logout</button>
        <?php
        } ?>
        <div class="cta-banner">
            <p><strong>Connect • Share • Collaborate</strong></p>
            <p>Build your network and share data securely with friends.</p>
            <a href="Friendship.php" class="link-arrow">Explore friendship features →</a>
        </div>

        <div class="cta-banner">
            <p><strong>Register stations</strong></p>
            <p>Register your stations using their serail number and make them yours.</p>
            <a href="StationRegistration.php" class="link-arrow">Add your station →</a>
        </div>
    </section>
    <section id="About">
        <h1>About_page</h1>
    </section>
    <section id="Service">
        <h1 class="section-title">Our Services</h1>

        <div class="service-grid">
            <div class="service-card">
                <h3>Live Temperature Tracking</h3>
                <p>Monitor sensors in real-time with accurate and continuous updates.</p>
            </div>

            <div class="service-card">
                <h3>Data Visualization</h3>
                <p>Interactive charts and graphs help you understand temperature trends.</p>
            </div>

            <div class="service-card">
                <h3>Historical Records</h3>
                <p>Browse stored measurements by hour, day, month, or custom range.</p>
            </div>

            <div class="service-card">
                <h3>Alerts & Thresholds</h3>
                <p>Receive alerts when temperatures exceed or fall below limits.</p>
            </div>

            <div class="service-card">
                <h3>Sensor Management</h3>
                <p>Add, configure, or remove sensors directly from the system interface.</p>
            </div>
        </div>
    </section>

    <section id="Dashboard" class="section dashboard">
        <h1 class="section-title">Live Dashboard</h1>
        <p class="section-text">
            View real-time readings directly from the database. Analyze trends, compare sensors, and check
            system health all in one place.
        </p>
        <div class="tempretureDisplay"></div>
        <ul class="dashboard-list">
            <li>Current temperature per sensor</li>
            <li>Last update timestamp</li>
            <li>Sensor connection status</li>
            <li>Minimum / maximum values</li>
            <li>24-hour and custom-range charts</li>
            <li>Database-powered analytics</li>
        </ul>
    </section>

    <section id="Contact">
        <h1 class="section-title">Contact Us</h1>
        <p class="section-text">
            Need help integrating sensors, configuring the system, or analyzing your temperature data?
            Get in touch — we're here to help.
        </p>

        <div class="contact-info">
            <p><strong>Email:</strong> support@tempsystem.com</p>
            <p><strong>Phone:</strong> +352 600 000 000</p>
            <p><strong>Location:</strong> Luxembourg</p>
        </div>
    </section>

</body>

</html>