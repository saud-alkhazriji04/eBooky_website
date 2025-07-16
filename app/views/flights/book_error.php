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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Error</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <main>
        <div class="error" style="max-width:600px;margin:2rem auto;">
            <?php echo $message; ?> 
        </div>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html> 