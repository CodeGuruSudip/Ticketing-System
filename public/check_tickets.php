<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/functions.php');
requireLogin();

try {
    $stmt = $pdo->prepare("
        SELECT ticket_id, seat_count, booking_time, status
        FROM ticket
        WHERE user_id = ?
        ORDER BY booking_time DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $tickets = $stmt->fetchAll();
    
    echo "<h1>Ticket Status Check</h1>";
    
    if (empty($tickets)) {
        echo "<p>No tickets found for your account.</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Ticket ID</th><th>Seats</th><th>Booking Time</th><th>Status</th></tr>";
        
        foreach ($tickets as $ticket) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($ticket['ticket_id']) . "</td>";
            echo "<td>" . htmlspecialchars($ticket['seat_count']) . "</td>";
            echo "<td>" . htmlspecialchars($ticket['booking_time']) . "</td>";
            echo "<td>" . htmlspecialchars($ticket['status']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        $booked = 0;
        $cancelled = 0;
        $other = 0;
        
        foreach ($tickets as $ticket) {
            if ($ticket['status'] === 'booked') {
                $booked++;
            } elseif ($ticket['status'] === 'cancelled') {
                $cancelled++;
            } else {
                $other++;
            }
        }
        
        echo "<p>Summary: $booked booked, $cancelled cancelled, $other other status</p>";
    }
    
} catch (PDOException $e) {
    die("Error fetching tickets: " . $e->getMessage());
}
?>
