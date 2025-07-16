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

// Load airline map
$airline_map = [];
$airline_map_path = __DIR__ . '/../../../airline_map.json';
if (file_exists($airline_map_path)) {
    $airline_map = json_decode(file_get_contents($airline_map_path), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <style>
        .confirmation-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 2rem;
            max-width: 600px;
            margin: 2rem auto;
        }
        .booking-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        .seat-info {
            background: #e8f5e8;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 4px solid #28a745;
        }
        .success-icon {
            color: #28a745;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <main>
        <div class="confirmation-card">
            <div class="success-icon">✓</div>
            <h2>Booking Confirmed!</h2>
            <p>Your flight has been successfully booked.</p>
            
            <div class="booking-details">
                <h3>Booking Information</h3>
                <p><strong>Booking Reference:</strong> <?php echo htmlspecialchars($bookingRef); ?></p>
                
                <?php
                // Get booking details from database
                $db = require __DIR__ . '/../../db.php';
                $stmt = $db->prepare('SELECT details FROM bookings WHERE booking_ref = ?');
                $stmt->execute([$bookingRef]);
                $booking = $stmt->fetch();
                
                if ($booking) {
                    $details = json_decode($booking['details'], true);
                    $segment = $details['itineraries'][0]['segments'][0];
                ?>
                    <p><strong>Flight:</strong> <?php 
                        $carrier = $segment['carrierCode'];
                        $carrier_name = $airline_map[$carrier] ?? $carrier;
                        if ($carrier_name !== $carrier) {
                            echo htmlspecialchars($carrier_name) . ' (' . htmlspecialchars($carrier) . ') ' . htmlspecialchars($segment['number']);
                        } else {
                            echo htmlspecialchars($carrier) . ' ' . htmlspecialchars($segment['number']);
                        }
                    ?></p>
                    <p><strong>From:</strong> <?php echo $segment['departure']['iataCode']; ?> @ <?php echo date('D, M j, H:i', strtotime($segment['departure']['at'])); ?></p>
                    <p><strong>To:</strong> <?php echo $segment['arrival']['iataCode']; ?> @ <?php echo date('D, M j, H:i', strtotime($segment['arrival']['at'])); ?></p>
                    
                    <?php if (!empty($details['selected_seats'])): ?>
                        <div class="seat-info">
                            <?php 
                            if (is_string($details['selected_seats'])) {
                                // First decode HTML entities, then try to decode JSON
                                $decodedString = html_entity_decode($details['selected_seats'], ENT_QUOTES, 'UTF-8');
                                $decoded = json_decode($decodedString, true);
                                if (is_array($decoded)) {
                                    // It's JSON array of seat objects
                                    $seatIds = array_map(function($seat) { 
                                        return is_array($seat) ? $seat['id'] : $seat; 
                                    }, $decoded);
                                    echo '<p><strong>Selected Seats:</strong> ' . htmlspecialchars(implode(', ', $seatIds)) . '</p>';
                                } else {
                                    // It's a simple string
                                    echo '<p><strong>Selected Seats:</strong> ' . htmlspecialchars($details['selected_seats']) . '</p>';
                                }
                            } else {
                                // Handle new format (multiple seats as JSON array)
                                $seatIds = array_map(function($seat) { 
                                    return is_array($seat) ? $seat['id'] : $seat; 
                                }, $details['selected_seats']);
                                echo '<p><strong>Selected Seats:</strong> ' . htmlspecialchars(implode(', ', $seatIds)) . '</p>';
                            }
                            ?>
                            <?php if (!empty($details['seat_upgrade'])): ?>
                                <p><strong>Seat Upgrade:</strong> <?php echo htmlspecialchars($details['seat_upgrade']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="seat-info" style="background: #fff3cd; border-left-color: #ffc107;">
                            <p><strong>Seat Assignment:</strong> <em>You will be assigned a seat at check-in</em></p>
                        </div>
                    <?php endif; ?>
                    
                    <p><strong>Total Amount:</strong> 
                        <?php 
                        $amount = isset($details['total_amount']) ? $details['total_amount'] : $details['price']['total'];
                        echo number_format($amount, 2) . ' ' . $details['price']['currency']; 
                        ?>
                    </p>
                <?php } ?>
            </div>
            
            <p><em>You will receive a confirmation email shortly with all the details.</em></p>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="<?php echo $base_path; ?>" class="flight-book-btn">Back to Home</a>
                <a href="<?php echo $base_path; ?>bookings" class="flight-book-btn" style="margin-left: 1rem;">View My Bookings</a>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html> 