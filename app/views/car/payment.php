<?php 
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
$offer = $offer ?? null;
?>
<link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<main class="payment-page">
    <div class="container">
        <div class="payment-container">
            <div class="booking-summary">
                <h2>Booking Summary</h2>
                <?php if ($offer): ?>
                    <div class="car-details">
                        <h3><?= htmlspecialchars($offer['vehicle']['description'] ?? 'Vehicle') ?></h3>
                        <div class="location">
                            <b>Pickup:</b> <?= htmlspecialchars($offer['start']['locationCode'] ?? '') ?><br>
                            <b>Dropoff:</b> <?= htmlspecialchars($offer['end']['locationCode'] ?? '') ?><br>
                        </div>
                        <div class="dates">
                            <div class="date-item">
                                <span class="label">Date & Time:</span>
                                <span class="value"><?= htmlspecialchars($offer['start']['dateTime'] ?? '') ?></span>
                            </div>
                        </div>
                        <div class="provider">
                            <span class="label">Provider:</span>
                            <span class="value"><?= htmlspecialchars($offer['serviceProvider']['name'] ?? '') ?></span>
                        </div>
                        <div class="seats">
                            <span class="label">Seats:</span>
                            <span class="value"><?= htmlspecialchars($offer['vehicle']['seats'][0]['count'] ?? '') ?></span>
                        </div>
                    </div>
                    <div class="price-breakdown">
                        <h3>Price Breakdown</h3>
                        <div class="price-item">
                            <span>Base Price</span>
                            <span><?= htmlspecialchars($offer['quotation']['base']['monetaryAmount'] ?? $offer['quotation']['monetaryAmount']) ?> <?= htmlspecialchars($offer['quotation']['currencyCode'] ?? '') ?></span>
                        </div>
                        <div class="price-item">
                            <span>Taxes & Fees</span>
                            <span><?= htmlspecialchars($offer['quotation']['totalTaxes']['monetaryAmount'] ?? '0.00') ?> <?= htmlspecialchars($offer['quotation']['currencyCode'] ?? '') ?></span>
                        </div>
                        <div class="price-total">
                            <span>Total</span>
                            <span><?= htmlspecialchars($offer['quotation']['monetaryAmount'] ?? '') ?> <?= htmlspecialchars($offer['quotation']['currencyCode'] ?? '') ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="payment-form">
                <h2>Payment Information</h2>
                <form method="POST" action="<?php echo $base_path; ?>car/confirmation" class="payment-form-inner">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" name="card_number" id="card_number" maxlength="19" required placeholder="1234 5678 9012 3456">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry">Expiry Date</label>
                            <input type="text" name="expiry" id="expiry" maxlength="5" placeholder="MM/YY" required>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" name="cvv" id="cvv" maxlength="4" placeholder="123" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cardholder">Cardholder Name</label>
                        <input type="text" name="cardholder" id="cardholder" required placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label for="billing_address">Billing Address</label>
                        <textarea name="billing_address" id="billing_address" required placeholder="Enter your billing address"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="email">Email for Confirmation</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone" required placeholder="+1 (555) 123-4567">
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
                        Pay <?= htmlspecialchars($offer['quotation']['monetaryAmount'] ?? '') ?> <?= htmlspecialchars($offer['quotation']['currencyCode'] ?? '') ?>
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
.car-details h3 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.25rem;
}
.location {
    color: #666;
    margin-bottom: 1rem;
}
.dates,
.provider,
.seats {
    margin-bottom: 0.75rem;
}
.date-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}
.price-breakdown {
    margin-top: 2rem;
}
.price-breakdown h3 {
    margin-bottom: 1rem;
    font-size: 1.1rem;
    color: #333;
}
.price-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}
.price-total {
    display: flex;
    justify-content: space-between;
    font-size: 1.15em;
    font-weight: 600;
    margin-top: 1rem;
}
.form-group {
    margin-bottom: 1.2em;
    display: flex;
    flex-direction: column;
}
.form-group label {
    font-weight: 500;
    margin-bottom: 0.3em;
}
.form-row {
    display: flex;
    gap: 1em;
}
.form-row .form-group {
    flex: 1;
}
.payment-form-inner input,
.payment-form-inner textarea {
    padding: 0.7em;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1em;
}
.btn-pay {
    width: 100%;
    font-size: 1.1em;
    background: linear-gradient(90deg,#6a82fb 0%,#fc5c7d 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 1em 0;
    font-weight: 600;
    margin-top: 1em;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-pay:hover {
    background: linear-gradient(90deg,#fc5c7d 0%,#6a82fb 100%);
}
.terms {
    margin-bottom: 1em;
}
.checkbox-label {
    display: flex;
    align-items: center;
    font-size: 0.98em;
}
.checkbox-label input[type="checkbox"] {
    margin-right: 0.5em;
}
</style>
<script>
// Format card number with spaces every 4 digits
const carCardNumber = document.getElementById('card_number');
if (carCardNumber) {
    carCardNumber.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = value.replace(/\s/g, '').replace(/(\d{4})/g, '$1 ').trim();
        e.target.value = formattedValue;
    });
}
// Format expiry date as MM/YY and validate
const carExpiry = document.getElementById('expiry');
if (carExpiry) {
    carExpiry.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });
    carExpiry.addEventListener('blur', function(e) {
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
            carExpiry.parentNode.appendChild(errEl);
        }
        errEl.textContent = error;
    });
}
// Only allow digits for CVV
const carCVV = document.getElementById('cvv');
if (carCVV) {
    carCVV.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
}
// Prevent form submit if expiry is invalid
const carForm = document.querySelector('.payment-form-inner');
if (carForm) {
    carForm.addEventListener('submit', function(e) {
        const val = carExpiry.value;
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
            carExpiry.parentNode.appendChild(errEl);
        }
        errEl.textContent = error;
        if (error) {
            e.preventDefault();
            carExpiry.focus();
        }
    });
}
</script> 