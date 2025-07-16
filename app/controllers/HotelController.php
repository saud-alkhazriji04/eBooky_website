<?php
class HotelController {
    public function search() {
        $params = [
            'query' => $_GET['city'] ?? '', // now used for country or hotel name
            'check_in' => $_GET['check_in'] ?? '',
            'check_out' => $_GET['check_out'] ?? '',
            'guests' => $_GET['guests'] ?? 1,
            'rooms' => $_GET['rooms'] ?? 1,
            'price_range' => $_GET['price_range'] ?? '',
            'stars' => $_GET['stars'] ?? '',
            'sort_by' => $_GET['sort_by'] ?? 'price'
        ];

        $results = null;
        $calendar = [];
        $hotels = $this->loadHotelsCsv();
        $filtered = [];
        if ($params['query'] && $params['check_in'] && $params['check_out']) {
            foreach ($hotels as $hotel) {
                $match = false;
                // Match by country (code or name) or hotel name (case-insensitive, partial)
                if ((isset($hotel['country']) && stripos($hotel['country'], $params['query']) !== false) ||
                    (isset($hotel['countyCode']) && stripos($hotel['countyCode'], $params['query']) !== false) ||
                    (isset($hotel['HotelName']) && stripos($hotel['HotelName'], $params['query']) !== false)
                ) {
                    $match = true;
                }
                if ($match) {
                    // Price and stars filter (simulate price for demo)
                    $star = isset($hotel['star_rating']) ? $hotel['star_rating'] : 1;
                    $hotel['offers'] = [[
                        'id' => md5(($hotel['HotelName'] ?? '') . $params['check_in'] . $params['check_out']),
                        'price' => [
                            'total' => $this->simulatePrice($star),
                            'currency' => 'USD'
                        ]
                    ]];
                    $hotel['hotel'] = [
                        'name' => $hotel['HotelName'] ?? '',
                        'rating' => $star,
                        'address' => [
                            'cityName' => $hotel['cityName'] ?? '',
                            'countryCode' => $hotel['countyCode'] ?? '',
                            'address' => $hotel['Address'] ?? ''
                        ],
                        'hotelId' => $hotel['HotelName'] ?? '',
                        'description' => $hotel['Description'] ?? '',
                        'facilities' => $hotel['HotelFacilities'] ?? '',
                        'website' => $hotel['HotelWebsiteUrl'] ?? ''
                    ];
                    $filtered[] = $hotel;
                }
            }
            // Apply stars filter
            if (!empty($params['stars'])) {
                $filtered = array_filter($filtered, function($h) use ($params) {
                    return (int)$h['star_rating'] >= (int)$params['stars'];
                });
            }
            // Sort
            if ($params['sort_by'] === 'price') {
                usort($filtered, function($a, $b) {
                    return $a['offers'][0]['price']['total'] <=> $b['offers'][0]['price']['total'];
                });
            } elseif ($params['sort_by'] === 'rating') {
                usort($filtered, function($a, $b) {
                    return $b['star_rating'] <=> $a['star_rating'];
                });
            }
            $results = ['data' => $filtered];
        }
        require __DIR__ . '/../views/hotels/search.php';
    }

