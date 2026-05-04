<?php
include_once("../MyLibrary.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- CDN jQuery pull -->
    <!--     <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>-->
    <script src="../js/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <!-- my vanila js script -->
    <script src="../js/MyScript.js"></script>
    <!-- bank of icons -->
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnvMonitor - Home</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?php
    NavigationBarE();
    ?>

    <section id="Home">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="hero-highlight">Environmental</span><br>
                    <span class="hero-subtitle">Monitoring Platform</span>
                </h1>
                <p class="hero-description">
                    Revolutionizing climate research through IoT technology and real-time data analytics
                </p>
                <div class="hero-actions">
                    <?php if ($_SESSION["userLogin"]) { ?>
                        <div class="welcome-user">
                            <span class="welcome-text">Welcome back, <strong><?php echo $_SESSION["username"]; ?>!</strong></span>

                            <button id="logout" class="btn btn-secondary" onclick="Logout()">
                                <i class='bx bx-log-out'></i> Sign Out
                            </button>
                        </div>
                    <?php } else { ?>
                        <a href="sign_in_up.php" class="btn btn-primary">
                            <i class='bx bx-user-plus'></i> Get Started
                        </a>
                    <?php } ?>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Active Sensors</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Monitoring</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Uptime</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="features-preview">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class='bx bx-trending-up'></i>
                </div>
                <h3>Real-time Analytics</h3>
                <p>Live data visualization and trend analysis</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class='bx bx-network-chart'></i>
                </div>
                <h3>IoT Integration</h3>
                <p>Seamless sensor connectivity and management</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class='bx bx-shield-check'></i>
                </div>
                <h3>Data Security</h3>
                <p>Enterprise-grade security and privacy protection</p>
            </div>
        </div>
    </section>
    <section id="About">
        <div class="about-header">
            <div class="section-badge">About Us</div>
            <h1 class="section-title">Empowering Environmental Intelligence</h1>
            <p class="section-description">
                We're pioneering the future of environmental monitoring through innovative IoT solutions,
                delivering unprecedented insights into our changing climate.
            </p>
        </div>

        <div class="about-grid">
            <div class="about-card">
                <div class="card-icon">
                    <i class='bx bx-globe'></i>
                </div>
                <div class="card-content">
                    <h3>Global Impact</h3>
                    <p>Our network spans continents, providing comprehensive environmental data that drives meaningful change and informed decision-making.</p>
                    <div class="card-stats">
                        <span class="stat">50+ Countries</span>
                        <span class="stat">10K+ Sensors</span>
                    </div>
                </div>
            </div>

            <div class="about-card">
                <div class="card-icon">
                    <i class='bx bx-brain'></i>
                </div>
                <div class="card-content">
                    <h3>AI-Powered Analytics</h3>
                    <p>Advanced machine learning algorithms process vast amounts of environmental data, uncovering patterns and predicting trends with remarkable accuracy.</p>
                    <div class="card-stats">
                        <span class="stat">95% Accuracy</span>
                        <span class="stat">Real-time Processing</span>
                    </div>
                </div>
            </div>

            <div class="about-card">
                <div class="card-icon">
                    <i class='bx bx-group'></i>
                </div>
                <div class="card-content">
                    <h3>Collaborative Ecosystem</h3>
                    <p>Building bridges between researchers, policymakers, and communities to foster innovation and accelerate environmental solutions.</p>
                    <div class="card-stats">
                        <span class="stat">500+ Partners</span>
                        <span class="stat">Open Data</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="about-mission">
            <div class="mission-content">
                <h2>Our Mission</h2>
                <p>To create a sustainable future by democratizing access to environmental data, enabling informed decisions that protect our planet for generations to come.</p>
                <div class="mission-values">
                    <span class="value-tag">Innovation</span>
                    <span class="value-tag">Sustainability</span>
                    <span class="value-tag">Collaboration</span>
                    <span class="value-tag">Excellence</span>
                </div>
            </div>
            <div class="mission-visual">
                <div class="visual-placeholder">
                    <i class='bx bx-leaf'></i>
                    <span>Environmental Impact Visualization</span>
                </div>
            </div>
        </div>
    </section>
    <section id="Service">
        <div class="services-header">
            <div class="section-badge">Our Services</div>
            <h1 class="section-title">Comprehensive Environmental Solutions</h1>
            <p class="section-description">
                From real-time monitoring to advanced analytics, we provide everything you need
                to understand and protect our environment.
            </p>
        </div>

        <div class="services-grid">
            <div class="service-card premium">
                <div class="card-header">
                    <div class="service-icon">
                        <i class='bx bx-pulse'></i>
                    </div>
                    <div class="service-badge">Most Popular</div>
                </div>
                <div class="card-content">
                    <h3>Live Environmental Tracking</h3>
                    <p>Monitor temperature, humidity, air quality, and more in real-time with sub-second precision and instant alerts.</p>
                    <ul class="service-features">
                        <li><i class='bx bx-check'></i> Real-time data streaming</li>
                        <li><i class='bx bx-check'></i> Multi-sensor support</li>
                        <li><i class='bx bx-check'></i> Instant notifications</li>
                    </ul>
                </div>
            </div>

            <div class="service-card">
                <div class="card-header">
                    <div class="service-icon">
                        <i class='bx bx-bar-chart-alt-2'></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>Advanced Data Visualization</h3>
                    <p>Transform raw data into actionable insights with interactive dashboards, trend analysis, and predictive modeling.</p>
                    <ul class="service-features">
                        <li><i class='bx bx-check'></i> Interactive charts</li>
                        <li><i class='bx bx-check'></i> Custom reports</li>
                        <li><i class='bx bx-check'></i> Export capabilities</li>
                    </ul>
                </div>
            </div>

            <div class="service-card">
                <div class="card-header">
                    <div class="service-icon">
                        <i class='bx bx-cloud-upload'></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3>API Integration</h3>
                    <p>Seamlessly integrate environmental data into your applications with our comprehensive REST API and webhooks.</p>
                    <ul class="service-features">
                        <li><i class='bx bx-check'></i> RESTful API</li>
                        <li><i class='bx bx-check'></i> Webhook support</li>
                        <li><i class='bx bx-check'></i> SDK libraries</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="services-cta">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of organizations already monitoring our environment</p>
            <div class="cta-buttons">
                <a href="StationRegistration.php" class="btn btn-primary">
                    <i class='bx bx-plus-circle'></i> Register Station
                </a>
                <a href="Friendship.php" class="btn btn-primary">
                    <i class='bx bx-group'></i> Add Friends
                </a>
                <a href="sign_in_up.php" class="btn btn-secondary">
                    <i class='bx bx-user-plus'></i> Create Account
                </a>
            </div>
        </div>
    </section>

    <section id="Dashboard" class="section dashboard">
        <div class="dashboard-header">
            <div class="section-badge">Live Dashboard</div>
            <h1 class="section-title">Real-Time Environmental Insights</h1>
            <p class="section-description">
                Monitor live sensor data, analyze trends, and gain actionable insights from your environmental monitoring network.
            </p>
        </div>

        <div class="dashboard-controls">
            <div class="control-group">
                <label for="dashboardStationSelect">Select Station:</label>
                <select id="dashboardStationSelect" class="form-control">
                    <option value="0">Loading stations…</option>
                </select>
            </div>
        </div>

        <div class="dashboard-metrics">
            <div class="metric-card active-metric" data-metric="humidity">
                <div class="metric-icon">
                    <i class='bx bx-droplet'></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="metric-humidity">--%</div>
                    <div class="metric-label">Humidity</div>
                    <div class="metric-trend">
                        <i class='bx bx-minus'></i>
                        <span id="metric-humidity-trend">--</span>
                    </div>
                </div>
            </div>

            <div class="metric-card" data-metric="pressure">
                <div class="metric-icon">
                    <i class='bx bx-wind'></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="metric-pressure">-- hPa</div>
                    <div class="metric-label">Air Pressure</div>
                    <div class="metric-trend">
                        <i class='bx bx-minus'></i>
                        <span id="metric-pressure-trend">--</span>
                    </div>
                </div>
            </div>

            <div class="metric-card" data-metric="light">
                <div class="metric-icon">
                    <i class='bx bx-sun'></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="metric-light">-- lx</div>
                    <div class="metric-label">Light Intensity</div>
                    <div class="metric-trend">
                        <i class='bx bx-minus'></i>
                        <span id="metric-light-trend">--</span>
                    </div>
                </div>
            </div>

            <div class="metric-card" data-metric="airquality">
                <div class="metric-icon">
                    <i class='bx bx-leaf'></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value" id="metric-airquality">-- ppm</div>
                    <div class="metric-label">Air Quality</div>
                    <div class="metric-trend">
                        <i class='bx bx-minus'></i>
                        <span id="metric-airquality-trend">--</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-charts">
            <div class="chart-container">
                <div class="chart-header">
                    <h3 id="chartMetricTitle">Humidity Trend</h3>
                    <div class="chart-controls">
                        <button type="button" class="chart-btn active" data-period="1h">1H</button>
                        <button type="button" class="chart-btn" data-period="24h">24H</button>
                        <button type="button" class="chart-btn" data-period="7d">7D</button>
                        <button type="button" class="chart-btn" data-period="30d">30D</button>
                    </div>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="tempTrendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="tempretureDisplay">
            <!-- Dynamic content loaded here -->
        </div>
    </section>

    <section id="Contact">
        <div class="contact-header">
            <div class="section-badge">Contact Us</div>
            <h1 class="section-title">Let's Build Something Together</h1>
            <p class="section-description">
                Have questions about our platform? Need technical support? Want to collaborate on environmental research?
                We're here to help you succeed.
            </p>
        </div>

        <div class="contact-grid">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class='bx bx-envelope'></i>
                </div>
                <div class="contact-content">
                    <h3>Email Support</h3>
                    <p>Get in touch with our expert team for any questions or support needs.</p>
                    <div class="contact-details">
                        <a href="mailto:support@envmonitor.com" class="contact-link">
                            <i class='bx bx-envelope'></i> support@envmonitor.com
                        </a>
                        <a href="mailto:tech@envmonitor.com" class="contact-link">
                            <i class='bx bx-envelope'></i> tech@envmonitor.com
                        </a>
                    </div>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class='bx bx-phone'></i>
                </div>
                <div class="contact-content">
                    <h3>Phone & Live Chat</h3>
                    <p>Speak directly with our support team for immediate assistance.</p>
                    <div class="contact-details">
                        <a href="tel:+352600000000" class="contact-link">
                            <i class='bx bx-phone'></i> +352 600 000 000
                        </a>
                        <div class="contact-status">
                            <span class="status-dot online"></span>
                            Live chat available 24/7
                        </div>
                    </div>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class='bx bx-map-pin'></i>
                </div>
                <div class="contact-content">
                    <h3>Global Headquarters</h3>
                    <p>Visit our main office or connect with regional representatives worldwide.</p>
                    <div class="contact-details">
                        <div class="contact-address">
                            <i class='bx bx-map-pin'></i>
                            123 Innovation Drive<br>
                            Luxembourg, LU 1234
                        </div>
                        <div class="contact-timezone">
                            <i class='bx bx-time-five'></i> CET (UTC+1)
                        </div>
                    </div>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class='bx bx-group'></i>
                </div>
                <div class="contact-content">
                    <h3>Partnerships</h3>
                    <p>Interested in collaborating or integrating with our platform?</p>
                    <div class="contact-details">
                        <a href="mailto:partnerships@envmonitor.com" class="contact-link">
                            <i class='bx bx-envelope'></i> partnerships@envmonitor.com
                        </a>
                        <a href="tel:+352600000001" class="contact-link">
                            <i class='bx bx-phone'></i> +352 600 000 001
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-form-section">
            <div class="form-container">
                <h2>Send us a Message</h2>
                <p>We typically respond within 24 hours</p>

                <form class="contact-form" id="contactForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contactName">Full Name</label>
                            <input type="text" id="contactName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="contactEmail">Email Address</label>
                            <input type="email" id="contactEmail" name="email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contactSubject">Subject</label>
                        <select id="contactSubject" name="subject" required>
                            <option value="">Select a topic</option>
                            <option value="support">Technical Support</option>
                            <option value="partnership">Partnership Inquiry</option>
                            <option value="sales">Sales Question</option>
                            <option value="feedback">Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contactMessage">Message</label>
                        <textarea id="contactMessage" name="message" rows="5" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-send'></i> Send Message
                    </button>
                </form>
            </div>

            <div class="contact-info-sidebar">
                <h3>Why Choose Us?</h3>
                <ul class="info-list">
                    <li><i class='bx bx-check-circle'></i> 24/7 Technical Support</li>
                    <li><i class='bx bx-check-circle'></i> Expert Environmental Consultants</li>
                    <li><i class='bx bx-check-circle'></i> Custom Integration Services</li>
                    <li><i class='bx bx-check-circle'></i> Comprehensive Documentation</li>
                    <li><i class='bx bx-check-circle'></i> Training & Onboarding</li>
                </ul>

                <div class="social-links">
                    <h4>Follow Us</h4>
                    <div class="social-icons">
                        <a href="#" class="social-link"><i class='bx bxl-twitter'></i></a>
                        <a href="#" class="social-link"><i class='bx bxl-linkedin'></i></a>
                        <a href="#" class="social-link"><i class='bx bxl-github'></i></a>
                        <a href="#" class="social-link"><i class='bx bxl-youtube'></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>