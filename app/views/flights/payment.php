<?php
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Payment - Ebooky</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <main class="payment-page">
        <div class="container">
            <div class="payment-container">
                <div class="booking-summary">
                    <h2>Booking Summary</h2>
                    <div class="flight-details">
                        <?php $segment = $flightOffer['itineraries'][0]['segments'][0]; ?>
                        <h3>Flight: <?php echo $segment['departure']['iataCode']; ?> → <?php echo $segment['arrival']['iataCode']; ?></h3>
                        <div class="location">
                            <b>From:</b> <?php echo $segment['departure']['iataCode']; ?> @ <?php echo date('M j, Y, H:i', strtotime($segment['departure']['at'])); ?><br>
                            <b>To:</b> <?php echo $segment['arrival']['iataCode']; ?> @ <?php echo date('M j, Y, H:i', strtotime($segment['arrival']['at'])); ?><br>
                        </div>
                        <div class="carrier">
                            <span class="label">Carrier:</span>
                            <span class="value"><?php 
                                $carrier = $segment['carrierCode'];
                                $carrier_name = $airline_map[$carrier] ?? $carrier;
                                echo htmlspecialchars($carrier_name);
                                if ($carrier_name !== $carrier) echo ' (' . htmlspecialchars($carrier) . ')';
                            ?></span>
                        </div>
                        <?php if (!empty($_POST['selected_seats']) || !empty($_SESSION['selected_seats'])): ?>
                            <div class="seats">
                                <span class="label">Selected Seats:</span>
                                <span class="value">
                                <?php 
                                $selectedSeats = !empty($_POST['selected_seats']) ? json_decode($_POST['selected_seats'], true) : json_decode($_SESSION['selected_seats'], true);
                                if (is_array($selectedSeats)) {
                                    $seatIds = array_map(function($seat) { return $seat['id']; }, $selectedSeats);
                                    echo htmlspecialchars(implode(', ', $seatIds));
                                } else {
                                    echo htmlspecialchars($_POST['selected_seats'] ?? $_SESSION['selected_seats']);
                                }
                                ?>
                                </span>
                                <?php if (!empty($_POST['seat_price']) || !empty($_SESSION['seat_price'])): ?>
                                    <br><span class="label">Seat Upgrade:</span>
                                    <span class="value"><?php echo htmlspecialchars($_POST['seat_price'] ?? $_SESSION['seat_price']); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="price-breakdown">
                        <h3>Price Breakdown</h3>
                        <div class="price-item">
                            <span>Base Price</span>
                            <span><?php echo $flightOffer['price']['total'] . ' ' . $flightOffer['price']['currency']; ?></span>
                        </div>
                        <?php 
                        $totalPrice = (float)$flightOffer['price']['total'];
                        $seatPrice = 0;
                        if (!empty($_POST['seat_price'])) {
                            $seatPrice = (float)str_replace(['+$', '$'], '', $_POST['seat_price']);
                        } elseif (!empty($_SESSION['seat_price'])) {
                            $seatPrice = (float)str_replace(['+$', '$'], '', $_SESSION['seat_price']);
                        }
                        $totalPrice += $seatPrice;
                        ?>
                        <?php if ($seatPrice > 0): ?>
                        <div class="price-item">
                            <span>Seat Upgrade</span>
                            <span><?php echo number_format($seatPrice, 2) . ' ' . $flightOffer['price']['currency']; ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="price-total">
                            <span>Total</span>
                            <span><?php echo number_format($totalPrice, 2) . ' ' . $flightOffer['price']['currency']; ?></span>
                        </div>
                    </div>
                </div>
                <div class="payment-form">
                    <h2>Payment Information</h2>
                    <form method="post" action="<?php echo $base_path; ?>flights/charge" class="payment-form-inner">
                        <input type="hidden" name="amount" value="<?php echo $totalPrice; ?>">
                        <input type="hidden" name="currency" value="<?php echo $flightOffer['price']['currency']; ?>">
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" name="card_number" id="card_number" 
                                   placeholder="1234 5678 9012 3456" required 
                                   pattern="[0-9\s]{13,19}" maxlength="19">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry">Expiry Date</label>
                                <input type="text" name="expiry" id="expiry" 
                                       placeholder="MM/YY" required 
                                       pattern="[0-9]{2}/[0-9]{2}" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" name="cvv" id="cvv" 
                                       placeholder="123" required 
                                       pattern="[0-9]{3,4}" maxlength="4">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cardholder_name">Cardholder Name</label>
                            <input type="text" name="cardholder_name" id="cardholder_name" 
                                   placeholder="John Doe" required>
                        </div>
                        <div class="form-group">
                            <label for="billing_address">Billing Address</label>
                            <textarea name="billing_address" id="billing_address" 
                                      placeholder="Enter your billing address" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="email">Email for Confirmation</label>
                            <input type="email" name="email" id="email" 
                                   value="<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" name="phone" id="phone" 
                                   placeholder="+1 (555) 123-4567" required>
                        </div>
                        <div class="terms">
                            <label class="checkbox-label">
                                <input type="checkbox" required>
                                <span class="checkmark"></span>
                                I agree to the <a href="#" target="_blank">Terms and Conditions</a> and 
                                <a href="#" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        <button type="submit" class="btn-pay">
                            Pay <?php echo number_format($totalPrice, 2) . ' ' . $flightOffer['price']['currency']; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
    <style>
    .payment-page {
        padding: 2rem 0;
        background: #f8f9fa;
        min-height: calc(100vh - 200px);
    }
    .payment-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
    }
    .booking-summary,
    .payment-form {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .booking-summary h2,
    .payment-form h2 {
        margin-bottom: 1.5rem;
        color: #333;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 0.5rem;
    }
    .flight-details h3 {
        color: #333;
        margin-bottom: 1rem;
        font-size: 1.25rem;
    }
    .location {
        color: #666;
        margin-bottom: 1rem;
    }
    .dates,
    .guests,
    .rooms,
    .room-type,
    .seats,
    .carrier {
        margin-bottom: 0.75rem;
    }
    .date-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    .label {
        font-weight: 500;
        color: #666;
    }
    .value {
        color: #333;
    }
    .price-breakdown {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f0f0f0;
    }
    .price-breakdown h3 {
        margin-bottom: 1rem;
        color: #333;
    }
    .price-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        color: #666;
    }
    .price-total {
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #f0f0f0;
        font-weight: bold;
        font-size: 1.1rem;
        color: #333;
    }
    .payment-form-inner {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .form-group {
        display: flex;
        flex-direction: column;
    }
    .form-group label {
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #333;
    }
    .form-group input,
    .form-group textarea {
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .terms {
        margin-top: 1rem;
    }
    .checkbox-label {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.9rem;
        color: #666;
    }
    .checkbox-label input[type="checkbox"] {
        margin: 0;
        width: auto;
    }
    .checkbox-label a {
        color: #667eea;
        text-decoration: none;
    }
    .checkbox-label a:hover {
        text-decoration: underline;
    }
    .btn-pay {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 500;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        margin-top: 1rem;
    }
    .btn-pay:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    }
    @media (max-width: 768px) {
        .payment-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .form-row {
            grid-template-columns: 1fr;
        }
        .booking-summary,
        .payment-form {
            padding: 1.5rem;
        }
    }
    </style>
    <script>
    // Format card number with spaces
    const hotelCardNumber = document.getElementById('card_number');
    if (hotelCardNumber) {
        hotelCardNumber.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.replace(/\s/g, '').replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = formattedValue;
        });
    }
    // Format expiry date as MM/YY and validate
    const hotelExpiry = document.getElementById('expiry');
    if (hotelExpiry) {
        hotelExpiry.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
        hotelExpiry.addEventListener('blur', function(e) {
            const val = e.target.value;
            const match = val.match(/^(\d{2})\/(\d{2})$/);
            let error = '';
            if (!match) {
                error = 'Expiry must be in MM/YY format.';
            } else {
                const mm = parseInt(match[1], 10);
                const yy = parseInt(match[2], 10);
                const now = new Date();
                const curYY = parseInt(now.getFullYear().toString().slice(-2), 10);
                if (mm < 1 || mm > 12) {
                    error = 'Month must be 01-12.';
                } else if (yy < curYY) {
                    error = 'Year must be this year or later.';
                }
            }
            let errEl = document.getElementById('expiry-error');
            if (!errEl) {
                errEl = document.createElement('div');
                errEl.id = 'expiry-error';
                errEl.style.color = '#b00';
                errEl.style.fontSize = '0.95em';
                hotelExpiry.parentNode.appendChild(errEl);
            }
            errEl.textContent = error;
        });
    }
    // Only allow digits for CVV
    const hotelCVV = document.getElementById('cvv');
    if (hotelCVV) {
        hotelCVV.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }
    // Prevent form submit if expiry is invalid
    const hotelForm = document.querySelector('.payment-form-inner');
    if (hotelForm) {
        hotelForm.addEventListener('submit', function(e) {
            const val = hotelExpiry.value;
            const match = val.match(/^(\d{2})\/(\d{2})$/);
            let error = '';
            if (!match) {
                error = 'Expiry must be in MM/YY format.';
            } else {
                const mm = parseInt(match[1], 10);
                const yy = parseInt(match[2], 10);
                const now = new Date();
                const curYY = parseInt(now.getFullYear().toString().slice(-2), 10);
                if (mm < 1 || mm > 12) {
                    error = 'Month must be 01-12.';
                } else if (yy < curYY) {
                    error = 'Year must be this year or later.';
                }
            }
            let errEl = document.getElementById('expiry-error');
            if (!errEl) {
                errEl = document.createElement('div');
                errEl.id = 'expiry-error';
                errEl.style.color = '#b00';
                errEl.style.fontSize = '0.95em';
                hotelExpiry.parentNode.appendChild(errEl);
            }
            errEl.textContent = error;
            if (error) {
                e.preventDefault();
                hotelExpiry.focus();
            }
        });
    }
    </script>
</body>
</html>
<?php
function getUserCurrency() {
    if (!empty($_SESSION['user']['country'])) {
        $map = [
            'United States' => 'usd',
            'United Kingdom' => 'gbp',
            'Bahrain' => 'bhd',
            'Saudi Arabia' => 'sar',
            'United Arab Emirates' => 'aed',
            'France' => 'eur',
            'Germany' => 'eur',
            'India' => 'inr',
            'Canada' => 'cad',
            'Australia' => 'aud',
        ];
        return $map[$_SESSION['user']['country']] ?? 'usd';
    }
    return 'usd';
}
?> 