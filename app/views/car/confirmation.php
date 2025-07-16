<?php 
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
$booking = $booking ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Booking Confirmation - Ebooky</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
</head>
<body>
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<main class="confirmation-page">
    <div class="container">
        <div class="confirmation-box">
            <h1>Booking Confirmed!</h1>
            <p class="success-message">Thank you for booking with Ebooky. Your transfer reservation is confirmed.</p>
            <div class="booking-details">
                <h2>Booking Details</h2>
                <?php if ($booking): ?>
                    <div class="detail-row">
                        <span class="label">Booking Reference:</span>
                        <span class="value"><?= htmlspecialchars($booking['booking_ref']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Vehicle:</span>
                        <span class="value"><?= htmlspecialchars($booking['vehicle']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Provider:</span>
                        <span class="value"><?= htmlspecialchars($booking['provider']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Pickup:</span>
                        <span class="value"><?= htmlspecialchars($booking['pickup']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Dropoff:</span>
                        <span class="value"><?= htmlspecialchars($booking['dropoff']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Date & Time:</span>
                        <span class="value"><?= htmlspecialchars($booking['date']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Seats:</span>
                        <span class="value"><?= htmlspecialchars($booking['seats']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Total Paid:</span>
                        <span class="value"><?= htmlspecialchars($booking['price']) ?> <?= htmlspecialchars($booking['currency']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Email:</span>
                        <span class="value"><?= htmlspecialchars($booking['email']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="actions">
                <a href="<?php echo $base_path; ?>bookings" class="btn-primary">View My Bookings</a>
                <a href="<?php echo $base_path; ?>car/search" class="btn-secondary">Book Another Transfer</a>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>
<style>
.confirmation-page {
    padding: 3rem 0;
    background: #f8f9fa;
    min-height: calc(100vh - 200px);
}
.confirmation-box {
    background: white;
    max-width: 600px;
    margin: 0 auto;
    padding: 2.5rem 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    text-align: center;
}
.success-message {
    color: #4caf50;
    font-size: 1.2rem;
    margin-bottom: 2rem;
}
.booking-details {
    text-align: left;
    margin-bottom: 2rem;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.label {
    color: #666;
    font-weight: 500;
}
.value {
    color: #333;
}
.actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
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
.btn-secondary {
    background: #f0f0f0;
    color: #333;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.3s;
}
.btn-secondary:hover {
    background: #e2e8f0;
}
</style>
</body>
</html> 