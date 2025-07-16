<?php
class AuthController {
    public function showLogin() {
        require_once __DIR__ . '/../views/login.php';
    }
    public function showRegister() {
        require_once __DIR__ . '/../views/register.php';
    }
    public function login() {
        require_once __DIR__ . '/../models/User.php';
        $db = require __DIR__ . '/../db.php';
        $userModel = new User($db);
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $errors = $userModel->validateLogin($email, $password);
        if (empty($errors)) {
            $user = $userModel->login($email, $password);
            if ($user) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'country' => $user['country'] ?? ''
                ];
                $home = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
                header('Location: ' . $home);
                exit;
            } else {
                $errors[] = 'Invalid email or password.';
            }
        }
        $old = ['email' => $email];
        require __DIR__ . '/../views/login.php';
    }
    public function register() {
        require_once __DIR__ . '/../models/User.php';
        $db = require __DIR__ . '/../db.php';
        $userModel = new User($db);
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $country = $_POST['country'] ?? '';
        $errors = $userModel->validateRegistration($name, $email, $password, $country);
        if (empty($errors)) {
            if ($userModel->findByEmail($email)) {
                $errors[] = 'Email already registered.';
            } else {
                $userModel->create($name, $email, $password, $country);
                $user = $userModel->findByEmail($email);
                // Airtable integration
                $config = require __DIR__ . '/../config.php';
                require_once __DIR__ . '/../services/AirtableService.php';
                $airtable = new AirtableService(
                    $config['airtable_token'],
                    $config['airtable_base'],
                    $config['airtable_table']
                );
                $airtable->addRegistration($name, $email);
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'country' => $user['country'] ?? ''
                ];
                $home = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
                header('Location: ' . $home);
                exit;
            }
        }
        $old = ['name' => $name, 'email' => $email, 'country' => $country];
        require __DIR__ . '/../views/register.php';
    }
    public function logout() {
        session_destroy();
        $home = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
        header('Location: ' . $home);
        exit;
    }
} 