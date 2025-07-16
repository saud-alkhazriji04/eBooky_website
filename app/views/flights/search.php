<?php
$base_path = '';
if (isset($_SERVER['SCRIPT_NAME'])) {
    $base_path = dirname($_SERVER['SCRIPT_NAME']);
    if ($base_path === '/' || $base_path === '\\') $base_path = '';
}
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
    <title>Flight Search</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>/css/style.css">
    <script src="<?php echo $base_path; ?>/js/iata-autocomplete.js" defer></script>
    <script>
      window.EBOOKY_BASE_PATH = "<?php echo $base_path; ?>";
    </script>
    <style>
    body {
        background: #f7f8fa;
    }
    .flight-hero {
        background: linear-gradient(rgba(30,40,70,0.45),rgba(30,40,70,0.45)), url('<?php echo $base_path; ?>/images/flight-search-hero.jpg') center center/cover no-repeat;
        min-height: 350px;
        padding-top: 3rem;
        padding-bottom: 4rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .flight-hero-title {
        color: #fff;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 2.5rem;
    }
    .flight-search-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    .flight-search-form-section {
        margin-top: -120px;
    }
    .flight-search-form-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        padding: 2.5rem 2rem 2rem 2rem;
        margin: 0 auto;
        max-width: 700px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        overflow: visible;
        z-index: 2;
    }
    .flight-form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        width: 100%;
        justify-content: center;
    }
    .flight-form-group {
        display: flex;
        flex-direction: column;
        min-width: 200px;
        flex: 1 1 200px;
    }
    .flight-form-group label {
        font-weight: 500;
        margin-bottom: 0.5em;
        text-align: left;
    }
    .flight-form-group input {
        padding: 0.7em;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 1em;
    }
    .flight-btn-primary {
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
    .flight-btn-primary:hover {
        background: #6a82fb;
    }
    @media (max-width: 900px) {
        .flight-form-row { flex-direction: column; align-items: stretch; }
        .flight-form-group { min-width: unset; }
    }
    /* --- Existing autocomplete and results/filter styles below --- */
    .autocomplete-group {
        position: relative;
        z-index: 10;
    }
    .autocomplete-dropdown {
        position: absolute;
        left: 0;
        right: 0;
        top: 100%;
        width: 100%;
        min-width: 200px;
        max-width: 100%;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        z-index: 100;
        max-height: 260px;
        overflow-y: auto;
        display: none;
    }
    .autocomplete-dropdown.active { display: block; }
    .autocomplete-dropdown .group-label {
        font-weight: bold;
        background: #f6f6f6;
        padding: 0.4em 1em;
        border-bottom: 1px solid #eee;
    }
    .autocomplete-dropdown .airport-option {
        padding: 0.5em 1em;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .autocomplete-dropdown .airport-option:hover {
        background: #e6f0ff;
    }
    .autocomplete-dropdown .star {
        color: #ffd700;
        font-size: 1.2em;
        cursor: pointer;
        margin-left: 0.5em;
    }
    .flight-results {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        margin: 2rem auto;
        max-width: 700px;
    }
    .flight-offer {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        padding: 1.5rem 2rem;
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
        border-left: 6px solid #003580;
    }
    .flight-offer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .flight-price {
        font-size: 1.4rem;
        font-weight: bold;
        color: #003580;
    }
    .flight-details {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }
    .flight-segment {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }
    .flight-carrier {
        font-weight: 500;
        color: #555;
    }
    .flight-book-btn {
        margin-top: 1rem;
        align-self: flex-end;
        background: #003580;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 0.7rem 1.5rem;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .flight-book-btn:hover {
        background: #0056b3;
    }
    .flight-meta {
        font-size: 0.95rem;
        color: #888;
    }
    .error {
        color: #b00;
        background: #ffeaea;
        border: 1px solid #fbb;
        padding: 1rem;
        border-radius: 8px;
        margin: 2rem auto;
        max-width: 600px;
    }
    .calendar-heatmap {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin: 2rem 0 1.5rem 0;
    }
    .calendar-day {
        background: #f6f6f6;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 0.7em 1.2em;
        cursor: pointer;
        text-align: center;
        transition: background 0.2s, border 0.2s;
        font-size: 1em;
        min-width: 90px;
    }
    .calendar-day.selected, .calendar-day:active {
        background: #003580;
        color: #fff;
        border: 1px solid #003580;
    }
    .calendar-date {
        font-weight: bold;
        margin-bottom: 0.2em;
    }
    .calendar-price {
        font-size: 1.1em;
    }
    .filters-bar {
        display: flex;
        gap: 2em;
        align-items: flex-end;
        margin-bottom: 1.5em;
        flex-wrap: wrap;
    }
    .filters-bar label { font-weight: 500; margin-right: 0.5em; }
    .filters-bar select, .filters-bar input[type=checkbox] { margin-right: 0.5em; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../partials/navbar.php'; ?>
    <div class="flight-hero">
        <h1 class="flight-hero-title">Find Your Perfect Flight</h1>
    </div>
    <div class="flight-search-container">
        <section class="flight-search-form-section">
            <form action="<?php echo $base_path; ?>/flights/search" method="get" autocomplete="off" class="flight-search-form-card">
                <div class="flight-form-row">
                    <div class="flight-form-group">
                        <label for="from-airport">From</label>
                        <input type="text" name="from" id="from-airport" placeholder="From (IATA code, e.g. BAH)" required autocomplete="off" value="<?php echo htmlspecialchars($_GET['from'] ?? ''); ?>">
                        <div id="from-airport-dropdown" class="autocomplete-dropdown"></div>
                    </div>
                    <div class="flight-form-group">
                        <label for="to-airport">To</label>
                        <input type="text" name="to" id="to-airport" placeholder="To (IATA code, e.g. DXB)" required autocomplete="off" value="<?php echo htmlspecialchars($_GET['to'] ?? ''); ?>">
                        <div id="to-airport-dropdown" class="autocomplete-dropdown"></div>
                    </div>
                    <div class="flight-form-group">
                        <label for="departure_date">Departure Date</label>
                        <input type="date" name="departure_date" id="departure_date" required value="<?php echo htmlspecialchars($_GET['departure_date'] ?? ''); ?>">
                    </div>
                    <div class="flight-form-group">
                        <label for="return_date">Return Date</label>
                        <input type="date" name="return_date" id="return_date" value="<?php echo htmlspecialchars($_GET['return_date'] ?? ''); ?>">
                    </div>
                    <div class="flight-form-group">
                        <label for="passengers">Passengers</label>
                        <input type="number" name="passengers" id="passengers" min="1" value="<?php echo htmlspecialchars($_GET['passengers'] ?? 1); ?>" required>
                    </div>
                </div>
                <button type="submit" class="flight-btn-primary">Search Flights</button>
                <small style="margin-top:1em;">Use IATA codes for airports/cities. Start typing for suggestions.</small>
            </form>
        </section>
        <?php
        $hasSearch = !empty($_GET['from']) || !empty($_GET['to']) || !empty($_GET['departure_date']);
        if ($hasSearch):
        ?>
        <div class="filters-bar" style="max-width:700px;margin:2rem auto 1.5rem auto;">
            <form method="get" action="<?php echo $base_path; ?>/flights/search" style="display:flex;flex-wrap:wrap;gap:2em;width:100%;align-items:flex-end;">
                <input type="hidden" name="from" value="<?php echo htmlspecialchars($_GET['from'] ?? ''); ?>">
                <input type="hidden" name="to" value="<?php echo htmlspecialchars($_GET['to'] ?? ''); ?>">
                <input type="hidden" name="departure_date" value="<?php echo htmlspecialchars($_GET['departure_date'] ?? ''); ?>">
                <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($_GET['return_date'] ?? ''); ?>">
                <input type="hidden" name="passengers" value="<?php echo htmlspecialchars($_GET['passengers'] ?? 1); ?>">
                <div>
                    <label>Stops:</label>
                    <select name="stops">
                        <option value="">Any</option>
                        <option value="0" <?php if(($_GET['stops'] ?? '')==='0') echo 'selected'; ?>>Direct</option>
                        <option value="1" <?php if(($_GET['stops'] ?? '')==='1') echo 'selected'; ?>>1 Stop</option>
                        <option value="2" <?php if(($_GET['stops'] ?? '')==='2') echo 'selected'; ?>>2+ Stops</option>
                    </select>
                </div>
                <div>
                    <label>Airlines:</label>
                    <?php foreach (function_exists('getAirlines') ? getAirlines($results) : [] as $al): ?>
                        <?php $al_name = $airline_map[$al] ?? $al; ?>
                        <label><input type="checkbox" name="airlines[]" value="<?php echo $al; ?>" <?php if(isset($_GET['airlines']) && in_array($al, (array)$_GET['airlines'])) echo 'checked'; ?>> <?php echo htmlspecialchars($al_name); ?></label>
                    <?php endforeach; ?>
                </div>
                <div>
                    <label>Departure Time:</label>
                    <select name="dep_time">
                        <option value="">Any</option>
                        <option value="morning" <?php if(($_GET['dep_time'] ?? '')==='morning') echo 'selected'; ?>>Morning (5am-12pm)</option>
                        <option value="afternoon" <?php if(($_GET['dep_time'] ?? '')==='afternoon') echo 'selected'; ?>>Afternoon (12pm-5pm)</option>
                        <option value="evening" <?php if(($_GET['dep_time'] ?? '')==='evening') echo 'selected'; ?>>Evening (5pm-9pm)</option>
                        <option value="night" <?php if(($_GET['dep_time'] ?? '')==='night') echo 'selected'; ?>>Night (9pm-5am)</option>
                    </select>
                </div>
                <div>
                    <label>Cabin Class:</label>
                    <select name="cabin">
                        <option value="">Any</option>
                        <option value="ECONOMY" <?php if(($_GET['cabin'] ?? '')==='ECONOMY') echo 'selected'; ?>>Economy</option>
                        <option value="PREMIUM_ECONOMY" <?php if(($_GET['cabin'] ?? '')==='PREMIUM_ECONOMY') echo 'selected'; ?>>Premium Economy</option>
                        <option value="BUSINESS" <?php if(($_GET['cabin'] ?? '')==='BUSINESS') echo 'selected'; ?>>Business</option>
                        <option value="FIRST" <?php if(($_GET['cabin'] ?? '')==='FIRST') echo 'selected'; ?>>First</option>
                    </select>
                </div>
                <div>
                    <label>Fare Type:</label>
                    <select name="fare_type">
                        <option value="">Any</option>
                        <option value="BASIC" <?php if(($_GET['fare_type'] ?? '')==='BASIC') echo 'selected'; ?>>Basic</option>
                        <option value="FLEX" <?php if(($_GET['fare_type'] ?? '')==='FLEX') echo 'selected'; ?>>Flex</option>
                        <option value="PREMIUM" <?php if(($_GET['fare_type'] ?? '')==='PREMIUM') echo 'selected'; ?>>Premium</option>
                    </select>
                </div>
                <div>
                    <label>Price Range:</label>
                    <input type="number" name="min_price" placeholder="Min" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>" style="width:70px;">
                    -
                    <input type="number" name="max_price" placeholder="Max" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>" style="width:70px;">
                </div>
                <div>
                    <button type="submit" style="margin-top:1.5em;">Apply Filters</button>
                </div>
            </form>
        </div>
        <?php if (isset($results['data']) && is_array($results['data']) && count($results['data']) > 0): ?>
            <div class="flight-results">
                <?php foreach ($results['data'] as $flight): ?>
                    <?php 
                        $segment = $flight['itineraries'][0]['segments'][0];
                        $arrival = $segment['arrival'];
                        $departure = $segment['departure'];
                        $carrier = $segment['carrierCode'];
                        $price = $flight['price']['total'] . ' ' . $flight['price']['currency'];
                        $duration = $flight['itineraries'][0]['duration'] ?? '';
                        $stops = count($flight['itineraries'][0]['segments']) - 1;
                    ?>
                    <div class="flight-offer">
                        <div class="flight-offer-header">
                            <div class="flight-carrier">Carrier: <?php 
                                $carrier_name = $airline_map[$carrier] ?? $carrier;
                                echo htmlspecialchars($carrier_name);
                                if ($carrier_name !== $carrier) echo " (" . htmlspecialchars($carrier) . ")";
                            ?></div>
                            <div class="flight-price">
                                <?php echo $price; ?>
                                <?php if (function_exists('getUserCurrency') && strtolower($flight['price']['currency']) !== getUserCurrency()): ?>
                                    <span style="color:#888;font-size:0.95em;">(Displayed in <?php echo strtoupper($flight['price']['currency']); ?>. Your currency: <?php echo strtoupper(getUserCurrency()); ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flight-details">
                            <div class="flight-segment">
                                <span><strong>From:</strong> <?php echo $departure['iataCode']; ?> <span class="flight-meta">@ <?php echo date('D, M j, H:i', strtotime($departure['at'])); ?></span></span>
                                <span><strong>To:</strong> <?php echo $arrival['iataCode']; ?> <span class="flight-meta">@ <?php echo date('D, M j, H:i', strtotime($arrival['at'])); ?></span></span>
                            </div>
                            <div class="flight-segment">
                                <span><strong>Stops:</strong> <?php echo $stops === 0 ? 'Direct' : $stops . ' stop' . ($stops > 1 ? 's' : ''); ?></span>
                                <?php if ($duration): ?><span><strong>Duration:</strong> <?php echo str_replace(['PT','H','M'], ['','h ','m'], $duration); ?></span><?php endif; ?>
                            </div>
                        </div>
                        <form method="post" action="<?php echo $base_path; ?>/flights/book">
                            <input type="hidden" name="flight_offer" value='<?php echo htmlspecialchars(json_encode($flight), ENT_QUOTES, "UTF-8"); ?>'>
                            <input type="hidden" name="passengers" value="<?php echo htmlspecialchars($_GET['passengers'] ?? 1); ?>">
                            <input type="hidden" name="from" value="<?php echo htmlspecialchars($_GET['from'] ?? ''); ?>">
                            <input type="hidden" name="to" value="<?php echo htmlspecialchars($_GET['to'] ?? ''); ?>">
                            <input type="hidden" name="departure_date" value="<?php echo htmlspecialchars($_GET['departure_date'] ?? ''); ?>">
                            <button type="submit" class="flight-book-btn">Book This Flight</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($results['errors'])): ?>
            <div class="error">Error: <?php echo htmlspecialchars($results['errors'][0]['detail'] ?? 'Unknown error'); ?></div>
        <?php else: ?>
            <p style="max-width:700px;margin:2rem auto;">No flights found for your search criteria.</p>
        <?php endif; ?>
        <?php if (!empty($calendar)): ?>
            <div class="calendar-heatmap">
                <?php foreach ($calendar as $date => $info): ?>
                    <form method="get" action="<?php echo $base_path; ?>/flights/search" style="display:inline;">
                        <input type="hidden" name="from" value="<?php echo htmlspecialchars($_GET['from'] ?? ''); ?>">
                        <input type="hidden" name="to" value="<?php echo htmlspecialchars($_GET['to'] ?? ''); ?>">
                        <input type="hidden" name="departure_date" value="<?php echo $date; ?>">
                        <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($_GET['return_date'] ?? ''); ?>">
                        <input type="hidden" name="passengers" value="<?php echo htmlspecialchars($_GET['passengers'] ?? 1); ?>">
                        <button type="submit" class="calendar-day<?php if (($results['meta']['searchDate'] ?? '') === $date) echo ' selected'; ?>">
                            <div class="calendar-date"><?php echo date('D, M j', strtotime($date)); ?></div>
                            <div class="calendar-price"><?php echo $info['price'] . ' ' . $info['currency']; ?></div>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php endif; ?>
        <div class="popular-routes" style="margin: 2rem auto; padding: 1.5rem; background: #f8f9fa; border-radius: 8px; max-width: 700px;">
            <h4>Popular Test Routes (Amadeus Sandbox)</h4>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">These routes typically have good data in the test environment:</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/flights/search?from=NYC&to=LAX&departure_date=<?php echo date('Y-m-d', strtotime('+7 days')); ?>&passengers=1" class="route-link" style="display: block; padding: 0.5rem; background: white; border-radius: 4px; text-decoration: none; color: #003580; border: 1px solid #ddd; text-align: center;">New York → Los Angeles</a>
                <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/flights/search?from=JFK&to=LAX&departure_date=<?php echo date('Y-m-d', strtotime('+7 days')); ?>&passengers=1" class="route-link" style="display: block; padding: 0.5rem; background: white; border-radius: 4px; text-decoration: none; color: #003580; border: 1px solid #ddd; text-align: center;">JFK → LAX</a>
                <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/flights/search?from=CDG&to=JFK&departure_date=<?php echo date('Y-m-d', strtotime('+7 days')); ?>&passengers=1" class="route-link" style="display: block; padding: 0.5rem; background: white; border-radius: 4px; text-decoration: none; color: #003580; border: 1px solid #ddd; text-align: center;">Paris → New York</a>
                <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/flights/search?from=LHR&to=JFK&departure_date=<?php echo date('Y-m-d', strtotime('+7 days')); ?>&passengers=1" class="route-link" style="display: block; padding: 0.5rem; background: white; border-radius: 4px; text-decoration: none; color: #003580; border: 1px solid #ddd; text-align: center;">London → New York</a>
                <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/flights/search?from=DXB&to=LHR&departure_date=<?php echo date('Y-m-d', strtotime('+7 days')); ?>&passengers=1" class="route-link" style="display: block; padding: 0.5rem; background: white; border-radius: 4px; text-decoration: none; color: #003580; border: 1px solid #ddd; text-align: center;">Dubai → London</a>
                <a href="<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>/flights/search?from=SYD&to=LAX&departure_date=<?php echo date('Y-m-d', strtotime('+7 days')); ?>&passengers=1" class="route-link" style="display: block; padding: 0.5rem; background: white; border-radius: 4px; text-decoration: none; color: #003580; border: 1px solid #ddd; text-align: center;">Sydney → Los Angeles</a>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
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

// Helper to extract unique airlines from results
function getAirlines($results) {
    $airlines = [];
    if (isset($results['data'])) {
        foreach ($results['data'] as $offer) {
            foreach ($offer['itineraries'] as $itinerary) {
                foreach ($itinerary['segments'] as $seg) {
                    $airlines[$seg['carrierCode']] = true;
                }
            }
        }
    }
    return array_keys($airlines);
}
?> 