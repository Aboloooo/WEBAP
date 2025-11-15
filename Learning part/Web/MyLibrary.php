<?php
function NavigationBarE()
{
    global $t;
?>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">MyBrand</div>
            <ul class="nav-links">
                <li><a href="index.php#Home">Home</a></li>
                <li><a href="index.php#About">About</a></li>
                <li><a href="index.php#Service">Service</a></li>
                <li><a href="index.php#Contact">Contact</a></li>
            </ul>
        </div>

    </nav>
    <div class="login_container_indexPage">
        <div id="goToLogin">
            <img src="../img/User.png" alt="not found">
            <span>username</span>
        </div>
    </div>
<?php
}
?>