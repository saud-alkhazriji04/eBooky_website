<?php
// Calculate the correct base path
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);

// If we're in a subdirectory (like /bookings), go up to the main directory
if (strpos($script_name, '/bookings') !== false) {
    $base_path = dirname($base_path);
}

// Ensure we have a trailing slash
$base_path = rtrim($base_path, '/') . '/';

$booking = $_SESSION['view_booking'];
$details = $_SESSION['view_booking_details'];
$segment = $details['itineraries'][0]['segments'][0];

// Load airline map
$airline_map = [];
$airline_map_path = __DIR__ . '/../../../airline_map.json';
if (file_exists($airline_map_path)) {
    $airline_map = json_decode(file_get_contents($airline_map_path), true);
}

// Get selected seats
$selectedSeats = [];
if (!empty($details['selected_seats'])) {
    if (is_string($details['selected_seats'])) {
        // First decode HTML entities, then try to decode JSON
        $decodedString = html_entity_decode($details['selected_seats'], ENT_QUOTES, 'UTF-8');
        $decoded = json_decode($decodedString, true);
        if (is_array($decoded)) {
            // It's JSON array of seat objects
            $selectedSeats = array_map(function($seat) { 
                return is_array($seat) ? $seat['id'] : $seat; 
            }, $decoded);
        } else {
            // It's a simple string
            $selectedSeats = [$details['selected_seats']];
        }
    } else {
        // It's already an array
        $selectedSeats = array_map(function($seat) { 
            return is_array($seat) ? $seat['id'] : $seat; 
        }, $details['selected_seats']);
    }
}

// Debug information
error_log("Raw selected_seats: " . print_r($details['selected_seats'], true));
error_log("Processed selectedSeats: " . print_r($selectedSeats, true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Booked Seats - Ebooky</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <style>
        .seatmap-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .flight-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .seatmap-section {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .cabin-layout {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }
        
        .seat-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .row-number {
            font-weight: bold;
            min-width: 30px;
            text-align: center;
        }
        
        .seat {
            width: 40px;
            height: 40px;
            border: 2px solid #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            transition: all 0.2s;
            position: relative;
        }
        
        .seat.available {
            background: #f8f9fa;
            border-color: #dee2e6;
            color: #6c757d;
        }
        
        .seat.booked {
            background: #007bff;
            border-color: #0056b3;
            color: white;
            cursor: default;
        }
        
        .seat-legend {
            display: flex;
            gap: 2rem;
            justify-content: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .legend-seat {
            width: 20px;
            height: 20px;
            border: 1px solid #ddd;
            border-radius: 2px;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    
    <main class="seatmap-container">
        <div class="flight-info">
            <h2>Your Booked Seats</h2>
            <p><strong>Booking Reference:</strong> <?php echo htmlspecialchars($booking['booking_ref']); ?></p>
            <p><strong>Flight:</strong> 
                <?php 
                    $carrier = $segment['carrierCode'];
                    $carrier_name = $airline_map[$carrier] ?? $carrier;
                    echo htmlspecialchars($carrier_name);
                    if ($carrier_name !== $carrier) echo ' (' . htmlspecialchars($carrier) . ')';
                    echo ' ' . htmlspecialchars($segment['number']);
                ?>
            </p>
            <p><strong>Route:</strong> 
                <?php echo $segment['departure']['iataCode']; ?> → 
                <?php echo $segment['arrival']['iataCode']; ?>
            </p>
            <p><strong>Date:</strong> 
                <?php echo date('M j, Y', strtotime($segment['departure']['at'])); ?>
            </p>
            <p><strong>Your Seats:</strong> 
                <?php echo htmlspecialchars(implode(', ', $selectedSeats)); ?>
            </p>
        </div>

        <div class="seatmap-section">
            <h3>Cabin Layout - Your Seats Highlighted</h3>
            
            <div class="seat-legend">
                <div class="legend-item">
                    <div class="legend-seat" style="background: #007bff; border-color: #0056b3;"></div>
                    <span>Your Seats</span>
                </div>
                <div class="legend-item">
                    <div class="legend-seat" style="background: #f8f9fa; border-color: #dee2e6;"></div>
                    <span>Other Seats</span>
                </div>
            </div>

            <div class="cabin-layout">
                <?php 
                // Generate a typical aircraft layout (e.g., 3-3 configuration)
                $rows = range(1, 30); // 30 rows
                $columns = ['A', 'B', 'C', 'D', 'E', 'F']; // 6 seats per row
                
                foreach ($rows as $rowNum): ?>
                    <div class="seat-row">
                        <div class="row-number"><?php echo $rowNum; ?></div>
                        <?php foreach ($columns as $col): 
                            $seatId = $rowNum . $col;
                            $isBooked = in_array($seatId, $selectedSeats);
                            
                            // Only show booked seats and other seats
                            if ($isBooked) {
                                $seatClass = 'booked';
                            } else {
                                $seatClass = 'available';
                            }
                        ?>
                            <div class="seat <?php echo $seatClass; ?>" 
                                 data-seat="<?php echo $seatId; ?>">
                                <?php echo $col; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="<?php echo $base_path; ?>bookings" class="back-btn">Back to My Bookings</a>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html> 