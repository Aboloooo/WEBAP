<?php
include_once("../MyLibrary.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- CDN jQuery pull -->
    <!--  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
    <script src="../js/jquery.js"></script>
    <script src="../js/MyScript.js"></script>
    <!-- bank of icons -->
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnvMonitor - My Collections</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?= NavigationBarE(); ?>
    <section id="Collections">
        <div class="collections-header">
            <h1 class="section-title">My Collections</h1>
            <p class="section-text">Manage and organize your environmental data collections</p>
        </div>

        <div class="main_container_Collection">
            <!-- Sections row -->
            <div class="sections_container">
                <div class="Collections_container active" data-section="my">
                    <i class="fas fa-folder"></i>
                    My Collections
                </div>
                <div class="Collections_shared_container" data-section="shared">
                    <i class="fas fa-share-alt"></i>
                    Shared Collections
                </div>
            </div>

            <!-- Section information -->
            <div class="section_info" id="sectionInfo">
                <div class="loading-state">
                    <div class="loading-spinner"></div>
                    <p>Loading collections...</p>
                </div>
            </div>
        </div>
    </section>

</body>

</html>