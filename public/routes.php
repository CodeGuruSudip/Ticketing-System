<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once('../includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT r.route_id, r.route_name, s1.name AS start_station, s2.name AS end_station
        FROM route r
        LEFT JOIN station s1 ON r.start_station_id = s1.station_id
        LEFT JOIN station s2 ON r.end_station_id = s2.station_id
    ");
    $routes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching routes: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Route Management</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <h2>Available Routes</h2>

    <?php if (count($routes) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Route Name</th>
                    <th>Start Station</th>
                    <th>End Station</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routes as $route): ?>
                    <tr>
                        <td><?php echo $route['route_id']; ?></td>
                        <td><?php echo htmlspecialchars($route['route_name']); ?></td>
                        <td><?php echo htmlspecialchars($route['start_station']); ?></td>
                        <td><?php echo htmlspecialchars($route['end_station']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No routes found.</p>
    <?php endif; ?>
</body>
</html>
