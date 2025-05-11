<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/functions.php');
requireLogin();

try {
    $stmt = $pdo->prepare("
        SELECT t.*, s.departure_time, r.route_name,
               st1.name AS start_station, st2.name AS end_station
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Transit System</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --success-color: #16a34a;
            --danger-color: #dc2626;
            --background-color: #f3f4f6;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 2rem;
            background-color: var(--background-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .booking-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .booking-card:hover {
            transform: translateY(-2px);
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .route-name {
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-booked {
            background-color: #dcfce7;
            color: var(--success-color);
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: var(--danger-color);
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            margin-bottom: 0.5rem;
        }

        .detail-label {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .detail-value {
            font-weight: 500;
            color: #1f2937;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .no-bookings {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h1>
            <div class="actions">
                <a href="book.php" class="btn btn-primary">New Booking</a>
                <a href="my_bookings.php" class="btn btn-primary">Manage Bookings</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <h2>Your Bookings</h2>

        <?php if (empty($bookings)): ?>
            <div class="no-bookings">
                <p>No bookings found. Start by making a new booking!</p>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <span class="route-name"><?= htmlspecialchars($booking['route_name']) ?></span>
                        <span class="status-badge status-<?= $booking['status'] ?>">
                            <?= ucfirst($booking['status']) ?>
                        </span>
                    </div>
                    
                    <div class="details-grid">
                        <div class="detail-item">
                            <div class="detail-label">Route</div>
                            <div class="detail-value">
                                <?= htmlspecialchars($booking['start_station']) ?> → 
                                <?= htmlspecialchars($booking['end_station']) ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Departure</div>
                            <div class="detail-value">
                                <?= date('M j, Y H:i', strtotime($booking['departure_time'])) ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Seats</div>
                            <div class="detail-value">
                                <?= $booking['seat_count'] ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Booked On</div>
                            <div class="detail-value">
                                <?= date('M j, Y H:i', strtotime($booking['booking_time'])) ?>
                            </div>
                        </div>
                    </div>

                    <div class="actions">
                        <form method="POST" action="cancel.php">
                            <input type="hidden" name="ticket_id" value="<?= $booking['ticket_id'] ?>">
                            <button type="submit" class="btn btn-danger">Cancel Booking</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
