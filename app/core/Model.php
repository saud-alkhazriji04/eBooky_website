<?php
class Model {
    protected $db;
    public function __construct($db) {
        $this->db = $db;
    }
    public static function getPDO() {
        $host = 'localhost';
        $dbname = 'Your_Database_Name';
        $user = 'Your_Database_User';
        $pass = 'Your_Database_Password';
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
} 