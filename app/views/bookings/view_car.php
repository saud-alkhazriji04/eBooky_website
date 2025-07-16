<?php
// app/views/bookings/view_car.php
function safe_html($value) { return is_string($value) ? htmlspecialchars($value) : ''; }
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
$vehicle = $details['vehicle'] ?? '';
$provider = $details['provider'] ?? '';
$pickup = $details['pickup'] ?? '';
$dropoff = $details['dropoff'] ?? '';
$date = $details['date'] ?? '';
$price = $details['price'] ?? '';
$currency = $details['currency'] ?? '';
$seats = $details['seats'] ?? '';
$email = $details['email'] ?? '';
$booking_ref = $booking['booking_ref'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Booking Details</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <style>
        .booking-details-card{max-width:600px;margin:2em auto;padding:2em;background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,0.07);}
        .booking-details-card h2{margin-bottom:0.7em;}
        .booking-details-card .meta{color:#888;font-size:1.1em;margin-bottom:0.5em;}
        .booking-details-card .row{margin-bottom:0.5em;}
        .booking-details-card strong{font-weight:600;}
    </style>
</head>
<body>
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<main>
    <div class="booking-details-card">
        <?php if (!empty($details['image'])): ?>
            <img src="<?php echo safe_html($details['image']); ?>" alt="Car Image" style="max-width:100%;border-radius:10px;margin-bottom:1em;">
        <?php endif; ?>
        <h2><?php echo safe_html($vehicle ?: 'Car Rental'); ?></h2>
        <div class="meta">
            <strong>Booking Reference:</strong> <?php echo safe_html($booking_ref); ?>
        </div>
        <div class="row"><strong>Date & Time:</strong> <?php echo safe_html($date); ?></div>
        <div class="row"><strong>Provider:</strong> <?php echo safe_html($provider); ?></div>
        <div class="row"><strong>Pickup:</strong> <?php echo safe_html($pickup); ?></div>
        <div class="row"><strong>Dropoff:</strong> <?php echo safe_html($dropoff); ?></div>
        <div class="row"><strong>Seats:</strong> <?php echo safe_html($seats); ?></div>
        <div class="row"><strong>Total Paid:</strong> <?php echo safe_html($price); ?> <?php echo safe_html($currency); ?></div>
        <div class="row"><strong>Email:</strong> <?php echo safe_html($email); ?></div>
        <a href="<?php echo $base_path; ?>bookings" class="btn-primary">Back to My Bookings</a>
    </div>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html> 