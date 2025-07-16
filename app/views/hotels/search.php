<?php 
function safe_html($value) {
    return is_string($value) ? htmlspecialchars($value) : '';
}
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';

// Remote hotel images (royalty-free URLs)
$mainImages = [
    'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&w=400&h=300',
    'https://images.pexels.com/photos/271639/pexels-photo-271639.jpeg?auto=compress&w=400&h=300',
    'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&w=400&h=300',
    'https://images.pexels.com/photos/210604/pexels-photo-210604.jpeg?auto=compress&w=400&h=300',
    'https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg?auto=compress&w=400&h=300',
    'https://images.pexels.com/photos/189296/pexels-photo-189296.jpeg?auto=compress&w=400&h=300',
    'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&w=400&h=300', // replacement for 26139
    'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&w=400&h=300', // replacement for 53464
    'https://images.pexels.com/photos/261187/pexels-photo-261187.jpeg?auto=compress&w=400&h=300',
];
$otherImages = [
    'https://images.pexels.com/photos/271639/pexels-photo-271639.jpeg?auto=compress&w=400&h=300', // room
    'https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg?auto=compress&w=400&h=300', // pool
    'https://images.pexels.com/photos/210604/pexels-photo-210604.jpeg?auto=compress&w=400&h=300', // dining
    'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&w=400&h=300', // replacement for 53464
    'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&w=400&h=300', // replacement for 26139
    'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&w=400&h=300', // spa
    'https://images.pexels.com/photos/189296/pexels-photo-189296.jpeg?auto=compress&w=400&h=300', // bar
    'https://images.pexels.com/photos/261187/pexels-photo-261187.jpeg?auto=compress&w=400&h=300', // restaurant
    'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&w=400&h=300', // suite
    'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&w=400&h=300', // terrace
    'https://images.pexels.com/photos/261187/pexels-photo-261187.jpeg?auto=compress&w=400&h=300', // breakfast
    'https://images.pexels.com/photos/210604/pexels-photo-210604.jpeg?auto=compress&w=400&h=300', // lounge
    'https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg?auto=compress&w=400&h=300', // pool2
    'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&w=400&h=300', // lobby2
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Search - Ebooky</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <script>
      window.EBOOKY_BASE_PATH = "<?php echo $base_path; ?>";
    </script>
    <script src="<?php echo $base_path; ?>js/hotel-autocomplete.js" defer></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    
    <main class="hotel-search">
        <div class="container">
            <!-- Search Form -->
            <section class="search-form">
                <h1>Find Your Perfect Hotel</h1>
                <form action="<?php echo $base_path; ?>hotels/search" method="get" class="hotel-search-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">Destination</label>
                            <div class="autocomplete-group">
                                <input type="text" name="city" id="city" placeholder="City, airport, or landmark" 
                                       value="<?php echo htmlspecialchars($params['query'] ?? ''); ?>" required>
                                <div id="city-dropdown" class="autocomplete-dropdown"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="check_in">Check-in</label>
                            <input type="date" name="check_in" id="check_in" 
                                   value="<?php echo $params['check_in'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="check_out">Check-out</label>
                            <input type="date" name="check_out" id="check_out" 
                                   value="<?php echo $params['check_out'] ?? ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="guests">Guests</label>
                            <select name="guests" id="guests">
                                <?php for($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($params['guests'] ?? 1) == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> <?php echo $i == 1 ? 'Guest' : 'Guests'; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="rooms">Rooms</label>
                            <select name="rooms" id="rooms">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($params['rooms'] ?? 1) == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> <?php echo $i == 1 ? 'Room' : 'Rooms'; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <button type="submit" class="btn-primary full-width">Search Hotels</button>
                    </div>
                </form>
            </section>

            <!-- Filters and Results -->
            <?php if ($results && isset($results['data'])): ?>
            <section class="search-results">
                <div class="results-header">
                    <h2><?php echo count($results['data']); ?> hotels found in <?php echo htmlspecialchars($params['query'] ?? ''); ?></h2>
                    
                    <!-- Filters -->
                    <div class="filters">
                        <form method="get" class="filter-form">
                            <input type="hidden" name="city" value="<?php echo htmlspecialchars($params['query'] ?? ''); ?>">
                            <input type="hidden" name="check_in" value="<?php echo $params['check_in']; ?>">
                            <input type="hidden" name="check_out" value="<?php echo $params['check_out']; ?>">
                            <input type="hidden" name="guests" value="<?php echo $params['guests']; ?>">
                            <input type="hidden" name="rooms" value="<?php echo $params['rooms']; ?>">
                            
                            <select name="price_range" onchange="this.form.submit()">
                                <option value="">All Prices</option>
                                <option value="budget" <?php echo ($params['price_range'] ?? '') === 'budget' ? 'selected' : ''; ?>>Budget ($0-$100)</option>
                                <option value="mid" <?php echo ($params['price_range'] ?? '') === 'mid' ? 'selected' : ''; ?>>Mid-range ($100-$300)</option>
                                <option value="luxury" <?php echo ($params['price_range'] ?? '') === 'luxury' ? 'selected' : ''; ?>>Luxury ($300-$1000)</option>
                                <option value="ultra" <?php echo ($params['price_range'] ?? '') === 'ultra' ? 'selected' : ''; ?>>Ultra Luxury ($1000+)</option>
                            </select>
                            
                            <select name="stars" onchange="this.form.submit()">
                                <option value="">All Ratings</option>
                                <option value="5" <?php echo ($params['stars'] ?? '') === '5' ? 'selected' : ''; ?>>5 Stars</option>
                                <option value="4" <?php echo ($params['stars'] ?? '') === '4' ? 'selected' : ''; ?>>4+ Stars</option>
                                <option value="3" <?php echo ($params['stars'] ?? '') === '3' ? 'selected' : ''; ?>>3+ Stars</option>
                            </select>
                            
                            <select name="sort_by" onchange="this.form.submit()">
                                <option value="price" <?php echo ($params['sort_by'] ?? 'price') === 'price' ? 'selected' : ''; ?>>Sort by Price</option>
                                <option value="rating" <?php echo ($params['sort_by'] ?? 'price') === 'rating' ? 'selected' : ''; ?>>Sort by Rating</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Price Calendar -->
                <?php if (!empty($calendar)): ?>
                <div class="price-calendar">
                    <h3>Flexible Dates - Lowest Prices</h3>
                    <div class="calendar-grid">
                        <?php foreach ($calendar as $date => $priceData): ?>
                            <div class="calendar-day">
                                <div class="date"><?php echo date('M j', strtotime($date)); ?></div>
                                <div class="price">$<?php echo number_format($priceData['price'], 0); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Hotel Results -->
                <div class="hotel-list">
                    <?php foreach ($results['data'] as $hotel): ?>
                        <?php if (isset($hotel['offers']) && count($hotel['offers']) > 0): ?>
                            <?php $offer = $hotel['offers'][0]; ?>
                            <div class="hotel-card">
                                <div class="hotel-image">
                                    <?php 
                                    // Always use a random main image from the remote list
                                    $mainImg = $mainImages[array_rand($mainImages)];
                                    echo '<img src="' . safe_html($mainImg) . '" alt="Hotel Image">';
                                    // Pick 5 unique other images for this hotel (for gallery/detail page)
                                    $otherImgs = [];
                                    if (count($otherImages) >= 5) {
                                        $keys = array_rand($otherImages, 5);
                                        foreach ((array)$keys as $k) {
                                            $otherImgs[] = $otherImages[$k];
                                        }
                                    }
                                    // You can use $otherImgs for a gallery or detail page
                                    ?>
                                </div>
                                
                                <div class="hotel-info">
                                    <h3><?php echo safe_html($hotel['hotel']['name']); ?></h3>
                                    
                                    <div class="hotel-details">
                                        <?php if (isset($hotel['hotel']['rating'])): ?>
                                            <div class="rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span class="star <?php echo $i <= $hotel['hotel']['rating'] ? 'filled' : ''; ?>">★</span>
                                                <?php endfor; ?>
                                                <span class="rating-text"><?php echo $hotel['hotel']['rating']; ?>/5</span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($hotel['hotel']['address'])): ?>
                                            <div class="location">
                                                <i class="icon-location"></i>
                                                <?php echo safe_html($hotel['hotel']['address']['cityName'] ?? ''); ?>, 
                                                <?php echo safe_html($hotel['hotel']['address']['countryCode'] ?? ''); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($hotel['hotel']['amenities'])): ?>
                                            <div class="amenities">
                                                <?php 
                                                $amenities = array_slice($hotel['hotel']['amenities'], 0, 3);
                                                foreach ($amenities as $amenity): 
                                                ?>
                                                    <span class="amenity-tag"><?php echo safe_html($amenity); ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($hotel['hotel']['amenities']) > 3): ?>
                                                    <span class="amenity-tag">+<?php echo count($hotel['hotel']['amenities']) - 3; ?> more</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (isset($offer['room']['description'])): ?>
                                        <div class="room-info">
                                            <strong><?php echo safe_html($offer['room']['description']); ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="hotel-price">
                                    <?php
                                    $total = $offer['price']['total'] ?? 0;
                                    $currency = $offer['price']['currency'] ?? 'USD';
                                    $checkIn = $params['check_in'] ?? '';
                                    $checkOut = $params['check_out'] ?? '';
                                    $nights = 1;
                                    if ($checkIn && $checkOut) {
                                        $in = strtotime($checkIn);
                                        $out = strtotime($checkOut);
                                        if ($in && $out && $out > $in) {
                                            $nights = max(1, round(($out - $in) / 86400));
                                        }
                                    }
                                    $perNight = $nights > 0 ? $total / $nights : $total;
                                    ?>
                                    <div class="price">
                                        <span class="amount">$<?php echo number_format($perNight, 2); ?></span>
                                        <span class="currency"><?php echo $currency; ?></span>
                                    </div>
                                    <div class="per-night">per night</div>
                                    <div class="total-price">Total: $<?php echo number_format($total, 2); ?> <?php echo $currency; ?></div>
                                    <a href="<?php echo $base_path; ?>hotels/view?hotel_id=<?php echo urlencode($hotel['hotel']['hotelId']); ?>&offer_id=<?php echo urlencode($offer['id']); ?>&adults=<?php echo urlencode($params['guests']); ?>&check_in=<?php echo urlencode($params['check_in']); ?>&check_out=<?php echo urlencode($params['check_out']); ?>" class="btn-book">View</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php elseif ($results): ?>
                <div class="no-results">
                    <h2>No hotels found</h2>
                    <?php if (isset($results['errors'][0]['detail'])): ?>
                        <div class="api-error" style="color:#b00; margin:1em 0;">
                            API Error: <?php echo htmlspecialchars($results['errors'][0]['detail']); ?>
                        </div>
                    <?php elseif (isset($results['error'])): ?>
                        <div class="api-error" style="color:#b00; margin:1em 0;">
                            API Error: <?php echo htmlspecialchars($results['error']); ?>
                        </div>
                    <?php else: ?>
                        <p>Try adjusting your search criteria or dates.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <style>
    .hotel-search {
        padding: 0 0 2rem 0; /* Remove top padding so main touches navbar */
    }
    
    .search-form {
        background: linear-gradient(rgba(30,40,70,0.45),rgba(30,40,70,0.45)), url('<?php echo $base_path; ?>images/hotel-search-hero.jpg') center center/cover no-repeat;
        border-radius: 0; /* Remove curved border */
        padding: 3rem 2rem 2rem 2rem;
        margin-bottom: 2.5rem;
        color: #fff;
        box-shadow: 0 8px 32px rgba(0,0,0,0.10);
    }
    
    .search-form h1 {
        text-align: center;
        margin-bottom: 2rem;
        font-size: 2.5rem;
    }
    
    .hotel-search-form {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .form-row {
        display: flex;
        gap: 2rem; /* Increase gap between input fields */
        margin-bottom: 1rem;
    }
    
    .form-group {
        flex: 1;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        margin-bottom: 0; /* Remove default margin to control spacing via gap */
    }
    
    .btn-primary {
        background: #ff6b6b;
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s;
    }
    
    .btn-primary:hover {
        background: #ff5252;
    }
    
    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .filters {
        display: flex;
        gap: 1rem;
    }
    
    .filters select {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        background: white;
    }
    
    .price-calendar {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    
    .calendar-grid {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        padding: 1rem 0;
    }
    
    .calendar-day {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        min-width: 80px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .calendar-day .date {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .calendar-day .price {
        color: #ff6b6b;
        font-weight: bold;
    }
    
    .hotel-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .hotel-card {
        display: flex;
        align-items: stretch; /* Make children stretch to same height */
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .hotel-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    
    .hotel-image {
        width: 200px;
        height: auto;
        min-height: 100%;
        display: flex;
        align-items: stretch;
        flex-shrink: 0;
    }
    
    .hotel-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    
    .no-image {
        width: 100%;
        height: 100%;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
    }
    
    .hotel-info {
        flex: 1;
        padding: 1.5rem;
    }
    
    .hotel-info h3 {
        margin: 0 0 1rem 0;
        color: #333;
        font-size: 1.25rem;
    }
    
    .hotel-details {
        margin-bottom: 1rem;
    }
    
    .rating {
        margin-bottom: 0.5rem;
    }
    
    .star {
        color: #ddd;
        font-size: 1.2rem;
    }
    
    .star.filled {
        color: #ffd700;
    }
    
    .rating-text {
        margin-left: 0.5rem;
        color: #666;
    }
    
    .location {
        color: #666;
        margin-bottom: 0.5rem;
    }
    
    .amenities {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .amenity-tag {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }
    
    .room-info {
        color: #666;
        font-size: 0.9rem;
    }
    
    .hotel-price {
        background: #f8f9fa;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-width: 150px;
    }
    
    .price .amount {
        font-size: 1.5rem;
        font-weight: bold;
        color: #ff6b6b;
    }
    
    .price .currency {
        font-size: 1rem;
        color: #666;
    }
    
    .per-night {
        color: #666;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }
    
    .btn-book {
        background: #4caf50;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.3s;
    }
    
    .btn-book:hover {
        background: #45a049;
    }
    
    .no-results {
        text-align: center;
        padding: 3rem;
        color: #666;
    }
    
    .autocomplete-group {
        position: relative;
    }
    
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff !important;
        border: 1px solid #ddd;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        z-index: 100;
        max-height: 200px;
        overflow-y: auto;
        display: none;
        color: #222 !important;
    }
    
    .autocomplete-dropdown.active {
        display: block;
    }
    
    .autocomplete-dropdown .option {
        background: #fff !important;
        color: #222 !important;
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .autocomplete-dropdown .option:hover {
        background: #f5f5f5 !important;
    }
    
    .search-results {
        padding-left: 2rem;
        padding-right: 2rem;
    }
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 1rem;
        }
        
        .hotel-card {
            flex-direction: column;
        }
        
        .hotel-image {
            width: 100%;
            height: 200px;
            min-height: unset;
        }
        
        .results-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filters {
            flex-wrap: wrap;
        }
        .search-results {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    }
    .btn-primary.full-width {
        width: 100%;
        display: block;
        margin-top: 1rem;
    }
    </style>
</body>
</html> 