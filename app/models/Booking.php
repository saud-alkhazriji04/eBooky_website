<?php
class Booking extends Model {
    public function create($data) {
        $stmt = $this->db->prepare('INSERT INTO bookings (user_id, type, status, booking_ref, details, airtable_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['user_id'],
            $data['type'],
            $data['status'],
            $data['booking_ref'],
            $data['details'],
            $data['airtable_id'] ?? null,
        ]);
        return $this->db->lastInsertId();
    }
    
    public function findByUser($userId, $type = null) {
        $sql = 'SELECT * FROM bookings WHERE user_id = ?';
        $params = [$userId];
        
        if ($type) {
            $sql .= ' AND type = ?';
            $params[] = $type;
        }
        
        $sql .= ' ORDER BY created_at DESC';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM bookings WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByBookingRef($bookingRef) {
        $stmt = $this->db->prepare('SELECT * FROM bookings WHERE booking_ref = ?');
        $stmt->execute([$bookingRef]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }
    
    public function cancel($id, $reason = null) {
        $this->db->beginTransaction();
        try {
            // Update booking status
            $stmt = $this->db->prepare('UPDATE bookings SET status = ? WHERE id = ?');
            $stmt->execute(['cancelled', $id]);
            
            // Get booking details
            $booking = $this->findById($id);
            
            // Add cancellation record
            $stmt = $this->db->prepare('INSERT INTO cancellations (booking_id, user_id, reason) VALUES (?, ?, ?)');
            $stmt->execute([$id, $booking['user_id'], $reason]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error cancelling booking: " . $e->getMessage());
            return false;
        }
    }
    
    public function getHotelBookings($userId) {
        return $this->findByUser($userId, 'hotel');
    }
    
    public function getFlightBookings($userId) {
        return $this->findByUser($userId, 'flight');
    }
    
    public function getCarBookings($userId) {
        return $this->findByUser($userId, 'car');
    }

    public function updateAirtableId($id, $airtable_id) {
        $stmt = $this->db->prepare('UPDATE bookings SET airtable_id = ? WHERE id = ?');
        $stmt->execute([$airtable_id, $id]);
    }
} 