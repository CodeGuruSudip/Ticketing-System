<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/functions.php');
requireLogin();
requireAdmin(); // Only admin can access reports

// Date filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

try {
    // Bookings report
    $bookingsStmt = $pdo->prepare("
        SELECT t.*, u.user_name, r.route_name, s.departure_time
        FROM ticket t
        JOIN user u ON t.user_id = u.user_id
        JOIN schedule s ON t.schedule_id = s.schedule_id
        JOIN route r ON s.route_id = r.route_id
        WHERE t.booking_time BETWEEN ? AND ?
    ");
    $bookingsStmt->execute([$startDate, $endDate]);
    $bookings = $bookingsStmt->fetchAll();

    // Revenue report
    $revenueStmt = $pdo->prepare("
        SELECT 
            SUM(t.seat_count * 10) AS total_revenue, -- Assuming $10 per seat
            COUNT(*) AS total_bookings,
            AVG(t.seat_count) AS avg_seats
        FROM ticket t
        WHERE t.booking_time BETWEEN ? AND ?
    ");
    $revenueStmt->execute([$startDate, $endDate]);
    $revenue = $revenueStmt->fetch();

} catch (PDOException $e) {
    die("Error generating reports: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        .filter-box { background: #f4f4f4; padding: 20px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>System Reports</h2>
        
        <!-- Date Filter -->
        <div class="filter-box">
            <form method="GET">
                <label>Start Date:
                    <input type="date" name="start_date" value="<?= $startDate ?>">
                </label>
                <label>End Date:
                    <input type="date" name="end_date" value="<?= $endDate ?>">
                </label>
                <button type="submit">Filter</button>
            </form>
        </div>

        <!-- Revenue Summary -->
        <h3>Revenue Summary</h3>
        <p>Total Revenue: $<?= number_format($revenue['total_revenue'] ?? 0, 2) ?></p>
        <p>Total Bookings: <?= $revenue['total_bookings'] ?></p>
        <p>Average Seats per Booking: <?= number_format($revenue['avg_seats'] ?? 0, 1) ?></p>

        <!-- Detailed Bookings -->
        <h3>Booking Details</h3>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Route</th>
                    <th>Departure</th>
                    <th>Seats</th>
                    <th>Booking Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['user_name']) ?></td>
                        <td><?= htmlspecialchars($booking['route_name']) ?></td>
                        <td><?= date('M j, Y H:i', strtotime($booking['departure_time'])) ?></td>
                        <td><?= $booking['seat_count'] ?></td>
                        <td><?= date('M j, Y H:i', strtotime($booking['booking_time'])) ?></td>
                        <td><?= $booking['status'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>