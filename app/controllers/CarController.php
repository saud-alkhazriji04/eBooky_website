<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/AmadeusService.php';
require_once __DIR__ . '/../models/Booking.php';

class CarController extends Controller {
    private $amadeusService;

    public function __construct() {
        $this->amadeusService = new AmadeusService();
    }

    // Show search form and handle search
    public function search() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datetime = $_POST['datetime'];
            if (strlen($datetime) === 16) { // e.g., '2025-08-01T11:00'
                $datetime .= ':00';
            }
            $params = [
                'startLocationCode' => $_POST['pickup'],
                'endLocationCode' => $_POST['dropoff'],
                'startDateTime' => $datetime,
                'passengers' => $_POST['passengers'] ?? 1
            ];
            $offers = $this->amadeusService->searchTransfers($params);
            $_SESSION['last_transfer_offers'] = $offers;
            $this->view('car/search_form', [
                'offers' => $offers,
                'params' => $params
            ]);
        } else {
            $this->view('car/search_form');
        }
    }

    // Book a transfer (mocked)
    public function book() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offer_id'])) {
            // Find offer in session
            $offers = $_SESSION['last_transfer_offers'] ?? [];
            $offer = null;
            foreach ($offers as $o) {
                if ((string)($o['id'] ?? '') === (string)$_POST['offer_id']) {
                    $offer = $o;
                    break;
                }
            }
            if (!$offer) {
                $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/car/search';
                header('Location: ' . $base);
                exit;
            }
            $_SESSION['car_selected_offer'] = $offer;
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/car/payment';
            header('Location: ' . $base);
            exit;
        }
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/car/search';
        header('Location: ' . $base);
        exit;
    }

    // Show confirmation
    public function confirmation() {
        if (empty($_SESSION['user'])) {
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/auth/login';
            header('Location: ' . $base);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_SESSION['car_selected_offer'])) {
                $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/car/search';
                header('Location: ' . $base);
                exit;
            }
            $offer = $_SESSION['car_selected_offer'];
            $user = $_SESSION['user'];
            $bookingData = [
                'user_id' => $user['id'],
                'type' => 'car',
                'status' => 'booked',
                'booking_ref' => 'CAR-' . uniqid(),
                'details' => json_encode([
                    'vehicle' => $offer['vehicle']['description'] ?? '',
                    'provider' => $offer['serviceProvider']['name'] ?? '',
                    'pickup' => $offer['start']['locationCode'] ?? '',
                    'dropoff' => $offer['end']['locationCode'] ?? '',
                    'date' => $offer['start']['dateTime'] ?? '',
                    'price' => $offer['quotation']['monetaryAmount'] ?? '',
                    'currency' => $offer['quotation']['currencyCode'] ?? '',
                    'seats' => $offer['vehicle']['seats'][0]['count'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'image' => $offer['vehicle']['imageURL'] ?? ($offer['vehicle']['image'] ?? ($offer['vehicle']['pictures'][0] ?? '')),
                ])
            ];
            require_once __DIR__ . '/../db.php';
            $db = require __DIR__ . '/../db.php';
            $bookingModel = new Booking($db);
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
                'BookingID' => $bookingData['booking_ref'],
                'CarType' => $offer['vehicle']['description'] ?? '',
                'PickupLocation' => $offer['start']['locationCode'] ?? '',
                'DropoffLocation' => $offer['end']['locationCode'] ?? '',
                'PickupDate' => isset($offer['start']['dateTime']) ? substr($offer['start']['dateTime'], 0, 10) : '',
                'DropoffDate' => isset($offer['end']['dateTime']) ? substr($offer['end']['dateTime'], 0, 10) : '',
                'Status' => 'Confirmed',
                'BookingDate' => date('Y-m-d'),
            ];
            $airtable_id = $airtable->addCarBooking($fields);
            $bookingModel->updateAirtableId($booking_id, $airtable_id);
            // Store booking result for confirmation page
            $booking = [
                'booking_ref' => $bookingData['booking_ref'],
                'vehicle' => $offer['vehicle']['description'] ?? '',
                'provider' => $offer['serviceProvider']['name'] ?? '',
                'pickup' => $offer['start']['locationCode'] ?? '',
                'dropoff' => $offer['end']['locationCode'] ?? '',
                'date' => $offer['start']['dateTime'] ?? '',
                'price' => $offer['quotation']['monetaryAmount'] ?? '',
                'currency' => $offer['quotation']['currencyCode'] ?? '',
                'seats' => $offer['vehicle']['seats'][0]['count'] ?? '',
                'email' => $_POST['email'] ?? '',
            ];
            $_SESSION['car_booking_confirmation'] = $booking;
            unset($_SESSION['car_selected_offer']);
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/car/confirmation';
            header('Location: ' . $base);
            exit;
        }
        $booking = $_SESSION['car_booking_confirmation'] ?? null;
        $this->view('car/confirmation', ['booking' => $booking]);
    }

    // View a booking
    public function viewBooking($id) {
        $bookingModel = new Booking(Booking::getPDO());
        $booking = $bookingModel->findById($id);
        $this->view('car/view', ['booking' => $booking]);
    }

    // Cancel a booking (mocked)
    public function cancel($id) {
        $bookingModel = new Booking(Booking::getPDO());
        $booking = $bookingModel->findById($id);
        if ($booking && $booking['type'] === 'car') {
            $this->amadeusService->cancelTransferBooking($booking['booking_ref']);
            $bookingModel->updateStatus($id, 'cancelled');
        }
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/mytrips';
        header('Location: ' . $base);
        exit;
    }

    // View a transfer offer details page
    public function viewOffer($offerId) {
        // Retrieve last search results from session (or cache)
        $offers = $_SESSION['last_transfer_offers'] ?? [];
        $offer = null;
        foreach ($offers as $o) {
            if ((string)($o['id'] ?? '') === (string)$offerId) {
                $offer = $o;
                break;
            }
        }
        $this->view('car/view_offer', ['offer' => $offer]);
    }

    public function payment() {
        if (empty($_SESSION['user'])) {
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/car/search';
            header('Location: ' . $base);
            exit;
        }
        // If offer_id is present in GET, set car_selected_offer
        if (isset($_GET['offer_id'])) {
            $offers = $_SESSION['last_transfer_offers'] ?? [];
            $offer = null;
            foreach ($offers as $o) {
                if ((string)($o['id'] ?? '') === (string)$_GET['offer_id']) {
                    $offer = $o;
                    break;
                }
            }
            if ($offer) {
                $_SESSION['car_selected_offer'] = $offer;
            } else {
                $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/car/search';
                header('Location: ' . $base);
                exit;
            }
        }
        if (empty($_SESSION['car_selected_offer'])) {
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/car/search';
            header('Location: ' . $base);
            exit;
        }
        $offer = $_SESSION['car_selected_offer'];
        $this->view('car/payment', ['offer' => $offer]);
    }
} 