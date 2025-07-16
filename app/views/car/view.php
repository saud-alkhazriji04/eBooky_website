<h2>Transfer Booking Details</h2>
<?php if (empty($booking)): ?>
    <p>Booking not found.</p>
    <a href="/bookings">Back to My Trips</a>
<?php else: ?>
    <?php $details = json_decode($booking['details'], true); ?>
    <p><strong>Booking Reference:</strong> <?= htmlspecialchars($details['booking_ref'] ?? '') ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($details['status'] ?? '') ?></p>
    <p><strong>Offer ID:</strong> <?= htmlspecialchars($details['offer_id'] ?? '') ?></p>
    <p><strong>Booked At:</strong> <?= htmlspecialchars($details['created_at'] ?? '') ?></p>
    <?php if ($details['status'] !== 'cancelled'): ?>
        <form method="POST" action="/car/cancel/<?= $booking['id'] ?>">
            <button type="submit">Cancel Booking</button>
        </form>
    <?php else: ?>
        <p>This booking has been cancelled.</p>
    <?php endif; ?>
    <a href="/bookings">Back to My Trips</a>
<?php endif; ?> 