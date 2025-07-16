<?php
class User extends Model {
    public function findByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function create($name, $email, $password, $country) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password, country) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$name, $email, $hashed, $country]);
    }
    public function validateRegistration($name, $email, $password, $country) {
        $errors = [];
        if (!$name || strlen($name) < 2) $errors[] = 'Name is required and must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (!$password || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if (!$country) $errors[] = 'Country is required.';
        return $errors;
    }
    public function validateLogin($email, $password) {
        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (!$password) $errors[] = 'Password is required.';
        return $errors;
    }
    public function login($email, $password) {
        $user = $this->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
} 