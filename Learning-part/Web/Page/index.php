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
        <div class="home-header">
            <h1>Home page</h1>
            <div class="home-welcome-row">
                <h2><?= $_SESSION["userLogin"] ? "Welcome " . $_SESSION["username"] . "!" : "" ?></h2>
                <?php if ($_SESSION["userLogin"]) {
                ?>
                    <button id="logout" onclick="Logout()">logout</button>
                <?php
                } ?>
            </div>
        </div>

        <div class="cta-container">
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
        </div>
    </section>
    <section id="About">
        <h1 class="section-title">About Our Platform</h1>
        <p class="section-text">
            We're revolutionizing environmental monitoring with cutting-edge IoT technology.
            Our platform provides real-time data collection, advanced analytics, and seamless
            collaboration tools for researchers, businesses, and environmental enthusiasts.
        </p>

        <div class="about-features">
            <div class="feature-item">
                <div class="feature-icon">🌍</div>
                <h3>Environmental Impact</h3>
                <p>Contributing to climate research and environmental protection through accurate data collection.</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon">🔬</div>
                <h3>Scientific Research</h3>
                <p>Supporting researchers with reliable, continuous environmental measurements.</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon">🤝</div>
                <h3>Community Driven</h3>
                <p>Building a collaborative network where knowledge and data are shared openly.</p>
            </div>
        </div>
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

        <div class="tempretureDisplay">

        </div>
    </section>

    <section id="Contact">
        <h1 class="section-title">Get In Touch</h1>
        <p class="section-text">
            Have questions about our platform, need technical support, or want to collaborate?
            We'd love to hear from you. Reach out through any of the channels below.
        </p>

        <div class="contact-container">
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">📧</div>
                    <h3>Email Support</h3>
                    <p><strong>General:</strong> support@tempsystem.com</p>
                    <p><strong>Technical:</strong> tech@tempsystem.com</p>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <h3>Phone</h3>
                    <p><strong>Support:</strong> +352 600 000 000</p>
                    <p><strong>Business:</strong> +352 600 000 001</p>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">📍</div>
                    <h3>Location</h3>
                    <p><strong>Headquarters:</strong> Luxembourg</p>
                    <p><strong>Timezone:</strong> CET (UTC+1)</p>
                </div>
            </div>

            <div class="contact-form">
                <h3>Send us a Message</h3>
                <form id="contactForm">
                    <div class="form-group">
                        <input type="text" id="contactName" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" id="contactEmail" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <select id="contactSubject" required>
                            <option value="">Select Subject</option>
                            <option value="support">Technical Support</option>
                            <option value="partnership">Partnership Inquiry</option>
                            <option value="feedback">Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea id="contactMessage" placeholder="Your Message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-save">Send Message</button>
                </form>
            </div>
        </div>
    </section>

</body>

</html>