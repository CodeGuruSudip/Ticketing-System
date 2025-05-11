<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

try {
    $stmt = $pdo->prepare("
        SELECT 
            t.ticket_id,
            t.seat_count,
            t.booking_time,
            t.status,
            s.departure_time,
            r.route_name,
            st1.name AS start_station,
            st2.name AS end_station
        FROM ticket t
        INNER JOIN schedule s ON t.schedule_id = s.schedule_id
        INNER JOIN route r ON s.route_id = r.route_id
        INNER JOIN station st1 ON r.start_station_id = st1.station_id
        INNER JOIN station st2 ON r.end_station_id = st2.station_id
        WHERE t.user_id = ?
        ORDER BY t.booking_time DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching bookings: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .booking-card {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .booked { background: #e6ffe6; color: #2a662a; }
        .cancelled { background: #ffe6e6; color: #662a2a; }
        .cancel-btn {
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 1rem;
        }
        .flash {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Bookings</h1>
        <a href="dashboard.php">← Back to Dashboard</a>

        <?php if ($msg = getFlash('success')): ?>
            <div class="flash success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if ($msg = getFlash('error')): ?>
            <div class="flash error"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <p>No bookings found.</p>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <h3><?= htmlspecialchars($booking['route_name']) ?></h3>
                    <p>
                        <?= htmlspecialchars($booking['start_station']) ?> → 
                        <?= htmlspecialchars($booking['end_station']) ?>
                    </p>
                    <p>Departure: <?= date('M j, Y H:i', strtotime($booking['departure_time'])) ?></p>
                    <p>Seats: <?= $booking['seat_count'] ?></p>
                    <p>Booked on: <?= date('M j, Y H:i', strtotime($booking['booking_time'])) ?></p>
                    <span class="status <?= $booking['status'] ?>">
                        <?= ucfirst($booking['status']) ?>
                    </span>

                    <?php if ($booking['status'] === 'booked'): ?>
                        <form method="POST" action="cancel.php">
                            <input type="hidden" name="ticket_id" 
                                value="<?= $booking['ticket_id'] ?>">
                            <button type="submit" class="cancel-btn">
                                Cancel Booking
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