    private function loadHotelsCsv() {
        $csvFile = __DIR__ . '/../../hotels.csv';
        $hotels = [];
        if (($handle = fopen($csvFile, 'r')) !== false) {
            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $hotels[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $hotels;
    }

    private function simulatePrice($stars) {
        // Simulate price based on star rating
        $base = 50 + ((int)$stars * 50);
        return rand($base, $base + 100);
    }

    public function book() {
        if (empty($_SESSION['user'])) {
            $base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            if (strpos($_SERVER['SCRIPT_NAME'], '/hotels') !== false) {
                $base_path = dirname($base_path);
            }
            $message = 'You must be signed in to book a hotel. <a href="' . $base_path . '/auth/login">Sign in</a> or <a href="' . $base_path . '/auth/register">register</a>.';
            require __DIR__ . '/../views/hotels/book_error.php';
            return;
        }
        
        if (!isset($_POST['hotel_offer'])) {
            $message = 'No hotel offer selected.';
            require __DIR__ . '/../views/hotels/book_error.php';
            return;
        }
        
        $hotelOffer = json_decode($_POST['hotel_offer'], true);
        $_SESSION['hotel_offer'] = $hotelOffer;
        
        // Store booking parameters
        $_SESSION['hotel_booking_params'] = [
            'guests' => $_POST['guests'] ?? 1,
            'rooms' => $_POST['rooms'] ?? 1,
            'city' => $_POST['city'] ?? '',
            'check_in' => $_POST['check_in'] ?? '',
            'check_out' => $_POST['check_out'] ?? ''
        ];
        
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/hotels/payment');
        exit;
    }

    public function payment() {
        if (empty($_SESSION['user']) || empty($_SESSION['hotel_offer'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');
            exit;
        }
        
        $hotelOffer = $_SESSION['hotel_offer'];
        require __DIR__ . '/../views/hotels/payment.php';
    }

    public function charge() {
        if (empty($_SESSION['user']) || empty($_SESSION['hotel_offer'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');
            exit;
        }
        
        $hotelOffer = $_SESSION['hotel_offer'];
        $user = $_SESSION['user'];
        
        // Process payment (simplified for demo)
        $paymentData = [
            'amount' => $_POST['amount'] ?? $hotelOffer['price']['total'],
            'currency' => $_POST['currency'] ?? $hotelOffer['price']['currency'],
            'payment_method' => $_POST['payment_method'] ?? 'credit_card',
            'card_number' => $_POST['card_number'] ?? '',
            'expiry' => $_POST['expiry'] ?? '',
            'cvv' => $_POST['cvv'] ?? ''
        ];
        
        // Simulate successful payment and booking
        $bookingResult = [
            'data' => [
                'id' => 'HOTEL-' . uniqid(),
                'hotel' => $hotelOffer['hotel'],
                'offer' => $hotelOffer,
                'booking_params' => $_SESSION['hotel_booking_params'],
                'payment' => $paymentData
            ]
        ];
        // Save to database
        require_once __DIR__ . '/../models/Booking.php';
        $db = require __DIR__ . '/../db.php';
        $bookingModel = new Booking($db);
        $bookingData = [
            'user_id' => $user['id'],
            'type' => 'hotel',
            'status' => 'booked',
            'booking_ref' => $bookingResult['data']['id'],
            'details' => json_encode($bookingResult['data'])
        ];
        $booking_id = $bookingModel->create($bookingData);
        // Airtable integration
        $config = require __DIR__ . '/../config.php';
        require_once __DIR__ . '/../services/AirtableService.php';
        $airtable = new AirtableService(
            $config['airtable_token'],
            $config['airtable_base']
        );
        $fields = [
            'Name' => $user['name'],
            'Email' => $user['email'],
            'BookingID' => $bookingResult['data']['id'],
            'HotelName' => $bookingResult['data']['hotel']['name'] ?? '',
            'City' => $bookingResult['data']['hotel']['address']['cityName'] ?? '',
            'CheckInDate' => $bookingResult['data']['booking_params']['check_in'] ?? '',
            'CheckOutDate' => $bookingResult['data']['booking_params']['check_out'] ?? '',
            'RoomType' => $bookingResult['data']['room']['description'] ?? 'Standard',
            'Status' => 'Confirmed',
            'BookingDate' => date('Y-m-d'),
        ];
        $airtable_id = $airtable->addHotelBooking($fields);
        $bookingModel->updateAirtableId($booking_id, $airtable_id);
        // Store booking result for confirmation page
        $_SESSION['hotel_booking_result'] = $bookingResult;
        // Clear session data
        unset($_SESSION['hotel_offer']);
        unset($_SESSION['hotel_booking_params']);
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/hotels/confirmation');
        exit;
    }

    public function confirmation() {
        if (empty($_SESSION['user']) || empty($_SESSION['hotel_booking_result'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/');
            exit;
        }
        
        $bookingResult = $_SESSION['hotel_booking_result'];
        unset($_SESSION['hotel_booking_result']); // Clear after displaying
        
        require __DIR__ . '/../views/hotels/confirmation.php';
    }

    public function autocomplete() {
        if (!isset($_GET['keyword'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Keyword required']);
            return;
        }
        $keyword = strtolower($_GET['keyword']);
        $hotels = $this->loadHotelsCsv();
        $suggestions = [];
        $countries = [];
        foreach ($hotels as $hotel) {
            if ((isset($hotel['HotelName']) && stripos($hotel['HotelName'], $keyword) !== false) ||
                (isset($hotel['country']) && stripos($hotel['country'], $keyword) !== false) ||
                (isset($hotel['countyCode']) && stripos($hotel['countyCode'], $keyword) !== false)
            ) {
                $suggestions[$hotel['HotelName']] = [
                    'name' => $hotel['HotelName'] ?? '',
                    'country' => $hotel['country'] ?? '',
                    'city' => $hotel['cityName'] ?? ''
                ];
                $countries[$hotel['country']] = [
                    'name' => $hotel['country'] ?? '',
                    'country' => $hotel['country'] ?? ''
                ];
            }
        }
        // Add country suggestions
        foreach ($countries as $c) {
            $suggestions[$c['name']] = $c;
        }
        $suggestions = array_values($suggestions);
        header('Content-Type: application/json');
        echo json_encode(['data' => $suggestions]);
    }

    public function ratings() {
        if (!isset($_GET['hotel_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Hotel ID required']);
            return;
        }
        
        require_once __DIR__ . '/../services/AmadeusService.php';
        $amadeus = new AmadeusService();
        $ratings = $amadeus->getHotelRatings($_GET['hotel_id']);
        
        header('Content-Type: application/json');
        echo json_encode($ratings);
    }

    public function view() {
        // Get hotel_id and offer_id from query
        $hotel_id = $_GET['hotel_id'] ?? '';
        $adults = $_GET['adults'] ?? 1;
        $check_in = $_GET['check_in'] ?? '';
        $check_out = $_GET['check_out'] ?? '';
        $hotels = $this->loadHotelsCsv();
        $hotelDetails = null;
        foreach ($hotels as $hotel) {
            if (($hotel['HotelName'] ?? '') === $hotel_id) {
                $hotelDetails = $hotel;
                break;
            }
        }
        if (!$hotelDetails) {
            $message = 'Hotel not found.';
            require __DIR__ . '/../views/hotels/book_error.php';
            return;
        }
        // Simulate price based on stars and facilities
        $star = isset($hotelDetails['star_rating']) ? $hotelDetails['star_rating'] : 1;
        $facilities = isset($hotelDetails['HotelFacilities']) ? explode(' ', $hotelDetails['HotelFacilities']) : [];
        $base = 50 + ((int)$star * 50);
        $facility_price = count($facilities) * 10;
        $price = $base + $facility_price;
        $hotelOffer = [
            'id' => md5(($hotelDetails['HotelName'] ?? '') . $check_in . $check_out),
            'hotel' => [
                'name' => $hotelDetails['HotelName'] ?? '',
                'rating' => $star,
                'address' => [
                    'cityName' => $hotelDetails['cityName'] ?? '',
                    'countryCode' => $hotelDetails['countyCode'] ?? '',
                    'address' => $hotelDetails['Address'] ?? ''
                ],
                'hotelId' => $hotelDetails['HotelName'] ?? '',
                'description' => $hotelDetails['Description'] ?? '',
                'facilities' => $hotelDetails['HotelFacilities'] ?? '',
                'website' => $hotelDetails['HotelWebsiteUrl'] ?? ''
            ],
            'room' => [
                'description' => 'Standard Room'
            ],
            'price' => [
                'total' => $price,
                'currency' => 'USD'
            ]
        ];
        require __DIR__ . '/../views/hotels/view.php';
    }
} 