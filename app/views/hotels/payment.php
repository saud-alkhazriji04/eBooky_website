<?php $base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Payment - Ebooky</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    
    <main class="payment-page">
        <div class="container">
            <div class="payment-container">
                <div class="booking-summary">
                    <h2>Booking Summary</h2>
                    <div class="hotel-details">
                        <h3><?php echo htmlspecialchars($hotelOffer['hotel']['name']); ?></h3>
                        <?php if (isset($hotelOffer['hotel']['address'])): ?>
                            <p class="location">
                                <i class="icon-location"></i>
                                <?php echo htmlspecialchars($hotelOffer['hotel']['address']['cityName'] ?? ''); ?>, 
                                <?php echo htmlspecialchars($hotelOffer['hotel']['address']['countryCode'] ?? ''); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="dates">
                            <div class="date-item">
                                <span class="label">Check-in:</span>
                                <span class="value"><?php echo date('M j, Y', strtotime($_SESSION['hotel_booking_params']['check_in'])); ?></span>
                            </div>
                            <div class="date-item">
                                <span class="label">Check-out:</span>
                                <span class="value"><?php echo date('M j, Y', strtotime($_SESSION['hotel_booking_params']['check_out'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="guests">
                            <span class="label">Guests:</span>
                            <span class="value"><?php echo $_SESSION['hotel_booking_params']['guests']; ?></span>
                        </div>
                        
                        <div class="rooms">
                            <span class="label">Rooms:</span>
                            <span class="value"><?php echo $_SESSION['hotel_booking_params']['rooms']; ?></span>
                        </div>
                        
                        <?php if (isset($hotelOffer['room']['description'])): ?>
                            <div class="room-type">
                                <span class="label">Room Type:</span>
                                <span class="value"><?php echo htmlspecialchars($hotelOffer['room']['description']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="price-breakdown">
                        <h3>Price Breakdown</h3>
                        <div class="price-item">
                            <span>Room Rate</span>
                            <span>$<?php echo number_format($hotelOffer['price']['total'], 2); ?></span>
                        </div>
                        <div class="price-item">
                            <span>Taxes & Fees</span>
                            <span>$<?php echo number_format($hotelOffer['price']['total'] * 0.15, 2); ?></span>
                        </div>
                        <div class="price-total">
                            <span>Total</span>
                            <span>$<?php echo number_format($hotelOffer['price']['total'] * 1.15, 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="payment-form">
                    <h2>Payment Information</h2>
                    <form method="post" action="<?php echo $base_path; ?>hotels/charge" class="payment-form-inner">
                        <input type="hidden" name="amount" value="<?php echo $hotelOffer['price']['total'] * 1.15; ?>">
                        <input type="hidden" name="currency" value="<?php echo $hotelOffer['price']['currency']; ?>">
                        
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
                                   value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" required>
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
                            Pay $<?php echo number_format($hotelOffer['price']['total'] * 1.15, 2); ?>
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
    
    .hotel-details h3 {
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
    .room-type {
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