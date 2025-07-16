<?php
class FlightController {
    public function search() {
        require_once __DIR__ . '/../services/AmadeusService.php';
        $params = [
            'from' => $_GET['from'] ?? '',
            'to' => $_GET['to'] ?? '',
            'departure_date' => $_GET['departure_date'] ?? '',
            'return_date' => $_GET['return_date'] ?? '',
            'passengers' => $_GET['passengers'] ?? 1,
        ];
        $results = null;
        $calendar = [];
        if ($params['from'] && $params['to'] && $params['departure_date']) {
            $amadeus = new AmadeusService();
            
            // Get the main search results first
            $results = $amadeus->searchFlights($params);
            
            // Debug: Log the number of results
            if ($results && isset($results['data'])) {
                error_log("Main search returned " . count($results['data']) . " flights");
            }
            
            // Generate calendar from main results instead of making separate API calls
            if ($results && isset($results['data']) && count($results['data']) > 0) {
                $baseDate = strtotime($params['departure_date']);
                for ($i = -3; $i <= 3; $i++) {
                    $date = date('Y-m-d', strtotime("$i day", $baseDate));
                    $lowest = null;
                    
                    // Find the lowest price flight for this date from our main results
                    foreach ($results['data'] as $offer) {
                        $offerDate = date('Y-m-d', strtotime($offer['itineraries'][0]['segments'][0]['departure']['at']));
                        if ($offerDate === $date) {
                            if (!$lowest || $offer['price']['total'] < $lowest['price']['total']) {
                                $lowest = $offer;
                            }
                        }
                    }
                    
                    if ($lowest) {
                        $calendar[$date] = [
                            'price' => $lowest['price']['total'],
                            'currency' => $lowest['price']['currency'],
                        ];
                    }
                }
            }
            
            // Apply filters to main results
            if ($results && isset($results['data'])) {
                $originalCount = count($results['data']);
                
                // Remove duplicates first (based on flight offer ID and key characteristics)
                $uniqueFlights = [];
                $seenKeys = [];
                foreach ($results['data'] as $offer) {
                    // Create a unique key based on carrier, flight number, departure time, and price
                    $segment = $offer['itineraries'][0]['segments'][0];
                    $uniqueKey = $segment['carrierCode'] . '_' . 
                                $segment['number'] . '_' . 
                                $segment['departure']['at'] . '_' . 
                                $offer['price']['total'];
                    
                    if (!in_array($uniqueKey, $seenKeys)) {
                        $seenKeys[] = $uniqueKey;
                        $uniqueFlights[] = $offer;
                    }
                }
                $results['data'] = $uniqueFlights;
                
                error_log("After deduplication: " . count($results['data']) . " flights (was $originalCount)");
                
                $results['data'] = array_filter($results['data'], function($offer) {
                    // Stops filter
                    if (isset($_GET['stops']) && $_GET['stops'] !== '') {
                        $stops = (int)$_GET['stops'];
                        $segments = count($offer['itineraries'][0]['segments']);
                        if ($stops === 0 && $segments > 1) return false;
                        if ($stops === 1 && $segments !== 2) return false;
                        if ($stops === 2 && $segments < 3) return false;
                    }
                    // Airlines filter
                    if (!empty($_GET['airlines'])) {
                        $found = false;
                        foreach ($offer['itineraries'] as $itinerary) {
                            foreach ($itinerary['segments'] as $seg) {
                                if (in_array($seg['carrierCode'], (array)$_GET['airlines'])) $found = true;
                            }
                        }
                        if (!$found) return false;
                    }
                    // Departure time filter
                    if (!empty($_GET['dep_time'])) {
                        $dep = $offer['itineraries'][0]['segments'][0]['departure']['at'];
                        $hour = (int)date('H', strtotime($dep));
                        $map = [
                            'morning' => [5,12],
                            'afternoon' => [12,17],
                            'evening' => [17,21],
                            'night' => [21,24],
                        ];
                        $sel = $_GET['dep_time'];
                        if ($sel === 'night' && ($hour >= 0 && $hour < 5)) return true;
                        if ($sel !== 'night' && ($hour < $map[$sel][0] || $hour >= $map[$sel][1])) return false;
                    }
                    // Cabin class filter
                    if (!empty($_GET['cabin']) && isset($offer['class'][0])) {
                        if (strtoupper($offer['class'][0]) !== strtoupper($_GET['cabin'])) return false;
                    }
                    // Fare type filter
                    if (!empty($_GET['fare_type']) && isset($offer['fareBasis'][0])) {
                        if (stripos($offer['fareBasis'][0], $_GET['fare_type']) === false) return false;
                    }
                    // Price range filter
                    if (!empty($_GET['min_price']) && $offer['price']['total'] < $_GET['min_price']) return false;
                    if (!empty($_GET['max_price']) && $offer['price']['total'] > $_GET['max_price']) return false;
                    return true;
                });
                
                // Debug: Log filtered results
                $filteredCount = count($results['data']);
                error_log("After filtering: $filteredCount flights (was $originalCount)");
            }
        }
        require __DIR__ . '/../views/flights/search.php';
    }

