<?php
// Calculate the correct base path
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);

// If we're in a subdirectory (like /flights), go up to the main directory
if (strpos($script_name, '/flights') !== false) {
    $base_path = dirname($base_path);
}

// Ensure we have a trailing slash
$base_path = rtrim($base_path, '/') . '/';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar">
    <div class="container">
        <a href="<?php echo $base_path; ?>" class="logo">Ebooky</a>
        <ul class="nav-links">
            <li><a href="<?php echo $base_path; ?>flights">Flights</a></li>
            <li><a href="<?php echo $base_path; ?>hotels/search">Hotels</a></li>
            <li><a href="<?php echo $base_path; ?>car/search">Car Rentals</a></li>
            <li><a href="<?php echo $base_path; ?>bookings">My Bookings</a></li>
            <?php if (isset($_SESSION['user'])): ?>
                <li><a href="<?php echo $base_path; ?>auth/logout">Sign Out</a></li>
            <?php else: ?>
                <li><a href="<?php echo $base_path; ?>auth/login">Sign In</a></li>
                <li><a href="<?php echo $base_path; ?>auth/register">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav> 