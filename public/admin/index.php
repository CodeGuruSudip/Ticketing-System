<?php
session_start();
require_once('../../includes/config.php');
require_once('../../includes/functions.php');
requireAdmin();

try {

    $userStmt = $pdo->query("SELECT COUNT(*) as total FROM user WHERE is_admin = 0");
    $totalUsers = $userStmt->fetch()['total'];
    
    $activeStmt = $pdo->query("SELECT SUM(seat_count) as total FROM ticket WHERE status = 'booked' OR status IS NULL");
    $activeTickets = $activeStmt->fetch()['total'] ?? 0;
    
    $cancelledStmt = $pdo->query("SELECT SUM(seat_count) as total FROM ticket WHERE status = 'cancelled'");
    $cancelledTickets = $cancelledStmt->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Transit System</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #4b5563;
            --success-color: #16a34a;
            --danger-color: #dc2626;
            --warning-color: #f59e0b;
            --background-color: #f3f4f6;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #1e293b;
            color: white;
            padding: 1rem;
        }
        
        .sidebar-header {
            padding: 1rem 0;
            border-bottom: 1px solid #334155;
            margin-bottom: 1rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 0.75rem 1rem;
            color: #e2e8f0;
            text-decoration: none;
            border-radius: 0.375rem;
            transition: background-color 0.2s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: #334155;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card h3 {
            margin-top: 0;
            color: var(--secondary-color);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: 600;
            margin: 0.5rem 0;
        }
        
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="number"><?= $totalUsers ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Booked Tickets</h3>
                    <div class="number"><?= $activeTickets ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Cancelled Tickets</h3>
                    <div class="number"><?= $cancelledTickets ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
