<?php $base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking Error - Ebooky</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <main class="error-page">
        <div class="container">
            <div class="error-box">
                <h1>Booking Error</h1>
                <p class="error-message"><?php echo $message ?? 'An error occurred during your hotel booking.'; ?></p>
                <a href="/hotels/search" class="btn-primary">Back to Hotel Search</a>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
    <style>
    .error-page {
        padding: 3rem 0;
        background: #f8f9fa;
        min-height: calc(100vh - 200px);
    }
    .error-box {
        background: white;
        max-width: 500px;
        margin: 0 auto;
        padding: 2.5rem 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        text-align: center;
    }
    .error-message {
        color: #e53e3e;
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }
    .btn-primary {
        background: #667eea;
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.3s;
    }
    .btn-primary:hover {
        background: #5a67d8;
    }
    </style>
</body>
</html> 