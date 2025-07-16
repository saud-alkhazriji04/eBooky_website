<?php
session_start();

require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/View.php';

// Simple autoloader
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
        __DIR__ . '/../app/services/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Improved router for subfolder support
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = trim($basePath, '/');
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');
if ($basePath && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
    $uri = trim($uri, '/');
}




switch ($uri) {
    case '':
        (new HomeController())->index();
        break;
    case 'auth/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController())->login();
        } else {
            (new AuthController())->showLogin();
        }
        break;
    case 'auth/register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new AuthController())->register();
        } else {
            (new AuthController())->showRegister();
        }
        break;
    case 'auth/logout':
        (new AuthController())->logout();
        break;
    case 'flights/search':
        (new FlightController())->search();
        break;
    case 'flights/book':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new FlightController())->book();
        } else {
            echo '404 Not Found';
        }
        break;
    case 'flights/seatmap':
        (new FlightController())->seatmap();
        break;
    case 'flights/payment':
        (new FlightController())->payment();
        break;
    case 'flights/charge':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new FlightController())->charge();
        } else {
            echo '404 Not Found';
        }
        break;
    case 'flights/confirmation':
        (new FlightController())->confirmation();
        break;
    case 'bookings':
        (new BookingController())->index();
        break;
    case 'bookings/cancel':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new BookingController())->cancel();
        } else {
            echo '404 Not Found';
        }
        break;
    case 'bookings/view-seatmap':
        (new BookingController())->viewSeatmap();
        break;
    case 'bookings/view-hotel':
        (new BookingController())->viewHotel();
        break;
    case 'bookings/view-car':
        (new BookingController())->viewCar();
        break;
    // HOTEL ROUTES
    case 'hotels/search':
        (new HotelController())->search();
        break;
    case 'hotels/book':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new HotelController())->book();
        } else {
            echo '404 Not Found';
        }
        break;
    case 'hotels/payment':
        (new HotelController())->payment();
        break;
    case 'hotels/charge':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new HotelController())->charge();
        } else {
            echo '404 Not Found';
        }
        break;
    case 'hotels/confirmation':
        (new HotelController())->confirmation();
        break;
    case 'hotels/autocomplete':
        (new HotelController())->autocomplete();
        break;
    case 'hotels/ratings':
        (new HotelController())->ratings();
        break;
    case 'hotels/view':
        (new HotelController())->view();
        break;
    // CAR/TRANSFER ROUTES
    case 'car/search':
        (new CarController())->search();
        break;
    case 'car/book':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new CarController())->book();
        } else {
            echo '404 Not Found';
        }
        break;
    case 'car/payment':
        (new CarController())->payment();
        break;
    case 'car/confirmation':
        (new CarController())->confirmation();
        break;
    case (preg_match('/^car\/view\/(.+)$/', $uri, $matches) ? true : false):
        (new CarController())->viewOffer($matches[1]);
        break;
    case (preg_match('/^car\/cancel\/(\d+)$/', $uri, $matches) ? true : false):
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new CarController())->cancel($matches[1]);
        } else {
            echo '404 Not Found';
        }
        break;
    case 'flights':
        header('Location: flights/search');
        exit;
    default:
        echo '404 Not Found';
        break;
} 

