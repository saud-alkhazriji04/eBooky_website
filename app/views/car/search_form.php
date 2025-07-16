<?php 
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
?>
<link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<style>
.car-search-hero {
    background: linear-gradient(rgba(30,40,70,0.45),rgba(30,40,70,0.45)), url('<?php echo $base_path; ?>images/car-rental-search-hero.jpeg') center center/cover no-repeat;
    min-height: 350px;
    padding-top: 3rem;
    padding-bottom: 4rem;
    text-align: center;
}
.car-search-container {
    max-width: 1200px;
    margin: 0 auto;
}
.car-search-form-section {
    margin-top: -120px;
}
.car-search-form-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.08);
    padding: 2.5rem 2rem 2rem 2rem;
    margin: 0 auto;
    max-width: 1100px;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.car-form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    width: 100%;
    justify-content: center;
}
.car-form-group {
    display: flex;
    flex-direction: column;
    min-width: 200px;
    flex: 1 1 200px;
}
.car-form-group label {
    font-weight: 500;
    margin-bottom: 0.5em;
    text-align: left;
}
.car-form-group input {
    padding: 0.7em;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1em;
}
.car-btn-primary {
    padding: 0.9em 2.2em;
    background: #fc5c7d;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 1.1em;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 1.5em;
}
.car-btn-primary:hover {
    background: #6a82fb;
}
@media (max-width: 900px) {
    .car-form-row { flex-direction: column; align-items: stretch; }
    .car-form-group { min-width: unset; }
}
</style>
<div class="car-search-hero">
    <h1 style="color:#fff; font-size:2.5rem; margin-bottom:2.5rem;">Find Your Perfect Transfer</h1>
</div>
<div class="car-search-container">
    <section class="car-search-form-section">
        <form method="POST" action="<?php echo $base_path; ?>car/search" class="car-search-form-card">
            <div class="car-form-row">
                <div class="car-form-group">
                    <label for="pickup">Pickup Location</label>
                    <input type="text" id="pickup" name="pickup" placeholder="City, airport, or landmark" required>
                </div>
                <div class="car-form-group">
                    <label for="dropoff">Dropoff Location</label>
                    <input type="text" id="dropoff" name="dropoff" placeholder="City, airport, or landmark" required>
                </div>
                <div class="car-form-group">
                    <label for="datetime">Date & Time</label>
                    <input type="datetime-local" id="datetime" name="datetime" required>
                </div>
                <div class="car-form-group">
                    <label for="passengers">Passengers</label>
                    <input type="number" id="passengers" name="passengers" min="1" max="10" value="1">
                </div>
            </div>
            <div class="car-form-row">
                <div class="car-form-group">
                    <button type="submit" class="car-btn-primary">Search Transfers</button>
                </div>
            </div>
        </form>
    </section>
</div>
<?php if (!empty($offers)): ?>
    <div style="max-width:1100px;margin:2rem auto 0 auto;">
        <h2 style="text-align:left;">Available Transfers</h2>
        <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <?php foreach ($offers as $offer): ?>
            <div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);display:flex;align-items:center;padding:1.5rem 2rem;gap:2rem;">
                <div>
                    <?php if (!empty($offer['vehicle']['imageURL'])): ?>
                        <img src="<?= htmlspecialchars($offer['vehicle']['imageURL']) ?>" alt="Vehicle" style="width:120px;border-radius:8px;">
                    <?php endif; ?>
                </div>
                <div style="flex:1;">
                    <h3 style="margin:0 0 0.5rem 0;"><?= htmlspecialchars($offer['vehicle']['description'] ?? 'Vehicle') ?></h3>
                    <div style="color:#555;">Type: <b><?= htmlspecialchars($offer['transferType'] ?? '') ?></b></div>
                    <div style="color:#555;">Provider: <b><?= htmlspecialchars($offer['serviceProvider']['name'] ?? '') ?></b></div>
                    <div style="color:#555;">Seats: <b><?= htmlspecialchars($offer['vehicle']['seats'][0]['count'] ?? '') ?></b></div>
                </div>
                <div style="text-align:right;min-width:160px;">
                    <div style="font-size:1.5rem;color:#fc5c7d;font-weight:600;">
                        <?= htmlspecialchars($offer['quotation']['monetaryAmount'] ?? '') ?> <?= htmlspecialchars($offer['quotation']['currencyCode'] ?? '') ?>
                    </div>
                    <form method="GET" action="<?php echo $base_path; ?>car/view/<?= htmlspecialchars($offer['id']) ?>">
                        <button type="submit" class="car-btn-primary" style="margin-top:1em;">View</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
<?php elseif (isset($offers)): ?>
    <div style="max-width:1100px;margin:2rem auto 0 auto;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:2rem;text-align:center;">
        <p>No transfer offers found for your search.</p>
    </div>
<?php endif; ?>
<?php include __DIR__ . '/../partials/footer.php'; ?> 