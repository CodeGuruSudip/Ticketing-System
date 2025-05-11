<?php
session_start();
require_once('../../includes/config.php');
require_once('../../includes/functions.php');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
            if (!isset($_POST['confirm'])) {
                $_SESSION['delete_user_id'] = $_POST['user_id'];
                $_SESSION['delete_confirmation'] = true;
                header('Location: users.php');
                exit;
            } else {
                try {
                    $stmt = $pdo->prepare("DELETE FROM user WHERE user_id = ? AND is_admin = 0");
                    $stmt->execute([$_POST['user_id']]);
                    setFlash('success', 'User deleted successfully');
                    unset($_SESSION['delete_user_id']);
                    unset($_SESSION['delete_confirmation']);
                } catch (PDOException $e) {
                    setFlash('error', 'Error deleting user: ' . $e->getMessage());
                }
            }
        }
        
        if ($_POST['action'] === 'cancel_delete') {
            unset($_SESSION['delete_user_id']);
            unset($_SESSION['delete_confirmation']);
        }
    }
    header('Location: users.php');
    exit;
}

$searchQuery = '';
$whereClause = '';
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $whereClause = " WHERE email LIKE ? ";
    $params[] = "%$searchQuery%";
}

try {
    $query = "
        SELECT user_id, email, created_at, is_admin
        FROM user
        " . $whereClause . "
        ORDER BY created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #4b5563;
            --success-color: #16a34a;
            --danger-color: #dc2626;
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
        
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            background-color: #f9fafb;
            font-weight: 500;
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
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-primary {
            background-color: #dbeafe;
            color: var(--primary-color);
        }
        
        .badge-success {
            background-color: #dcfce7;
            color: var(--success-color);
        }
        
        .search-box {
            margin-bottom: 1.5rem;
        }
        
        .search-box form {
            display: flex;
            gap: 0.5rem;
        }
        
        .search-box input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
        }
        
        .flash {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.375rem;
        }
        
        .flash-success {
            background-color: #dcfce7;
            color: var(--success-color);
        }
        
        .flash-error {
            background-color: #fee2e2;
            color: var(--danger-color);
        }
        
        .confirmation-box {
            background-color: #fff3cd;
            color: #856404;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0.375rem;
            border: 1px solid #ffeeba;
        }
        
        .confirmation-box form {
            display: inline-block;
            margin-top: 0.5rem;
        }
        
        .confirmation-box .btn {
            margin-right: 0.5rem;
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
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="users.php" class="active">Users</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>User Management</h1>
            </div>
            
            <?php if ($msg = getFlash('success')): ?>
                <div class="flash flash-success"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            
            <?php if ($msg = getFlash('error')): ?>
                <div class="flash flash-error"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['delete_confirmation']) && isset($_SESSION['delete_user_id'])): 
                $userStmt = $pdo->prepare("SELECT email FROM user WHERE user_id = ?");
                $userStmt->execute([$_SESSION['delete_user_id']]);
                $userToDelete = $userStmt->fetch();
            ?>
                <div class="confirmation-box">
                    <p><strong>Confirm Deletion:</strong> Are you sure you want to delete the user "<?= htmlspecialchars($userToDelete['email']) ?>"?</p>
                    <p>This action cannot be undone.</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?= $_SESSION['delete_user_id'] ?>">
                        <input type="hidden" name="confirm" value="1">
                        <button type="submit" class="btn btn-danger">Yes, Delete User</button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="cancel_delete">
                        <button type="submit" class="btn btn-primary">Cancel</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <div class="search-box">
                <form method="GET" action="users.php">
                    <input type="text" name="search" id="userSearch" placeholder="Search users by email..." value="<?= htmlspecialchars($searchQuery) ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="users.php" class="btn btn-danger">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <?= empty($searchQuery) ? 'All Users' : 'Search Results' ?>
                    <?php if (!empty($searchQuery)): ?>
                        <small>(Showing results for: <?= htmlspecialchars($searchQuery) ?>)</small>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Registered On</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['user_id'] ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= $user['is_admin'] ? 'primary' : 'success' ?>">
                                        <?= $user['is_admin'] ? 'Admin' : 'User' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$user['is_admin']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No users found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
