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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Collections</title>
    <link rel="stylesheet" href="../MyStyle.css">
</head>

<body>
    <?= NavigationBarE(); ?>
    <div class="main_container_Collection">
        <!-- Sections row -->
        <div class="sections_container">
            <div class="Collections_container active" data-section="my">My Collections</div>
            <div class="Collections_shared_container" data-section="shared">Shared Collections</div>
        </div>

        <!-- Section information -->
        <div class="section_info" id="sectionInfo">
            <h2>My Collections</h2>
            <p>Here you can view and manage all your personal collections. Add new items, edit existing ones, or explore your past collections.</p>

            <ul class="collections-list">
                <li>Create and organize collections</li>
                <li>Add measurements from stations</li>
                <li>Edit collection details</li>
                <li>Export collection data</li>
            </ul>

            <div class="collection-actions">
                <button class="collection-btn btn-save" id="createCollectionBtn">
                    <i class="fas fa-plus"></i> Create Collection
                </button>
                <button class="collection-btn btn-approve" id="viewCollectionsBtn">
                    <i class="fas fa-eye"></i> View All
                </button>
                <button class="collection-btn btn-cancel" id="exportBtn">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>

</body>

</html>