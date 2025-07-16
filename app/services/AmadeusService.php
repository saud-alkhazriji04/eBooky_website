<?php
class AmadeusService {
    private $clientId = 'Your_Client_ID';
    private $clientSecret = 'Your_Client_Secret';
    private $baseUrl = 'https://test.api.amadeus.com';
    private $cacheDir = __DIR__ . '/../../cache/amadeus/';

    public function __construct() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    private function getToken() {
        $tokenFile = $this->cacheDir . 'token.json';
        if (file_exists($tokenFile)) {
            $data = json_decode(file_get_contents($tokenFile), true);
            if ($data && isset($data['expires_at']) && $data['expires_at'] > time()) {
                return $data['access_token'];
            }
        }
        // Request new token
        $ch = curl_init($this->baseUrl . '/v1/security/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        if (isset($data['access_token'])) {
            $data['expires_at'] = time() + $data['expires_in'] - 60; // buffer
            file_put_contents($tokenFile, json_encode($data));
            return $data['access_token'];
        }
        return null;
    }

    public function searchFlights($params) {
        $cacheKey = md5(json_encode($params));
        $cacheFile = $this->cacheDir . 'flight_' . $cacheKey . '.json';
        // Cache for 5 minutes (reduced from 10 to get fresher data)
        if (file_exists($cacheFile) && filemtime($cacheFile) > (time() - 300)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        $token = $this->getToken();
        if (!$token) return null;
        
        // Build query parameters
        $queryParams = [
            'originLocationCode' => $params['from'],
            'destinationLocationCode' => $params['to'],
            'departureDate' => $params['departure_date'],
            'adults' => $params['passengers'],
            'nonStop' => 'false',
            'max' => 50, // Increased from 10 to get more variety
            'currencyCode' => 'USD'
        ];
        
        // Add return date if provided
        if (!empty($params['return_date'])) {
            $queryParams['returnDate'] = $params['return_date'];
        }
        
        // Add cabin class if specified
        if (!empty($params['cabin_class'])) {
            $queryParams['travelClass'] = strtoupper($params['cabin_class']);
        }
        
        $query = http_build_query($queryParams);
        $url = $this->baseUrl . '/v2/shopping/flight-offers?' . $query;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        
        // Debug: Log the response for troubleshooting
        if (isset($data['data'])) {
            file_put_contents($cacheFile, json_encode($data));
            
            // Log unique airlines found
            $airlines = [];
            foreach ($data['data'] as $offer) {
                foreach ($offer['itineraries'] as $itinerary) {
                    foreach ($itinerary['segments'] as $segment) {
                        $airlines[] = $segment['carrierCode'];
                    }
                }
            }
            $uniqueAirlines = array_unique($airlines);
            error_log("Flight search for {$params['from']}-{$params['to']} found " . count($data['data']) . " offers from airlines: " . implode(', ', $uniqueAirlines));
        } else {
            error_log("Flight search failed for {$params['from']}-{$params['to']}: " . json_encode($data));
        }
        
        return $data;
    }

    public function bookFlight($flightOffer, $user) {
        $token = $this->getToken();
        if (!$token) return null;
        $url = $this->baseUrl . '/v1/booking/flight-orders';
        $payload = [
            'data' => [
                'type' => 'flight-order',
                'flightOffers' => [$flightOffer],
                'travelers' => [[
                    'id' => '1',
                    'dateOfBirth' => '1990-01-01',
                    'name' => [
                        'firstName' => $user['name'],
                        'lastName' => 'Traveler'
                    ],
                    'gender' => 'MALE',
                    'contact' => [
                        'emailAddress' => $user['email'],
                        'phones' => [[
                            'deviceType' => 'MOBILE',
                            'countryCallingCode' => '1',
                            'number' => '0000000000'
                        ]]
                    ],
                    'documents' => [[
                        'documentType' => 'PASSPORT',
                        'number' => 'X1234567',
                        'expiryDate' => '2030-01-01',
                        'issuanceCountry' => 'US',
                        'nationality' => 'US',
                        'holder' => true
                    ]]
                ]]
            ]
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function getSeatmap($flightOffer) {
        $cacheKey = md5(json_encode($flightOffer));
        $cacheFile = $this->cacheDir . 'seatmap_' . $cacheKey . '.json';
        // Cache for 30 minutes
        if (file_exists($cacheFile) && filemtime($cacheFile) > (time() - 1800)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        $token = $this->getToken();
        if (!$token) return null;
        
        $url = $this->baseUrl . '/v1/shopping/seatmaps';
        $payload = [
            'data' => [
                'type' => 'seatmap-display',
                'flightOffers' => [$flightOffer]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        
        if (isset($data['data'])) {
            file_put_contents($cacheFile, json_encode($data));
        }
        
        return $data;
    }

    // Hotel Search API
    public function searchHotels($params) {
        $cacheKey = md5(json_encode($params));
        $cacheFile = $this->cacheDir . 'hotel_' . $cacheKey . '.json';
        // Cache for 10 minutes
        if (file_exists($cacheFile) && filemtime($cacheFile) > (time() - 600)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        $token = $this->getToken();
        if (!$token) return null;

        // If hotelIds is set, skip city lookup and use directly
        if (!empty($params['hotelIds'])) {
            $hotelIdsStr = $params['hotelIds'];
        } else {
            // Step 1: Get hotel IDs for the city
            $cityCode = $params['city'] ?? '';
            if (!$cityCode) {
                return ['errors' => [['detail' => 'No city or hotelIds provided.']]];
            }
            $hotelIdsUrl = $this->baseUrl . '/v1/reference-data/locations/hotels/by-city?cityCode=' . urlencode($cityCode);
            $ch = curl_init($hotelIdsUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token
            ]);
            $hotelIdsResponse = curl_exec($ch);
            curl_close($ch);
            $hotelIdsData = json_decode($hotelIdsResponse, true);
            if (!isset($hotelIdsData['data']) || count($hotelIdsData['data']) === 0) {
                $error = [
                    'errors' => [[
                        'detail' => 'No hotels found for this city.'
                    ]]
                ];
                file_put_contents($cacheFile, json_encode($error));
                return $error;
            }
            $hotelIds = array_map(function($h) { return $h['hotelId']; }, $hotelIdsData['data']);
            $hotelIdsStr = implode(',', array_slice($hotelIds, 0, 50)); // Limit to 50 hotels
        }
        // Step 2: Fetch hotel offers for these hotel IDs
        $queryParams = [
            'hotelIds' => $hotelIdsStr,
            'checkInDate' => $params['check_in'],
            'checkOutDate' => $params['check_out'],
            'adults' => $params['guests'],
            'roomQuantity' => $params['rooms'],
            'currency' => 'USD',
            'bestRateOnly' => 'true',
            'view' => 'FULL',
            'sort' => 'PRICE',
            'page[limit]' => 50
        ];
        if (!empty($params['price_range'])) {
            $priceRanges = [
                'budget' => [0, 100],
                'mid' => [100, 300],
                'luxury' => [300, 1000],
                'ultra' => [1000, 9999]
            ];
            $range = $priceRanges[$params['price_range']] ?? null;
            if ($range) {
                $queryParams['priceRange'] = $range[0] . '-' . $range[1];
            }
        }
        if (!empty($params['stars'])) {
            $queryParams['ratings'] = $params['stars'];
        }
        $query = http_build_query($queryParams);
        $url = $this->baseUrl . '/v3/shopping/hotel-offers?' . $query;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        if (isset($data['data'])) {
            file_put_contents($cacheFile, json_encode($data));
            error_log("Hotel search for " . (!empty($params['hotelIds']) ? $params['hotelIds'] : ($params['city'] ?? '')) . " found " . count($data['data']) . " hotels");
        } else {
            error_log("Hotel search failed for " . (!empty($params['hotelIds']) ? $params['hotelIds'] : ($params['city'] ?? '')) . ": " . json_encode($data));
        }
        return $data;
    }

    // Hotel Booking API
    public function bookHotel($hotelOffer, $user, $bookingParams) {
        $token = $this->getToken();
        if (!$token) return null;
        
        $url = $this->baseUrl . '/v1/booking/hotel-bookings';
        $payload = [
            'data' => [
                'offerId' => $hotelOffer['id'],
                'guests' => [
                    [
                        'name' => [
                            'firstName' => $user['name'],
                            'lastName' => 'Guest'
                        ],
                        'contact' => [
                            'email' => $user['email'],
                            'phone' => '+10000000000'
                        ]
                    ]
                ],
                'payments' => [
                    [
                        'method' => 'creditCard',
                        'card' => [
                            'vendorCode' => 'VI',
                            'cardNumber' => '4111111111111111',
                            'expiryDate' => '2025-01'
                        ]
                    ]
                ]
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        
        if (isset($data['data'])) {
            error_log("Hotel booking successful: " . $data['data']['id']);
        } else {
            error_log("Hotel booking failed: " . json_encode($data));
        }
        
        return $data;
    }

    // Hotel Name Autocomplete API
    public function hotelAutocomplete($keyword) {
        $cacheKey = md5($keyword);
        $cacheFile = $this->cacheDir . 'hotel_autocomplete_' . $cacheKey . '.json';
        // Cache for 1 hour
        if (file_exists($cacheFile) && filemtime($cacheFile) > (time() - 3600)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        $token = $this->getToken();
        if (!$token) return null;
        
        $queryParams = [
            'keyword' => $keyword,
            'subType' => 'HOTEL_LEISURE'
        ];
        
        $query = http_build_query($queryParams);
        $url = $this->baseUrl . '/v1/reference-data/locations/hotels/by-keyword?' . $query;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        
        if (isset($data['data'])) {
            file_put_contents($cacheFile, json_encode($data));
        }
        
        return $data;
    }

    // Hotel Ratings API
    public function getHotelRatings($hotelId) {
        $cacheKey = md5($hotelId);
        $cacheFile = $this->cacheDir . 'hotel_ratings_' . $cacheKey . '.json';
        // Cache for 24 hours
        if (file_exists($cacheFile) && filemtime($cacheFile) > (time() - 86400)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        $token = $this->getToken();
        if (!$token) return null;
        
        $url = $this->baseUrl . '/v2/e-reputation/hotel-sentiments?hotelIds=' . $hotelId;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        
        if (isset($data['data'])) {
            file_put_contents($cacheFile, json_encode($data));
        }
        
        return $data;
    }

    // Get hotel details by ID
    public function getHotelDetails($hotelId) {
        $cacheKey = md5($hotelId);
        $cacheFile = $this->cacheDir . 'hotel_details_' . $cacheKey . '.json';
        // Cache for 24 hours
        if (file_exists($cacheFile) && filemtime($cacheFile) > (time() - 86400)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        $token = $this->getToken();
        if (!$token) return null;
        
        $url = $this->baseUrl . '/v2/reference-data/locations/hotels/' . $hotelId;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        
        if (isset($data['data'])) {
            file_put_contents($cacheFile, json_encode($data));
        }
        
        return $data;
    }

    // Get offers for a specific hotel using /by-hotel endpoint
    public function getHotelOffersByHotel($hotelId, $adults, $checkInDate, $checkOutDate) {
        $token = $this->getToken();
        if (!$token) return null;
        $queryParams = [
            'hotelId' => $hotelId,
            'adults' => $adults,
            'checkInDate' => $checkInDate,
            'checkOutDate' => $checkOutDate
        ];
        $query = http_build_query($queryParams);
        $url = $this->baseUrl . '/v3/shopping/hotel-offers/by-hotel?' . $query;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function searchTransfers($params) {
        $url = 'https://test.api.amadeus.com/v1/shopping/transfer-offers';
        $token = $this->getToken();
        $payload = [
            'startLocationCode' => $params['startLocationCode'],
            'endLocationCode' => $params['endLocationCode'],
            'startDateTime' => $params['startDateTime'],
        ];
        if (isset($params['passengers'])) {
            $payload['passengers'] = $params['passengers'];
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        return $data['data'] ?? [];
    }

    public function bookTransfer($offerId, $userData) {
        // Mock booking: generate fake booking_ref, save to cache
        $booking_ref = 'CAR' . time() . rand(100,999);
        $booking = [
            'booking_ref' => $booking_ref,
            'offer_id' => $offerId,
            'user' => $userData,
            'status' => 'booked',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $cacheFile = __DIR__ . '/../../cache/amadeus/transfer_booking_' . $booking_ref . '.json';
        file_put_contents($cacheFile, json_encode($booking));
        return $booking;
    }

    public function getTransferBooking($booking_ref) {
        $cacheFile = __DIR__ . '/../../cache/amadeus/transfer_booking_' . $booking_ref . '.json';
        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        return null;
    }

    public function cancelTransferBooking($booking_ref) {
        $cacheFile = __DIR__ . '/../../cache/amadeus/transfer_booking_' . $booking_ref . '.json';
        if (file_exists($cacheFile)) {
            $booking = json_decode(file_get_contents($cacheFile), true);
            $booking['status'] = 'cancelled';
            file_put_contents($cacheFile, json_encode($booking));
            return true;
        }
        return false;
    }
} 