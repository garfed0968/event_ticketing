<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle User Deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    // Prevent self-deletion
    if ($delete_id == $_SESSION['user_id']) {
        $msg = "Cannot delete your own admin account.";
        $msg_type = "danger";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $msg = "User deleted successfully.";
            $msg_type = "success";
        } else {
            $msg = "Error deleting user: " . $conn->error;
            $msg_type = "danger";
        }
        $stmt->close();
    }
}

// Fetch Stats
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$events_count = $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'];
$tickets_count = $conn->query("SELECT COUNT(*) as count FROM tickets")->fetch_assoc()['count'];

// Fetch All Users
$users_result = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
?>

<div class="container py-5">
    <?php if (isset($msg)): ?>
        <div class="alert alert-<?php echo $msg_type; ?> animate-fade-in"><?php echo $msg; ?></div>
    <?php endif; ?>

    <h2 class="fw-bold text-white mb-4">Admin Dashboard</h2>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card p-4 text-center">
                <i class="bi bi-people h1 text-info"></i>
                <h3 class="text-white mt-2"><?php echo $users_count; ?></h3>
                <p class="text-white-50 mb-0">Total Users</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 text-center">
                <i class="bi bi-calendar-event h1 text-warning"></i>
                <h3 class="text-white mt-2"><?php echo $events_count; ?></h3>
                <p class="text-white-50 mb-0">Total Events</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 text-center">
                <i class="bi bi-ticket-perforated h1 text-success"></i>
                <h3 class="text-white mt-2"><?php echo $tickets_count; ?></h3>
                <p class="text-white-50 mb-0">Tickets Sold/Pending</p>
            </div>
        </div>
    </div>

    <!-- User Management Table -->
    <div class="glass-card p-4">
        <h4 class="mb-3 border-bottom pb-2 border-secondary text-white"><i class="bi bi-people-fill me-2"></i>User Management</h4>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" style="background: transparent;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php 
                                $badge = 'bg-secondary';
                                if($user['role'] == 'admin') $badge = 'bg-danger';
                                if($user['role'] == 'organizer') $badge = 'bg-info';
                            ?>
                            <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($user['role']); ?></span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if($user['role'] != 'admin'): ?>
                                <a href="admin_dashboard.php?delete_id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure? This will delete all their events/tickets.');"><i class="bi bi-trash"></i> Delete</a>
                            <?php else: ?>
                                <span class="text-muted small">Protected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
