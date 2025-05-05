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
    $stmt = $pdo->query("SELECT * FROM station ORDER BY station_id ASC");
    $stations = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching stations: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Station Management</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <h2>Available Stations</h2>

    <?php if (count($stations) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Station Name</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stations as $station): ?>
                    <tr>
                        <td><?php echo $station['station_id']; ?></td>
                        <td><?php echo htmlspecialchars($station['name']); ?></td>
                        <td><?php echo htmlspecialchars($station['location']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No stations found.</p>
    <?php endif; ?>
</body>
</html>
