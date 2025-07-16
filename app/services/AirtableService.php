<?php
class AirtableService {
    private $token;
    private $baseId;
    private $tableName;

    public function __construct($token, $baseId, $tableName = 'registration') {
        $this->token = $token;
        $this->baseId = $baseId;
        $this->tableName = $tableName;
    }

    public function addRegistration($name, $email) {
        $url = "https://api.airtable.com/v0/{$this->baseId}/{$this->tableName}";
        $data = [
            'fields' => [
                'Name' => $name,
                'Email' => $email
            ]
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            // Optionally log $response for debugging
            return false;
        }
    }

    public function addFlightBooking($fields) {
        return $this->addRow('FlightBookings', $fields);
    }
    public function addHotelBooking($fields) {
        return $this->addRow('HotelBookings', $fields);
    }
    public function addCarBooking($fields) {
        return $this->addRow('CarBookings', $fields);
    }
    public function updateBookingStatus($table, $recordId, $status) {
        $url = "https://api.airtable.com/v0/{$this->baseId}/{$table}/{$recordId}";
        $data = [ 'fields' => [ 'Status' => $status ] ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode >= 200 && $httpCode < 300;
    }
    private function addRow($table, $fields) {
        $url = "https://api.airtable.com/v0/{$this->baseId}/{$table}";
        $data = [ 'fields' => $fields ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = json_decode($response, true);
        curl_close($ch);
        if ($httpCode >= 200 && $httpCode < 300 && isset($result['id'])) {
            return $result['id'];
        }
        return false;
    }
} 