    public function book() {
        if (empty($_SESSION['user'])) {
            $base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            // If we're in a subdirectory (like /flights), go up to the main directory
            if (strpos($_SERVER['SCRIPT_NAME'], '/flights') !== false) {
                $base_path = dirname($base_path);
            }
            $message = 'You must be signed in to book a flight. <a href="' . $base_path . '/auth/login">Sign in</a> or <a href="' . $base_path . '/auth/register">register</a>.';
            require __DIR__ . '/../views/flights/book_error.php';
            return;
        }
        if (!isset($_POST['flight_offer'])) {
            $message = 'No flight offer selected.';
            require __DIR__ . '/../views/flights/book_error.php';
            return;
        }
        $flightOffer = json_decode($_POST['flight_offer'], true);
        $_SESSION['flight_offer'] = $flightOffer;
        
        // Store search parameters for seat selection
        $_SESSION['search_params'] = [
            'passengers' => $_POST['passengers'] ?? 1,
            'from' => $_POST['from'] ?? '',
            'to' => $_POST['to'] ?? '',
            'departure_date' => $_POST['departure_date'] ?? ''
        ];
        
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/flights/seatmap');
        exit;
    }

    public function seatmap() {
        if (empty($_SESSION['user']) || empty($_SESSION['flight_offer'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');
            exit;
        }
        
        $flightOffer = $_SESSION['flight_offer'];
        $amadeus = new AmadeusService();
        $seatmapData = $amadeus->getSeatmap($flightOffer);
        
        require __DIR__ . '/../views/flights/seatmap.php';
    }

    public function payment() {
        if (empty($_SESSION['user']) || empty($_SESSION['flight_offer'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');
            exit;
        }
        
        // Store seat selection data from POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['selected_seats'])) {
                $_SESSION['selected_seats'] = $_POST['selected_seats'];
            }
            if (!empty($_POST['seat_price'])) {
                $_SESSION['seat_price'] = $_POST['seat_price'];
            }
        }
        
        $flightOffer = $_SESSION['flight_offer'];
        require __DIR__ . '/../views/flights/payment.php';
    }

    private function getCurrencyForCountry($country) {
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
            // Add more as needed
        ];
        return $map[$country] ?? 'usd';
    }

    private function getStripeAmount($amount, $currency) {
        $threeDecimal = ['bhd', 'jod', 'kwd', 'omr', 'tnd'];
        if (in_array($currency, $threeDecimal)) {
            // Multiply by 1000, then round down to nearest 10
            return floor($amount * 1000 / 10) * 10;
        }
        // Default: multiply by 100 (cents)
        return round($amount * 100);
    }

    public function charge() {
        if (empty($_SESSION['user']) || empty($_SESSION['flight_offer'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');
            exit;
        }
        
        $flightOffer = $_SESSION['flight_offer'];
        $user = $_SESSION['user'];

        // Process payment (simulate, like hotel)
        $paymentData = [
            'amount' => $_POST['amount'] ?? $flightOffer['price']['total'],
            'currency' => $_POST['currency'] ?? $flightOffer['price']['currency'],
            'payment_method' => $_POST['payment_method'] ?? 'credit_card',
            'card_number' => $_POST['card_number'] ?? '',
            'expiry' => $_POST['expiry'] ?? '',
            'cvv' => $_POST['cvv'] ?? ''
        ];

        // Simulate successful payment and booking
        $bookingRef = 'FLIGHT-' . uniqid();
        $bookingDetails = $flightOffer;
        $bookingDetails['payment'] = $paymentData;
        if (!empty($_SESSION['selected_seats'])) {
            $bookingDetails['selected_seats'] = json_decode($_SESSION['selected_seats'], true);
        }
        if (!empty($_SESSION['seat_price'])) {
            $bookingDetails['seat_upgrade'] = $_SESSION['seat_price'];
        }
        $bookingDetails['total_amount'] = $paymentData['amount'];

        // Save to database
        require_once __DIR__ . '/../models/Booking.php';
        $db = require __DIR__ . '/../db.php';
        $bookingModel = new Booking($db);
        $bookingData = [
            'user_id' => $user['id'],
            'type' => 'flight',
            'status' => 'booked',
            'booking_ref' => $bookingRef,
            'details' => json_encode($bookingDetails)
        ];
        $booking_id = $bookingModel->create($bookingData);
        // Airtable integration
        $config = require __DIR__ . '/../config.php';
        require_once __DIR__ . '/../services/AirtableService.php';
        $airtable = new AirtableService(
            $config['airtable_token'],
            $config['airtable_base']
        );
        // Prepare fields for Airtable
        $flight = $flightOffer['itineraries'][0]['segments'][0];
        $fields = [
            'Name' => $user['name'],
            'Email' => $user['email'],
            'BookingID' => $bookingRef,
            'FlightNumber' => $flight['carrierCode'] . $flight['number'],
            'From' => $flight['departure']['iataCode'],
            'To' => $flight['arrival']['iataCode'],
            'DepartureDate' => substr($flight['departure']['at'], 0, 10),
            'DepartureTime' => substr($flight['departure']['at'], 11, 5),
            'ArrivalDate' => substr($flight['arrival']['at'], 0, 10),
            'ArrivalTime' => substr($flight['arrival']['at'], 11, 5),
            'Status' => 'Confirmed',
            'BookingDate' => date('Y-m-d'),
        ];
        $airtable_id = $airtable->addFlightBooking($fields);
        $bookingModel->updateAirtableId($booking_id, $airtable_id);
        // Store booking result for confirmation page
        $_SESSION['flight_booking_result'] = [ 'data' => $bookingDetails, 'booking_ref' => $bookingRef ];
        $_SESSION['booking_ref'] = $bookingRef;
        // Clear session data
        unset($_SESSION['flight_offer']);
        unset($_SESSION['selected_seats']);
        unset($_SESSION['seat_price']);
        unset($_SESSION['search_params']);
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/flights/confirmation');
        exit;
    }

    public function confirmation() {
        if (empty($_SESSION['booking_ref'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');
            exit;
        }
        $bookingRef = $_SESSION['booking_ref'];
        unset($_SESSION['booking_ref']);
        require __DIR__ . '/../views/flights/confirmation.php';
    }
} 