<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: login.php");
    exit;
}

$organizer_id = $_SESSION['user_id'];

// Fetch My Events
$events_stmt = $conn->prepare("SELECT * FROM events WHERE organizer_id = ? ORDER BY date ASC");
$events_stmt->bind_param("i", $organizer_id);
$events_stmt->execute();
$events_result = $events_stmt->get_result();

// Fetch Pending Tickets for My Events
$tickets_stmt = $conn->prepare("
    SELECT t.id, t.created_at, u.username, e.title as event_title, t.payment_method, t.payment_proof_path 
    FROM tickets t 
    JOIN events e ON t.event_id = e.id 
    JOIN users u ON t.user_id = u.id 
    WHERE e.organizer_id = ? AND t.status = 'pending'
    ORDER BY t.created_at ASC
");
$tickets_stmt->bind_param("i", $organizer_id);
$tickets_stmt->execute();
$tickets_result = $tickets_stmt->get_result();
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white">Organizer Dashboard</h2>
        <a href="create_event.php" class="btn btn-premium"><i class="bi bi-plus-lg me-2"></i>Create New Event</a>
    </div>

    <!-- Pending Approvals Section -->
    <?php if ($tickets_result->num_rows > 0): ?>
    <div class="glass-card p-4 mb-5 animate-fade-in">
        <h4 class="mb-3 border-bottom pb-2 border-secondary"><i class="bi bi-ticket-perforated me-2"></i>Pending Ticket Approvals</h4>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" style="background: transparent;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Event</th>
                        <th>User</th>
                        <th>Payment</th>
                        <th>Proof</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($ticket = $tickets_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($ticket['event_title']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['username']); ?></td>
                        <td><?php echo htmlspecialchars($ticket['payment_method']); ?></td>
                        <td>
                            <?php if($ticket['payment_proof_path']): ?>
                                <a href="<?php echo htmlspecialchars($ticket['payment_proof_path']); ?>" target="_blank" class="text-info text-decoration-none">
                                    <i class="bi bi-file-earmark-image me-1"></i>View
                                </a>
                            <?php else: ?>
                                <span class="text-muted">None</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="ticket_action.php?id=<?php echo $ticket['id']; ?>&action=approve" class="btn btn-success btn-sm me-1"><i class="bi bi-check-lg"></i></a>
                            <a href="ticket_action.php?id=<?php echo $ticket['id']; ?>&action=decline" class="btn btn-danger btn-sm"><i class="bi bi-x-lg"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>

    <?php
    // Fetch Refund Requests
    $refunds_stmt = $conn->prepare("
        SELECT t.id, t.created_at, u.username, e.title as event_title, t.payment_method 
        FROM tickets t 
        JOIN events e ON t.event_id = e.id 
        JOIN users u ON t.user_id = u.id 
        WHERE e.organizer_id = ? AND t.status = 'refund_requested'
        ORDER BY t.created_at ASC
    ");
    $refunds_stmt->bind_param("i", $organizer_id);
    $refunds_stmt->execute();
    $refunds_result = $refunds_stmt->get_result();
    ?>

    <!-- Refund Requests Section -->
    <?php if ($refunds_result->num_rows > 0): ?>
    <div class="glass-card p-4 mb-5 animate-fade-in">
        <h4 class="mb-3 border-bottom pb-2 border-secondary text-warning"><i class="bi bi-arrow-counterclockwise me-2"></i>Refund Requests</h4>
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0" style="background: transparent;">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>User</th>
                        <th>Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($refund = $refunds_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($refund['event_title']); ?></td>
                        <td><?php echo htmlspecialchars($refund['username']); ?></td>
                        <td><?php echo htmlspecialchars($refund['payment_method']); ?></td>
                        <td>
                            <form action="process_refund.php" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="ticket_id" value="<?php echo $refund['id']; ?>">
                                <input type="hidden" name="action" value="refund">
                                <input type="file" name="refund_proof" class="form-control form-control-sm" accept="image/*,.pdf" required title="Upload Refund Receipt">
                                <button type="submit" class="btn btn-warning btn-sm text-nowrap" onclick="return confirm('Confirm refund with this receipt?');">Confirm</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- My Events Section -->
    <h4 class="mb-3 text-white"><i class="bi bi-calendar-event me-2"></i>My Events</h4>
    <div class="row g-4">
        <?php if ($events_result->num_rows > 0): ?>
            <?php while($event = $events_result->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="glass-card h-100 event-card p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="event-date-badge"><?php echo date('M d, Y', strtotime($event['date'])); ?></span>
                        <small class="text-muted"><?php echo date('h:i A', strtotime($event['time'])); ?></small>
                    </div>
                    <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($event['title']); ?></h5>
                    <p class="mb-1 text-white-50"><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($event['location']); ?></p>
                    <p class="mb-2 text-white-50"><i class="bi bi-tag me-2"></i><?php echo htmlspecialchars($event['theme']); ?></p>
                    <div class="mt-3 pt-2 border-top border-secondary d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-success">
                            <?php echo ($event['price'] > 0) ? "ETB " . number_format($event['price'], 2) : "Free"; ?>
                        </span>
                        <div>
                            <small class="text-muted d-block mb-1 text-center">Created: <?php echo date('M d', strtotime($event['created_at'])); ?></small>
                            <div class="d-flex gap-1">
                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-info btn-sm flex-fill"><i class="bi bi-pencil-square me-1"></i>Edit</a>
                                <form action="delete_event.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event? This will also remove all associated tickets and saved items.');">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm h-100"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info glass-card bg-transparent text-white border-0" role="alert">
                    You haven't created any events yet. Click the button above to get started!
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$events_stmt->close();
$tickets_stmt->close();
include 'footer.php'; 
?>
