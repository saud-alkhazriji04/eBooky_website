<?php
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
    <title>My Bookings</title>
    <link rel="stylesheet" href="<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <main>
        <div class="container" style="max-width: 900px; margin: 0 auto;">
            <h2 style="margin-top:2rem;">My Bookings</h2>
            <?php if (empty($bookings)): ?>
                <p>You have no bookings yet.</p>
            <?php else: ?>
                <div class="booking-filters" style="display:flex;gap:1.5em;margin-bottom:1.5em;align-items:center;">
                    <div>
                        <label for="filter-type" style="font-weight:500;margin-right:0.5em;">Type:</label>
                        <select id="filter-type" class="filter-select">
                            <option value="all">All</option>
                            <option value="hotel">Hotel</option>
                            <option value="flight">Flight</option>
                            <option value="car">Car</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter-status" style="font-weight:500;margin-right:0.5em;">Status:</label>
                        <select id="filter-status" class="filter-select">
                            <option value="all">All</option>
                            <option value="booked">Booked</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter-date" style="font-weight:500;margin-right:0.5em;">Date:</label>
                        <select id="filter-date" class="filter-select">
                            <option value="all">All</option>
                            <option value="future">Future</option>
                            <option value="past">Past</option>
                        </select>
                    </div>
                </div>
                <div class="booking-list" id="booking-list">
                <?php foreach ($bookings as $b): $details = json_decode($b['details'], true); ?>
                    <?php
                    $dataDate = '';
                    if ($b['type'] === 'flight' && isset($details['itineraries'][0]['segments'][0]['departure']['at'])) {
                        $dataDate = date('Y-m-d', strtotime($details['itineraries'][0]['segments'][0]['departure']['at']));
                    } elseif ($b['type'] === 'hotel' && isset($details['booking_params']['check_in'])) {
                        $dataDate = $details['booking_params']['check_in'];
                    } elseif ($b['type'] === 'car' && isset($details['start']['dateTime'])) {
                        $dataDate = date('Y-m-d', strtotime($details['start']['dateTime']));
                    }
                    ?>
                    <div class="booking-card" data-date="<?= htmlspecialchars($dataDate) ?>">
                        <div class="booking-header">
                            <span class="badge badge-type badge-<?php echo htmlspecialchars($b['type']); ?>">
                                <?php echo ucfirst($b['type']); ?>
                            </span>
                            <span class="badge badge-status badge-<?php echo htmlspecialchars($b['status']); ?>">
                                <?php echo ucfirst($b['status']); ?>
                            </span>
                            <span class="booking-ref">Ref: <?php echo htmlspecialchars($b['booking_ref']); ?></span>
                        </div>
                        <div class="booking-details">
                            <?php if ($b['type'] === 'flight' && isset($details['itineraries'][0]['segments'][0])): ?>
                                <?php $seg = $details['itineraries'][0]['segments'][0]; ?>
                                <div class="booking-title">Flight: <?php echo $seg['departure']['iataCode']; ?> → <?php echo $seg['arrival']['iataCode']; ?></div>
                                <div class="booking-dates">
                                    <span><?php echo date('D, M j, H:i', strtotime($seg['departure']['at'])); ?></span>
                                </div>
                                <div class="booking-meta">
                                    <span>Carrier: <?php 
                                        $carrier = $seg['carrierCode'];
                                        $carrier_name = $airline_map[$carrier] ?? $carrier;
                                        echo htmlspecialchars($carrier_name);
                                        if ($carrier_name !== $carrier) echo ' (' . htmlspecialchars($carrier) . ')';
                                    ?></span>
                                    <span>Price: <?php echo $details['price']['total'] . ' ' . $details['price']['currency']; ?></span>
                                </div>
                                <?php if (!empty($details['selected_seats'])): ?>
                                    <div class="booking-seats">
                                        <strong>Seats:</strong> 
                                        <?php 
                                        if (is_string($details['selected_seats'])) {
                                            $decodedString = html_entity_decode($details['selected_seats'], ENT_QUOTES, 'UTF-8');
                                            $decoded = json_decode($decodedString, true);
                                            if (is_array($decoded)) {
                                                $seatIds = array_map(function($seat) { 
                                                    return is_array($seat) ? $seat['id'] : $seat; 
                                                }, $decoded);
                                                echo htmlspecialchars(implode(', ', $seatIds));
                                            } else {
                                                echo htmlspecialchars($details['selected_seats']);
                                            }
                                        } else {
                                            $seatIds = array_map(function($seat) { 
                                                return is_array($seat) ? $seat['id'] : $seat; 
                                            }, $details['selected_seats']);
                                            echo htmlspecialchars(implode(', ', $seatIds));
                                        }
                                        ?>
                                    </div>
                                    <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/bookings/view-seatmap?booking_id=<?php echo $b['id']; ?>" class="btn-secondary btn-sm" style="margin-top:0.5em;">View Seatmap</a>
                                <?php else: ?>
                                    <div class="booking-seats"><em>Seat assigned at check-in</em></div>
                                <?php endif; ?>
                                <?php if (!empty($details['seat_upgrade'])): ?>
                                    <div class="booking-upgrade"><strong>Seat Upgrade:</strong> <?php echo htmlspecialchars($details['seat_upgrade']); ?></div>
                                <?php endif; ?>
                            <?php elseif ($b['type'] === 'hotel' && isset($details['hotel'])): ?>
                                <?php 
                                    $hotel = $details['hotel'];
                                    $params = $details['booking_params'] ?? [];
                                    $price = $details['payment']['amount'] ?? ($details['offer']['price']['total'] ?? '');
                                    $currency = $details['payment']['currency'] ?? ($details['offer']['price']['currency'] ?? 'USD');
                                ?>
                                <div class="booking-title">Hotel: <strong><?php echo htmlspecialchars($hotel['name'] ?? ''); ?></strong></div>
                                <div class="booking-dates">
                                    <?php if (!empty($params['check_in']) && !empty($params['check_out'])): ?>
                                        <span><?php echo htmlspecialchars($params['check_in']); ?> → <?php echo htmlspecialchars($params['check_out']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="booking-meta">
                                    <?php if (!empty($price)): ?>
                                        <span>Price: $<?php echo number_format($price, 2); ?> <?php echo htmlspecialchars($currency); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/bookings/view-hotel?booking_id=<?php echo $b['id']; ?>" class="btn-secondary btn-sm" style="margin-top:0.5em;">View Details</a>
                            <?php elseif ($b['type'] === 'car' && (!empty($details['vehicle']) || !empty($details['provider']))): ?>
                                <div class="car-title-row" style="display:flex;align-items:center;gap:1em;margin-bottom:0.3em;">
                                    <?php if (!empty($details['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($details['image']); ?>" alt="Car Image" style="max-width:80px;border-radius:8px;">
                                    <?php endif; ?>
                                    <div class="booking-title">Car Rental: <strong><?php echo htmlspecialchars($details['vehicle'] ?? ''); ?></strong></div>
                                </div>
                                <div class="booking-dates">
                                    <?php if (!empty($details['date'])): ?>
                                        <span><?php echo htmlspecialchars($details['date']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="booking-meta">
                                    <?php if (!empty($details['provider'])): ?>
                                        <span>Provider: <?php echo htmlspecialchars($details['provider']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($details['price'])): ?>
                                        <span>Price: <?php echo htmlspecialchars($details['price']); ?> <?php echo htmlspecialchars($details['currency'] ?? ''); ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/bookings/view-car?booking_id=<?php echo $b['id']; ?>" class="btn-secondary btn-sm" style="margin-top:0.5em;">View Details</a>
                            <?php else: ?>
                                <div class="booking-title">N/A</div>
                            <?php endif; ?>
                        </div>
                        <div class="booking-action">
                            <?php if ($b['status'] === 'booked'): ?>
                                <form method="post" action="bookings/cancel" class="cancel-form">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <input type="text" name="reason" placeholder="Reason (optional)" class="cancel-reason">
                                    <button type="submit" class="btn-cancel">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-cancelled">-</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
    <style>
    .booking-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        margin: 2rem 0;
    }
    .booking-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        padding: 1.5rem 2rem;
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        gap: 2rem;
        justify-content: space-between;
    }
    .booking-header {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-width: 170px;
        gap: 0.5em;
    }
    .booking-ref {
        font-size: 0.95em;
        color: #888;
        margin-top: 0.5em;
        word-break: break-all;
    }
    .badge {
        display: inline-block;
        padding: 0.3em 0.9em;
        border-radius: 8px;
        font-size: 0.95em;
        font-weight: 500;
        margin-right: 0.5em;
        margin-bottom: 0.2em;
    }
    .badge-type {
        background: #e6f0ff;
        color: #003580;
    }
    .badge-type-hotel { background: #e6f0ff; color: #003580; }
    .badge-type-flight { background: #f0e6ff; color: #764ba2; }
    .badge-status {
        background: #e8f5e9;
        color: #388e3c;
    }
    .badge-status-booked { background: #e8f5e9; color: #388e3c; }
    .badge-status-cancelled { background: #ffebee; color: #b71c1c; }
    .badge-cancelled { background: #ffebee; color: #b71c1c; padding: 0.3em 1em; border-radius: 8px; }
    .booking-details {
        flex: 1;
        min-width: 200px;
    }
    .booking-title {
        font-size: 1.15em;
        font-weight: 600;
        margin-bottom: 0.5em;
    }
    .booking-dates {
        color: #666;
        margin-bottom: 0.5em;
    }
    .booking-meta {
        color: #444;
        font-size: 0.98em;
        margin-bottom: 0.5em;
        display: flex;
        flex-direction: column;
        gap: 0.3em;
        flex-wrap: wrap;
    }
    .booking-seats, .booking-upgrade {
        font-size: 0.98em;
        margin-bottom: 0.3em;
    }
    .btn-primary, .btn-secondary, .btn-cancel, .btn-sm {
        display: inline-block;
        border: none;
        border-radius: 8px;
        font-size: 1em;
        font-weight: 500;
        padding: 0.5em 1.2em;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-primary {
        background: #667eea;
        color: #fff;
    }
    .btn-primary:hover {
        background: #5a67d8;
    }
    .btn-secondary {
        background: #e0e7ef;
        color: #003580;
    }
    .btn-secondary:hover {
        background: #cfd8dc;
    }
    .btn-cancel {
        background: #b00;
        color: #fff;
        margin-left: 0.5em;
        padding: 0.5em 1.2em;
    }
    .btn-cancel:hover {
        background: #d32f2f;
    }
    .btn-sm {
        font-size: 0.93em;
        padding: 0.3em 0.9em;
    }
    .cancel-form {
        display: flex;
        align-items: center;
        gap: 0.5em;
        margin-top: 0.5em;
    }
    .cancel-reason {
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 0.4em 0.8em;
        font-size: 1em;
        min-width: 120px;
    }
    @media (max-width: 800px) {
        .booking-card {
            flex-direction: column;
            gap: 1em;
            padding: 1.2rem 1rem;
        }
        .booking-header {
            flex-direction: row;
            gap: 1em;
            min-width: 0;
        }
    }
    .filter-select {
        padding: 0.5em 1em;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 1em;
        background: #f8f9fa;
        color: #222;
        margin-right: 0.5em;
    }
    .filter-select:focus {
        outline: 2px solid #667eea;
        border-color: #667eea;
    }
    .booking-filters label {
        margin-right: 0.5em;
    }
    </style>
    <script>
    // --- Booking Filters ---
    function getBookingDate(card) {
        // Use the machine-readable data-date attribute
        let dateStr = card.getAttribute('data-date');
        if (!dateStr) return null;
        let d = new Date(dateStr);
        return isNaN(d.getTime()) ? null : d;
    }
    function filterBookings() {
        const type = document.getElementById('filter-type').value;
        const status = document.getElementById('filter-status').value;
        const date = document.getElementById('filter-date').value;
        const today = new Date();
        today.setHours(0,0,0,0);
        document.querySelectorAll('.booking-card').forEach(card => {
            let show = true;
            if (type !== 'all' && card.getAttribute('data-type') !== type) show = false;
            if (status !== 'all' && card.getAttribute('data-status') !== status) show = false;
            if (date !== 'all') {
                const d = getBookingDate(card);
                if (d) {
                    if (date === 'future' && d < today) show = false;
                    if (date === 'past' && d >= today) show = false;
                }
            }
            card.style.display = show ? '' : 'none';
        });
    }
    document.getElementById('filter-type').addEventListener('change', filterBookings);
    document.getElementById('filter-status').addEventListener('change', filterBookings);
    document.getElementById('filter-date').addEventListener('change', filterBookings);
    // --- Add data attributes to cards for filtering ---
    document.querySelectorAll('.booking-card').forEach(card => {
        // Set data-type, data-status
        const type = card.querySelector('.badge-type');
        const status = card.querySelector('.badge-status');
        card.setAttribute('data-type', type ? type.textContent.trim().toLowerCase() : '');
        card.setAttribute('data-status', status ? status.textContent.trim().toLowerCase() : '');
    });
    </script>
</body>
</html> 