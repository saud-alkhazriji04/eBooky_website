<?php
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
function safe_html($value) {
    return is_string($value) ? htmlspecialchars($value) : '';
}
// Navigation stepper (like flights)
$steps = [
    'Search',
    'Details',
    'Payment',
    'Confirmation'
];
$currentStep = 1; // Details
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Details - Ebooky</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>css/style.css">
    <style>
        .stepper .active { background: #003580; color: #fff; }
        .hotel-details-card img.main-image { transition: box-shadow 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .hotel-details-card .thumb.selected { border: 2px solid #003580; }
        .back-btn { background: #eee; color: #003580; border: none; border-radius: 6px; padding: 0.5em 1.2em; font-size: 1em; cursor: pointer; margin-bottom: 1.5em; }
        .back-btn:hover { background: #003580; color: #fff; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../partials/navbar.php'; ?>
<main style="max-width:900px;margin:2rem auto;">
    <button class="back-btn" onclick="window.history.back()">&larr; Back</button>
    <div class="stepper" style="display:flex;gap:2em;justify-content:center;margin-bottom:2em;">
        <?php foreach ($steps as $i => $step): ?>
            <div style="text-align:center;">
                <div style="width:32px;height:32px;border-radius:50%;background:<?php echo $i==$currentStep?'#003580':'#eee'; ?>;color:<?php echo $i==$currentStep?'#fff':'#888'; ?>;display:flex;align-items:center;justify-content:center;margin:0 auto;font-weight:bold;"> <?php echo $i+1; ?> </div>
                <div style="margin-top:0.5em;font-size:0.95em;color:<?php echo $i==$currentStep?'#003580':'#888'; ?>;"> <?php echo $step; ?> </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="hotel-details-card" style="background:#fff;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,0.08);padding:2em;display:flex;gap:2em;">
        <div style="flex:1;">
            <?php // Images: reuse random selection logic from search.php
            $mainImages = [
                'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/271639/pexels-photo-271639.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/210604/pexels-photo-210604.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/189296/pexels-photo-189296.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/261187/pexels-photo-261187.jpeg?auto=compress&w=400&h=300',
            ];
            $otherImages = [
                'https://images.pexels.com/photos/271639/pexels-photo-271639.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/210604/pexels-photo-210604.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/189296/pexels-photo-189296.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/261187/pexels-photo-261187.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/261187/pexels-photo-261187.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/210604/pexels-photo-210604.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg?auto=compress&w=400&h=300',
                'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&w=400&h=300',
            ];
            shuffle($mainImages); shuffle($otherImages);
            ?>
            <img src="<?php echo $mainImages[0]; ?>" alt="Hotel main" class="main-image" id="mainImage" style="width:100%;border-radius:8px;max-height:300px;object-fit:cover;">
            <div style="display:flex;gap:0.5em;margin-top:0.5em;">
                <?php for($i=0;$i<5;$i++): ?>
                    <img src="<?php echo $otherImages[$i]; ?>" alt="Hotel extra" class="thumb" style="width:60px;height:60px;border-radius:6px;object-fit:cover;cursor:pointer;<?php if($i==0)echo'border:2px solid #003580;'; ?>" onclick="setMainImage(this)">
                <?php endfor; ?>
            </div>
        </div>
        <div style="flex:2;">
            <h2><?php echo safe_html($hotelOffer['hotel']['name'] ?? 'Hotel'); ?></h2>
            <div style="color:#888;font-size:1.1em;margin-bottom:0.5em;">
                <?php echo safe_html($hotelOffer['hotel']['address']['address'] ?? ''); ?>, 
                <?php echo safe_html($hotelOffer['hotel']['address']['cityName'] ?? ''); ?>, 
                <?php echo safe_html($hotelOffer['hotel']['address']['countryCode'] ?? ''); ?>
            </div>
            <?php if (!empty($hotelOffer['hotel']['rating'])): ?>
                <div style="margin-bottom:0.5em;">Rating: <?php echo str_repeat('★', (int)$hotelOffer['hotel']['rating']); ?></div>
            <?php endif; ?>
            <?php 
            $facilityMap = [
                'pool' => ['pool', 'indoor pool', 'outdoor pool', 'swimming pool', 'children\'s pool', 'plunge pool'],
                'spa' => ['spa', 'spa treatment', 'sauna', 'steam room', 'massage', 'hammam', 'hot tub'],
                'fitness' => ['fitness', 'gym', 'health club'],
                'wifi' => ['wifi', 'wireless internet'],
                'bar' => ['bar', 'lounge'],
                'restaurant' => ['restaurant', 'coffee shop', 'cafe', 'breakfast'],
                'parking' => ['parking', 'car park', 'private parking', 'free parking'],
                'family' => ['family room', 'family rooms', 'kid', 'children\'s club'],
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
            $hotelFacilityStr = $hotelOffer['hotel']['facilities'] ?? '';
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
            if (!empty($foundFacilities)) {
                echo '<h3>Most popular facilities</h3>';
                echo '<div class="popular-facilities-grid">';
                $facilityIcons = [
                    'pool' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/><path d="M6 16V4m6 12V4m6 12V4"/></svg>',
                    'fitness' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="9" width="20" height="6" rx="3"/><path d="M6 9V5m12 4V5"/></svg>',
                    'non-smoking' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M4.93 4.93l14.14 14.14"/><path d="M8 12h8"/></svg>',
                    'parking' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><text x="12" y="16" text-anchor="middle" font-size="10" fill="#15803d" font-family="Arial">P</text></svg>',
                    'wifi' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M12 20h.01"/></svg>',
                    'family' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="10" r="3"/><circle cx="17" cy="10" r="3"/><path d="M7 13v5m10-5v5"/><path d="M12 17v2"/><circle cx="12" cy="17" r="2"/></svg>',
                    'elevator' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M12 6v8"/><path d="M9 9l3-3 3 3"/><path d="M9 15l3 3 3-3"/></svg>',
                    'air conditioning' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.07 7.07l-1.41-1.41M6.34 6.34L4.93 4.93m12.73 0l-1.41 1.41M6.34 17.66l-1.41 1.41"/></svg>',
                    'spa' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="12" rx="10" ry="6"/><path d="M2 12s2-4 10-4 10 4 10 4"/></svg>',
                    'bar' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="10" width="18" height="7" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>',
                    'restaurant' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 3h16v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V3z"/><path d="M7 21V7m10 14V7"/></svg>',
                    'room service' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 16h8M8 12h8M8 8h8"/></svg>',
                    'arcade' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="8" cy="12" r="1"/><circle cx="16" cy="12" r="1"/></svg>',
                    'beach' => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20c2 0 2-2 4-2s2 2 4 2 2-2 4-2 2 2 4 2 2-2 4-2"/><path d="M12 2v6"/><path d="M8 6h8"/></svg>',
                ];
                foreach ($foundFacilities as $key => $label) {
                    echo '<div class="facility-item">' . ($facilityIcons[$key] ?? '') . '<span>' . safe_html($label) . '</span></div>';
                }
                echo '</div>';
                // Add grid CSS
                echo '<style>.popular-facilities-grid{display:flex;flex-wrap:wrap;gap:0.7em 1.5em;margin-bottom:1.2em;align-items:center;}.facility-item{display:flex;align-items:center;gap:0.4em;font-size:0.98em;color:#222;}.facility-item span{font-weight:400;}.facility-item svg{width:20px;height:20px;min-width:20px;min-height:20px;}</style>';
                // Show all (filtered) facilities button as before
                echo '<button id="show-all-facilities-btn" style="font-size:0.9em;padding:0.2em 0.7em;margin-bottom:0.5em;cursor:pointer;background:#f5f5f5;border:1px solid #ccc;border-radius:4px;">Show all facilities</button>';
                echo '<ul id="all-facilities-list" style="display:none;margin-top:0.5em;">';
                foreach ($hotelFacilities as $fac) {
                    foreach ($facilityMap as $key => $keywords) {
                        foreach ($keywords as $kw) {
                            if ($fac && stripos($fac, $kw) !== false) {
                                echo '<li>' . safe_html($facilityLabels[$key]) . '</li>';
                                break 2;
                            }
                        }
                    }
                }
                echo '</ul>';
                echo '<script>document.getElementById("show-all-facilities-btn").onclick=function(){
                    var l=document.getElementById("all-facilities-list");
                    if(l.style.display==="none"){l.style.display="block";this.textContent="Hide all facilities";}else{l.style.display="none";this.textContent="Show all facilities";}
                };</script>';
            }
            ?>
            <?php if (!empty($hotelOffer['hotel']['description'])): 
                $desc = $hotelOffer['hotel']['description'];
                // Remove meta/system fields (with or without colon, with optional whitespace)
                $desc = preg_replace('/<p>\s*(HeadLine|Location|Rooms|Dining|CheckIn Instructions|Special Instructions)\s*:?.*?<\/p>/i', '', $desc);
                $desc = preg_replace('/<ul>.*?<\/ul>/is', '', $desc); // Remove <ul> blocks (like CheckIn)
                $desc = preg_replace('/<p>\s*:?\s*<\/p>/i', '', $desc); // Remove empty <p> tags or those with just a colon
                echo '<div style="margin-bottom:0.5em; color:#444;">' . nl2br(safe_html($desc)) . '</div>';
            endif; ?>
            <?php if (!empty($hotelOffer['hotel']['website'])): ?>
                <div style="margin-bottom:0.5em;"><a href="<?php echo safe_html($hotelOffer['hotel']['website']); ?>" target="_blank">Hotel Website</a></div>
            <?php endif; ?>
            <div style="margin-bottom:1em;">
                <strong>Room:</strong> <?php echo safe_html($hotelOffer['room']['description'] ?? 'Standard Room'); ?><br>
                <?php
                $total = $hotelOffer['price']['total'] ?? 0;
                $currency = $hotelOffer['price']['currency'] ?? 'USD';
                $checkIn = $_GET['check_in'] ?? '';
                $checkOut = $_GET['check_out'] ?? '';
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
                <strong>Price per night:</strong> $<?php echo number_format($perNight, 2); ?> <?php echo safe_html($currency); ?><br>
                <strong>Total price:</strong> $<?php echo number_format($total, 2); ?> <?php echo safe_html($currency); ?>
            </div>
            <form method="post" action="<?php echo $base_path; ?>hotels/book">
                <input type="hidden" name="hotel_offer" value="<?php echo htmlspecialchars(json_encode($hotelOffer)); ?>">
                <input type="hidden" name="adults" value="<?php echo safe_html($adults); ?>">
                <input type="hidden" name="check_in" value="<?php echo safe_html($check_in); ?>">
                <input type="hidden" name="check_out" value="<?php echo safe_html($check_out); ?>">
                <button type="submit" class="btn-primary" style="margin-top:1em;">Book</button>
            </form>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../partials/footer.php'; ?>
<script>
function setMainImage(thumb) {
    var main = document.getElementById('mainImage');
    main.src = thumb.src;
    // Remove selected from all
    var thumbs = document.querySelectorAll('.thumb');
    thumbs.forEach(function(t) { t.classList.remove('selected'); });
    thumb.classList.add('selected');
}
</script>
</body>
</html> 