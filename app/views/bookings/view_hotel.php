<?php
// app/views/bookings/view_hotel.php
function safe_html($value) { return is_string($value) ? htmlspecialchars($value) : ''; }
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
$hotel = $details['hotel'] ?? [];
$params = $details['booking_params'] ?? [];
$price = $details['payment']['amount'] ?? ($details['offer']['price']['total'] ?? '');
$currency = $details['payment']['currency'] ?? ($details['offer']['price']['currency'] ?? 'USD');
$popularFacilities = [
    'pool' => ['pool', 'indoor pool', 'outdoor pool', 'swimming pool', "children's pool", 'plunge pool'],
    'spa' => ['spa', 'spa treatment', 'sauna', 'steam room', 'massage', 'hammam', 'hot tub'],
    'fitness' => ['fitness', 'gym', 'health club'],
    'wifi' => ['wifi', 'wireless internet'],
    'bar' => ['bar', 'lounge'],
    'restaurant' => ['restaurant', 'dining'],
    'parking' => ['parking', 'free parking'],
    'family' => ['family', 'family room'],
    'nonsmoking' => ['non-smoking', 'nonsmoking'],
    'roomservice' => ['room service'],
    'ac' => ['air conditioning', 'ac'],
    'arcade' => ['arcade', 'game room'],
    'breakfast' => ['breakfast', 'free breakfast'],
    'beach' => ['beach'],
    'elevator' => ['elevator', 'lift'],
];
$facilityIcons = [
    'pool' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2-2 4-2"/><path d="M6 16V4m6 12V4m6 12V4"/></svg>',
    'spa' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 16s1.5-2 4-2 4 2 4 2"/></svg>',
    'fitness' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="6"/><path d="M6 6v12"/><path d="M18 6v12"/></svg>',
    'wifi' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e42" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
    'bar' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#be185d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3z"/><path d="M7 13v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-6"/></svg>',
    'restaurant' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 3h16v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V3z"/><path d="M7 13v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-6"/></svg>',
    'parking' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="7" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
    'family' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e42" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="7" r="3"/><circle cx="17" cy="7" r="3"/><circle cx="12" cy="17" r="3"/></svg>',
    'nonsmoking' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#222" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 8l8 8"/></svg>',
    'roomservice' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>',
    'ac' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="8" rx="4"/><path d="M3 12h18"/></svg>',
    'arcade' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e42" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="6"/><path d="M6 6v12"/><path d="M18 6v12"/></svg>',
    'breakfast' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e42" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 16s1.5-2 4-2 4 2 4 2"/></svg>',
    'beach' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/></svg>',
    'elevator' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#222" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="3" width="8" height="18" rx="4"/><path d="M12 9v6"/></svg>',
];
$hotelFacilityStr = $hotel['facilities'] ?? '';
$hotelFacilities = array_map('trim', preg_split('/[,;\n]/', $hotelFacilityStr));
$foundFacilities = [];
foreach ($popularFacilities as $key => $variants) {
    foreach ($hotelFacilities as $fac) {
        foreach ($variants as $variant) {
            if (stripos($fac, $variant) !== false) {
                $foundFacilities[$key] = $fac;
                break 2;
            }
        }
    }
}
// --- Facilities mapping and labels (copied from hotel view) ---
$facilityMap = [
    'pool' => ['pool', 'indoor pool', 'outdoor pool', 'swimming pool', "children's pool", 'plunge pool'],
    'spa' => ['spa', 'spa treatment', 'sauna', 'steam room', 'massage', 'hammam', 'hot tub'],
    'fitness' => ['fitness', 'gym', 'health club'],
    'wifi' => ['wifi', 'wireless internet'],
    'bar' => ['bar', 'lounge'],
    'restaurant' => ['restaurant', 'coffee shop', 'cafe', 'breakfast'],
    'parking' => ['parking', 'car park', 'private parking', 'free parking'],
    'family' => ['family room', 'family rooms', 'kid', "children's club"],
    'non-smoking' => ['non-smoking', 'non smoking'],
    'room service' => ['room service'],
    'air conditioning' => ['air conditioning', 'air conditioned'],
    'arcade' => ['arcade', 'game room', 'billiards', 'table tennis'],
    'beach' => ['beach', 'beachfront', 'beach towels', 'private beach'],
    'elevator' => ['elevator', 'lift'],
];
$facilityLabels = [
    'pool' => 'Pool',
    'spa' => 'Spa / Wellness',
    'fitness' => 'Fitness center',
    'wifi' => 'Free WiFi',
    'bar' => 'Bar / Lounge',
    'restaurant' => 'Restaurant',
    'parking' => 'Free parking',
    'family' => 'Family rooms',
    'non-smoking' => 'Non-smoking rooms',
    'room service' => 'Room service',
    'air conditioning' => 'Air conditioning',
    'arcade' => 'Arcade/game room',
    'beach' => 'Beach',
    'elevator' => 'Elevator/lift',
];
$hotelFacilityStr = $hotel['facilities'] ?? '';
$hotelFacilities = array_map('trim', preg_split('/[,;\n]/', $hotelFacilityStr));
$foundFacilities = [];
foreach ($facilityMap as $key => $keywords) {
    foreach ($hotelFacilities as $fac) {
        foreach ($keywords as $kw) {
            if ($fac && stripos($fac, $kw) !== false) {
                $foundFacilities[$key] = $facilityLabels[$key];
                break 2;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hotel Booking Details</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <style>
        .popular-facilities-grid{display:flex;flex-wrap:wrap;gap:0.7em 1.5em;margin-bottom:1.2em;align-items:center;}.facility-item{display:flex;align-items:center;gap:0.4em;font-size:0.98em;color:#222;}.facility-item span{font-weight:400;}.facility-item svg{width:20px;height:20px;min-width:20px;min-height:20px;}
        .booking-details-card{max-width:600px;margin:2em auto;padding:2em;background:#fff;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,0.07);}
    </style>
</head>
<body>
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<main>
    <div class="booking-details-card">
        <h2><?php echo safe_html($hotel['name'] ?? 'Hotel'); ?></h2>
        <div style="color:#888;font-size:1.1em;margin-bottom:0.5em;">
            <?php echo safe_html($hotel['address']['address'] ?? ''); ?>, 
            <?php echo safe_html($hotel['address']['cityName'] ?? ''); ?>, 
            <?php echo safe_html($hotel['address']['countryCode'] ?? ''); ?>
        </div>
        <div style="margin-bottom:1em;">
            <strong>Booking Reference:</strong> <?php echo safe_html($details['id'] ?? ''); ?><br>
            <strong>Check-in:</strong> <?php echo safe_html($params['check_in'] ?? ''); ?><br>
            <strong>Check-out:</strong> <?php echo safe_html($params['check_out'] ?? ''); ?><br>
            <strong>Guests:</strong> <?php echo safe_html($params['guests'] ?? ''); ?><br>
            <strong>Total Paid:</strong> $<?php echo number_format($price, 2); ?> <?php echo safe_html($currency); ?><br>
        </div>
        <?php if (!empty($foundFacilities)): ?>
            <h3>Most popular facilities</h3>
            <div class="popular-facilities-grid">
                <?php foreach ($foundFacilities as $key => $label): ?>
                    <div class="facility-item"><?php echo $facilityIcons[$key] ?? ''; ?><span><?php echo safe_html($label); ?></span></div>
                <?php endforeach; ?>
            </div>
            <button id="show-all-facilities-btn" style="font-size:0.9em;padding:0.2em 0.7em;margin-bottom:0.5em;cursor:pointer;background:#f5f5f5;border:1px solid #ccc;border-radius:4px;">Show all facilities</button>
            <ul id="all-facilities-list" style="display:none;margin-top:0.5em;">
                <?php foreach ($hotelFacilities as $fac): ?>
                    <?php foreach ($facilityMap as $key => $keywords): ?>
                        <?php foreach ($keywords as $kw): ?>
                            <?php if ($fac && stripos($fac, $kw) !== false): ?>
                                <li><?php echo safe_html($facilityLabels[$key]); ?></li>
                                <?php break 2; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
            <script>document.getElementById("show-all-facilities-btn").onclick=function(){
                var l=document.getElementById("all-facilities-list");
                if(l.style.display==="none"){l.style.display="block";this.textContent="Hide all facilities";}else{l.style.display="none";this.textContent="Show all facilities";}
            };</script>
        <?php endif; ?>
        <?php if (!empty($hotel['description'])): ?>
            <?php 
            $desc = $hotel['description'];
            $desc = preg_replace('/<p>\s*(HeadLine|Location|Rooms|Dining|CheckIn Instructions|Special Instructions)\s*:?.*?<\/p>/i', '', $desc);
            $desc = preg_replace('/<ul>.*?<\/ul>/is', '', $desc);
            $desc = preg_replace('/<p>\s*:?\s*<\/p>/i', '', $desc);
            ?>
            <div style="margin-bottom:0.5em; color:#444;">
                <?php echo nl2br(safe_html($desc)); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($hotel['website'])): ?>
            <div style="margin-bottom:0.5em;"><a href="<?php echo safe_html($hotel['website']); ?>" target="_blank">Hotel Website</a></div>
        <?php endif; ?>
        <a href="<?php echo $base_path; ?>bookings" class="btn-primary">Back to My Bookings</a>
    </div>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html> 