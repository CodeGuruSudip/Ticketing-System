<?php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id'])) {
    try {
        $pdo->beginTransaction();
        
  
        $checkStmt = $pdo->prepare("
            SELECT ticket_id, seat_count, schedule_id, status
            FROM ticket 
            WHERE ticket_id = ? 
            AND user_id = ?
        ");
        $checkStmt->execute([
            $_POST['ticket_id'],
            $_SESSION['user_id']
        ]);
        $ticket = $checkStmt->fetch();

        if (!$ticket) {
            
            setFlash('error', 'Ticket not found or does not belong to you');
            $pdo->rollBack();
        } else if ($ticket['status'] === 'cancelled') {
        
            setFlash('info', 'This ticket has already been cancelled');
            $pdo->rollBack();
        } else {
            
            $updateTicket = $pdo->prepare("
                UPDATE ticket 
                SET status = 'cancelled' 
                WHERE ticket_id = ?
            ");
            $updateTicket->execute([$_POST['ticket_id']]);

            $updateSchedule = $pdo->prepare("
                UPDATE schedule 
                SET available_seats = available_seats + ? 
                WHERE schedule_id = ?
            ");
            $updateSchedule->execute([
                $ticket['seat_count'],
                $ticket['schedule_id']
            ]);

            $pdo->commit();
            setFlash('success', 'Ticket cancelled successfully!');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('error', 'Cancellation failed: ' . $e->getMessage());
    }
}

header("Location: dashboard.php");
exit;
?>
