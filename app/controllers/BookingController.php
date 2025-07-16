<?php
class BookingController {
    public function index() {
        if (empty($_SESSION['user'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/auth/login');
            exit;
        }
        $db = require __DIR__ . '/../db.php';
        $stmt = $db->prepare('SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$_SESSION['user']['id']]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../views/bookings/index.php';
    }
    public function cancel() {
        if (empty($_SESSION['user']) || empty($_POST['booking_id'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/bookings');
            exit;
        }
        $db = require __DIR__ . '/../db.php';
        $stmt = $db->prepare('UPDATE bookings SET status = ? WHERE id = ? AND user_id = ?');
        $stmt->execute(['cancelled', $_POST['booking_id'], $_SESSION['user']['id']]);
        // Optionally, add to cancellations table
        $stmt2 = $db->prepare('INSERT INTO cancellations (booking_id, user_id, reason) VALUES (?, ?, ?)');
        $stmt2->execute([$_POST['booking_id'], $_SESSION['user']['id'], $_POST['reason'] ?? 'User cancelled']);
        // Airtable integration for cancellation
        $db = require __DIR__ . '/../db.php';
        $stmt = $db->prepare('SELECT * FROM bookings WHERE id = ?');
        $stmt->execute([$_POST['booking_id']]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($booking) {
            $details = json_decode($booking['details'], true);
            $config = require __DIR__ . '/../config.php';
            require_once __DIR__ . '/../services/AirtableService.php';
            $airtable = new AirtableService(
                $config['airtable_token'],
                $config['airtable_base']
            );
            $table = '';
            if ($booking['type'] === 'flight') $table = 'FlightBookings';
            if ($booking['type'] === 'hotel') $table = 'HotelBookings';
            if ($booking['type'] === 'car') $table = 'CarBookings';
            $airtable_id = $booking['airtable_id'] ?? null;
            if ($airtable_id) {
                $airtable->updateBookingStatus($table, $airtable_id, 'Cancelled');
            }
        }
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/bookings');
        exit;
    }
    
    public function viewSeatmap() {
        if (empty($_SESSION['user']) || empty($_GET['booking_id'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/bookings');
            exit;
        }
        
        $db = require __DIR__ . '/../db.php';
        $stmt = $db->prepare('SELECT * FROM bookings WHERE id = ? AND user_id = ?');
        $stmt->execute([$_GET['booking_id'], $_SESSION['user']['id']]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking || $booking['type'] !== 'flight') {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/bookings');
            exit;
        }
        
        $details = json_decode($booking['details'], true);
        
        // Check if seats were selected
        if (empty($details['selected_seats'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/bookings');
            exit;
        }
        
        // Store booking data for the view
        $_SESSION['view_booking'] = $booking;
        $_SESSION['view_booking_details'] = $details;
        
        require __DIR__ . '/../views/bookings/view_seatmap.php';
    }

    public function viewHotel() {
        if (empty($_SESSION['user']) || empty($_GET['booking_id'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/bookings');
            exit;
        }
        $db = require __DIR__ . '/../db.php';
        $stmt = $db->prepare('SELECT * FROM bookings WHERE id = ? AND user_id = ?');
        $stmt->execute([$_GET['booking_id'], $_SESSION['user']['id']]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$booking || $booking['type'] !== 'hotel') {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/bookings');
            exit;
        }
        $details = json_decode($booking['details'], true);
        require __DIR__ . '/../views/bookings/view_hotel.php';
    }

    public function viewCar() {
        if (empty($_SESSION['user']) || empty($_GET['booking_id'])) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/bookings');
            exit;
        }
        $db = require __DIR__ . '/../db.php';
        $stmt = $db->prepare('SELECT * FROM bookings WHERE id = ? AND user_id = ?');
        $stmt->execute([$_GET['booking_id'], $_SESSION['user']['id']]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$booking || $booking['type'] !== 'car') {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/bookings');
            exit;
        }
        $details = json_decode($booking['details'], true);
        require __DIR__ . '/../views/bookings/view_car.php';
    }
} 