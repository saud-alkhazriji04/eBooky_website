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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Seat - Ebooky</title>
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
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            transition: all 0.2s;
            position: relative;
        }
        
        .seat.available {
            background: #e8f5e8;
            border-color: #28a745;
        }
        
        .seat.available:hover {
            background: #d4edda;
            transform: scale(1.05);
        }
        
        .seat.selected {
            background: #007bff;
            border-color: #0056b3;
            color: white;
        }
        
        .seat.occupied {
            background: #f8d7da;
            border-color: #dc3545;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .seat.premium {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            border-color: #ffc107;
        }
        
        .seat.exit-row {
            background: linear-gradient(45deg, #17a2b8, #20c997);
            border-color: #17a2b8;
            color: white;
        }
        
        .aisle {
            width: 60px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #666;
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
        
        .seat-details {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .seat-price {
            font-weight: bold;
            color: #28a745;
        }
        
        .continue-btn {
            background: #007bff;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .continue-btn:hover {
            background: #0056b3;
        }
        
        .continue-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .no-seatmap {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    
    <main class="seatmap-container">
        <div class="flight-info">
            <h2>Select Your Seat</h2>
            <p><strong>Flight:</strong> 
                <?php 
                    $carrier = $flightOffer['itineraries'][0]['segments'][0]['carrierCode'];
                    $carrier_name = $airline_map[$carrier] ?? $carrier;
                    echo htmlspecialchars($carrier_name);
                    if ($carrier_name !== $carrier) echo ' (' . htmlspecialchars($carrier) . ')';
                    echo ' ' . htmlspecialchars($flightOffer['itineraries'][0]['segments'][0]['number']);
                ?>
            </p>
            <p><strong>Route:</strong> 
                <?php echo $flightOffer['itineraries'][0]['segments'][0]['departure']['iataCode']; ?> → 
                <?php echo $flightOffer['itineraries'][0]['segments'][0]['arrival']['iataCode']; ?>
            </p>
            <p><strong>Date:</strong> 
                <?php echo date('M j, Y', strtotime($flightOffer['itineraries'][0]['segments'][0]['departure']['at'])); ?>
            </p>
        </div>

        <?php if ($seatmapData && isset($seatmapData['data'][0]['decks'][0]['facilities'])): ?>
            <div class="seatmap-section">
                <h3>Cabin Layout</h3>
                
                <div class="seat-legend">
                    <div class="legend-item">
                        <div class="legend-seat available" style="background: #e8f5e8; border-color: #28a745;"></div>
                        <span>Available</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat selected" style="background: #007bff; border-color: #0056b3;"></div>
                        <span>Selected</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat occupied" style="background: #f8d7da; border-color: #dc3545;"></div>
                        <span>Occupied</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat premium" style="background: linear-gradient(45deg, #ffd700, #ffed4e); border-color: #ffc107;"></div>
                        <span>Premium</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-seat exit-row" style="background: linear-gradient(45deg, #17a2b8, #20c997); border-color: #17a2b8;"></div>
                        <span>Exit Row</span>
                    </div>
                </div>

                <div class="cabin-layout">
                    <?php 
                    $facilities = $seatmapData['data'][0]['decks'][0]['facilities'];
                    $rows = [];
                    
                    // Group seats by row
                    foreach ($facilities as $facility) {
                        if (isset($facility['coordinates']['row'])) {
                            $row = $facility['coordinates']['row'];
                            if (!isset($rows[$row])) {
                                $rows[$row] = [];
                            }
                            $rows[$row][] = $facility;
                        }
                    }
                    ksort($rows);
                    
                    foreach ($rows as $rowNum => $seats): ?>
                        <div class="seat-row">
                            <div class="row-number"><?php echo $rowNum; ?></div>
                            <?php 
                            // Sort seats by column
                            usort($seats, function($a, $b) {
                                return $a['coordinates']['column'] <=> $b['coordinates']['column'];
                            });
                            
                            foreach ($seats as $seat): 
                                $seatClass = 'available';
                                $seatText = $seat['coordinates']['column'];
                                $seatPrice = '';
                                
                                if (isset($seat['characteristics'])) {
                                    foreach ($seat['characteristics'] as $char) {
                                        if ($char['code'] === 'OCCUPIED') {
                                            $seatClass = 'occupied';
                                        } elseif ($char['code'] === 'EXIT_ROW') {
                                            $seatClass = 'exit-row';
                                            $seatPrice = ' +$50';
                                        } elseif ($char['code'] === 'PREMIUM') {
                                            $seatClass = 'premium';
                                            $seatPrice = ' +$25';
                                        }
                                    }
                                }
                            ?>
                                <div class="seat <?php echo $seatClass; ?>" 
                                     data-seat="<?php echo $rowNum . $seat['coordinates']['column']; ?>"
                                     data-price="<?php echo $seatPrice; ?>"
                                     data-row="<?php echo $rowNum; ?>"
                                     data-column="<?php echo $seat['coordinates']['column']; ?>">
                                    <?php echo $seat['coordinates']['column']; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="seat-details" id="seatDetails" style="display: none;">
                    <h4>Selected Seats: <span id="selectedSeats"></span></h4>
                    <p id="seatPrice"></p>
                    <p id="passengerInfo" style="color: #666; font-size: 0.9rem;"></p>
                </div>

                <form id="seatForm" method="POST" action="<?php echo $base_path; ?>flights/payment">
                    <input type="hidden" name="selected_seats" id="selectedSeatsInput">
                    <input type="hidden" name="seat_price" id="seatPriceInput">
                    <button type="submit" class="continue-btn" id="continueBtn" disabled>
                        Continue to Payment
                    </button>
                </form>
            </div>
        <?php else: ?>
            <?php 
            // 70% chance to show custom seatmap, 30% chance to show "not available"
            $showCustomSeatmap = (mt_rand(1, 100) <= 70);
            ?>
            
            <?php if ($showCustomSeatmap): ?>
                <div class="seatmap-section">
                    <h3>Cabin Layout</h3>
                    <p style="color: #666; font-style: italic; margin-bottom: 1rem;">Seat availability is estimated based on typical cabin layouts.</p>
                    
                    <div class="seat-legend">
                        <div class="legend-item">
                            <div class="legend-seat available" style="background: #e8f5e8; border-color: #28a745;"></div>
                            <span>Available</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-seat selected" style="background: #007bff; border-color: #0056b3;"></div>
                            <span>Selected</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-seat occupied" style="background: #f8d7da; border-color: #dc3545;"></div>
                            <span>Occupied</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-seat premium" style="background: linear-gradient(45deg, #ffd700, #ffed4e); border-color: #ffc107;"></div>
                            <span>Premium</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-seat exit-row" style="background: linear-gradient(45deg, #17a2b8, #20c997); border-color: #17a2b8;"></div>
                            <span>Exit Row</span>
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
                                    // Random seat status
                                    $rand = mt_rand(1, 100);
                                    $seatClass = 'available';
                                    $seatPrice = '';
                                    
                                    if ($rand <= 60) {
                                        $seatClass = 'available';
                                    } elseif ($rand <= 80) {
                                        $seatClass = 'occupied';
                                    } elseif ($rand <= 90) {
                                        $seatClass = 'premium';
                                        $seatPrice = ' +$25';
                                    } else {
                                        $seatClass = 'exit-row';
                                        $seatPrice = ' +$50';
                                    }
                                    
                                    // Make some seats unavailable for selection (occupied)
                                    $isSelectable = ($seatClass === 'available' || $seatClass === 'premium' || $seatClass === 'exit-row');
                                ?>
                                    <div class="seat <?php echo $seatClass; ?>" 
                                         data-seat="<?php echo $rowNum . $col; ?>"
                                         data-price="<?php echo $seatPrice; ?>"
                                         data-row="<?php echo $rowNum; ?>"
                                         data-column="<?php echo $col; ?>"
                                         <?php if (!$isSelectable): ?>style="cursor: not-allowed;"<?php endif; ?>>
                                        <?php echo $col; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="seat-details" id="seatDetails" style="display: none;">
                        <h4>Selected Seats: <span id="selectedSeats"></span></h4>
                        <p id="seatPrice"></p>
                        <p id="passengerInfo" style="color: #666; font-size: 0.9rem;"></p>
                    </div>

                    <form id="seatForm" method="POST" action="<?php echo $base_path; ?>flights/payment">
                        <input type="hidden" name="selected_seats" id="selectedSeatsInput">
                        <input type="hidden" name="seat_price" id="seatPriceInput">
                        <button type="submit" class="continue-btn" id="continueBtn" disabled>
                            Continue to Payment
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="no-seatmap">
                    <h3>Seat Selection Not Available</h3>
                    <p>Seat selection is not available for this flight. You will be assigned a seat at check-in.</p>
                    <form method="POST" action="<?php echo $base_path; ?>flights/payment">
                        <button type="submit" class="continue-btn">
                            Continue to Payment
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle both real Amadeus seatmaps and custom random seatmaps
            const seats = document.querySelectorAll('.seat.available, .seat.premium, .seat.exit-row');
            const seatDetails = document.getElementById('seatDetails');
            const selectedSeatsSpan = document.getElementById('selectedSeats');
            const seatPriceSpan = document.getElementById('seatPrice');
            const passengerInfoSpan = document.getElementById('passengerInfo');
            const selectedSeatsInput = document.getElementById('selectedSeatsInput');
            const seatPriceInput = document.getElementById('seatPriceInput');
            const continueBtn = document.getElementById('continueBtn');
            
            // Get passenger count from flight offer (stored in session)
            const passengerCount = <?php 
                // Force use of session search params for passenger count
                if (isset($_SESSION['search_params']['passengers'])) {
                    echo (int)$_SESSION['search_params']['passengers'];
                } else {
                    echo 1; // Default to 1 if we can't determine
                }
            ?>;
            let selectedSeats = [];
            let totalSeatPrice = 0;
            
            seats.forEach(seat => {
                seat.addEventListener('click', function() {
                    const seatId = this.dataset.seat;
                    const price = this.dataset.price || '';
                    const seatPrice = price ? parseFloat(price.replace(/[^0-9.-]+/g, '')) : 0;
                    
                    // Check if seat is already selected
                    const seatIndex = selectedSeats.findIndex(s => s.id === seatId);
                    
                    if (seatIndex !== -1) {
                        // Deselect seat
                        this.classList.remove('selected');
                        selectedSeats.splice(seatIndex, 1);
                        totalSeatPrice -= seatPrice;
                    } else {
                        // Check if we can select more seats
                        if (selectedSeats.length < passengerCount) {
                            // Select seat
                            this.classList.add('selected');
                            selectedSeats.push({
                                id: seatId,
                                price: seatPrice
                            });
                            totalSeatPrice += seatPrice;
                        } else {
                            // Show alert that max seats reached
                            alert(`You can only select ${passengerCount} seat(s) for ${passengerCount} passenger(s).`);
                            return;
                        }
                    }
                    
                    // Update display
                    if (selectedSeats.length > 0) {
                        const seatIds = selectedSeats.map(s => s.id).join(', ');
                        selectedSeatsSpan.textContent = seatIds;
                        seatPriceSpan.textContent = totalSeatPrice > 0 ? `Additional cost: +$${totalSeatPrice}` : 'No additional cost';
                        passengerInfoSpan.textContent = `Selected ${selectedSeats.length} of ${passengerCount} required seat(s)`;
                        seatDetails.style.display = 'block';
                        
                        // Update form inputs
                        selectedSeatsInput.value = JSON.stringify(selectedSeats);
                        seatPriceInput.value = totalSeatPrice > 0 ? `+$${totalSeatPrice}` : '';
                        
                        // Enable/disable continue button
                        continueBtn.disabled = selectedSeats.length !== passengerCount;
                    } else {
                        seatDetails.style.display = 'none';
                        selectedSeatsInput.value = '';
                        seatPriceInput.value = '';
                        continueBtn.disabled = true;
                    }
                });
            });
        });
    </script>
</body>
</html> 