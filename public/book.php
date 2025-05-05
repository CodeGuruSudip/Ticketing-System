<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../includes/config.php');
require_once('../includes/functions.php');
requireLogin();

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Rest of your existing booking.php code...
?>
<?php
//session_start();
require_once('../includes/config.php');
require_once('../includes/functions.php');
requireLogin();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize variables
$schedules = [];
$error = '';

// Database connection check
try {
    $stmt = $pdo->query("
        SELECT 
            s.schedule_id,
            s.departure_time,
            s.available_seats,
            r.route_name,
            st1.name AS start_station,
            st2.name AS end_station
        FROM schedule s
        LEFT JOIN route r ON s.route_id = r.route_id
        LEFT JOIN station st1 ON r.start_station_id = st1.station_id
        LEFT JOIN station st2 ON r.end_station_id = st2.station_id
        WHERE s.available_seats > 0
        AND s.departure_time > NOW()
        ORDER BY s.departure_time ASC
    ");
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Handle booking form submission
// In booking.php's POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_ticket'])) {
    try {
        // Validate inputs
        $scheduleId = filter_var($_POST['schedule_id'], FILTER_VALIDATE_INT);
        $seatCount = filter_var($_POST['seat_count'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        if (!$scheduleId || !$seatCount) {
            throw new Exception("Invalid booking data received");
        }

        $pdo->beginTransaction();

        // 1. Check Availability
        $stmt = $pdo->prepare("
            SELECT available_seats 
            FROM schedule 
            WHERE schedule_id = ? 
            FOR UPDATE
        ");
        $stmt->execute([$scheduleId]);
        $available = $stmt->fetchColumn();

        if ($available < $seatCount) {
            throw new Exception("Only $available seats remaining");
        }

        // 2. Create Ticket
        $stmt = $pdo->prepare("
            INSERT INTO ticket (user_id, schedule_id, seat_count)
            VALUES (?, ?, ?)
        ");
        if (!$stmt->execute([$_SESSION['user_id'], $scheduleId, $seatCount])) {
            throw new Exception("Failed to create ticket");
        }

        // 3. Update Seats
        $stmt = $pdo->prepare("
            UPDATE schedule 
            SET available_seats = available_seats - ? 
            WHERE schedule_id = ?
        ");
        if (!$stmt->execute([$seatCount, $scheduleId])) {
            throw new Exception("Failed to update seat count");
        }

        $pdo->commit();
        setFlash('success', 'Booking confirmed!');
        header("Location: my_bookings.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', $e->getMessage());
        header("Location: booking.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Tickets - Transit System</title>
    <style>
        /* Modern styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 2rem;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .schedule-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-group {
            margin: 1rem 0;
        }

        input[type="number"] {
            padding: 0.5rem;
            width: 100px;
        }

        button {
            background: #2563eb;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        button:hover {
            opacity: 0.9;
        }

        .flash {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }

        .flash-success {
            background: #dcfce7;
            color: #166534;
        }

        .flash-error {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Book Tickets</h1>
        <a href="dashboard.php">← Back to Dashboard</a>

        <?php if ($msg = getFlash('success')): ?>
            <div class="flash flash-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if ($msg = getFlash('error')): ?>
            <div class="flash flash-error"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($schedules)): ?>
            <div class="flash flash-info">
                No available schedules at the moment. Please check back later.
            </div>
        <?php else: ?>
            <div class="schedule-list">
                <?php foreach ($schedules as $schedule): ?>
                    <div class="schedule-card">
                        <h3><?= htmlspecialchars($schedule['route_name']) ?></h3>
                        <p>
                            <?= htmlspecialchars($schedule['start_station']) ?> to 
                            <?= htmlspecialchars($schedule['end_station']) ?><br>
                            Departure: <?= date('M j, Y H:i', strtotime($schedule['departure_time'])) ?><br>
                            Available Seats: <?= $schedule['available_seats'] ?>
                        </p>
                        <form method="POST">
                            <input type="hidden" name="schedule_id" 
                                value="<?= $schedule['schedule_id'] ?>">
                            <div class="form-group">
                                <label>
                                    Number of Seats:
                                    <input type="number" name="seat_count" 
                                        min="1" max="<?= $schedule['available_seats'] ?>" 
                                        value="1" required>
                                </label>
                            </div>
                            <button type="submit" name="book_ticket">Book Now</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>