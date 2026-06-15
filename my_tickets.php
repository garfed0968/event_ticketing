<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get My Tickets
$tickets_stmt = $conn->prepare("
    SELECT t.id, t.status, t.payment_method, t.created_at, t.refund_proof_path, e.title, e.date, e.time, e.location 
    FROM tickets t 
    JOIN events e ON t.event_id = e.id 
    WHERE t.user_id = ? 
    ORDER BY t.created_at DESC
");
$tickets_stmt->bind_param("i", $user_id);
$tickets_stmt->execute();
$tickets_result = $tickets_stmt->get_result();

// Get Saved Events
$saved_stmt = $conn->prepare("
    SELECT e.* 
    FROM saved_events s 
    JOIN events e ON s.event_id = e.id 
    WHERE s.user_id = ? 
    ORDER BY e.date ASC
");
$saved_stmt->bind_param("i", $user_id);
$saved_stmt->execute();
$saved_result = $saved_stmt->get_result();

// Notification Logic: Check if any saved event or ticket event is TOMORROW
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$notifications = [];

// Re-query for notifications (simplest way to catch both saved and purchased)
$notif_sql = "
    SELECT e.title, 'ticket' as type FROM tickets t JOIN events e ON t.event_id = e.id WHERE t.user_id = $user_id AND e.date = '$tomorrow'
    UNION
    SELECT e.title, 'saved' as type FROM saved_events s JOIN events e ON s.event_id = e.id WHERE s.user_id = $user_id AND e.date = '$tomorrow'
";
$notif_res = $conn->query($notif_sql);
if ($notif_res->num_rows > 0) {
    while($row = $notif_res->fetch_assoc()) {
        $notifications[] = "Reminder: You have an upcoming " . $row['type'] . " event '" . $row['title'] . "' tomorrow!";
    }
}
?>

<div class="container py-5">
    
    <!-- Notification Area -->
    <?php if (!empty($notifications)): ?>
        <?php foreach($notifications as $notif): ?>
        <div class="alert alert-warning animate-fade-in border-0 shadow" role="alert">
            <i class="bi bi-bell-fill me-2"></i> <?php echo $notif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 shadow"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>

    <h2 class="fw-bold text-white mb-4">My Dashboard</h2>

    <div class="row g-5">
        <!-- My Tickets Column -->
        <div class="col-lg-8">
            <div class="glass-card p-4 h-100">
                <h4 class="mb-4 text-white border-bottom border-secondary pb-2"><i class="bi bi-ticket-perforated-fill me-2"></i>My Tickets</h4>
                
                <?php if ($tickets_result->num_rows > 0): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php while($ticket = $tickets_result->fetch_assoc()): ?>
                            <div class="p-3 rounded border border-secondary" style="background: rgba(255,255,255,0.05);">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold text-white mb-0"><?php echo htmlspecialchars($ticket['title']); ?></h5>
                                    <?php 
                                        $statusClass = 'bg-warning text-dark';
                                        if ($ticket['status'] == 'approved') $statusClass = 'bg-success';
                                        if ($ticket['status'] == 'declined') $statusClass = 'bg-danger';
                                        if ($ticket['status'] == 'refund_requested') $statusClass = 'bg-info text-dark';
                                        if ($ticket['status'] == 'refunded') $statusClass = 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?> rounded-pill">
                                        <?php echo str_replace('_', ' ', ucfirst($ticket['status'])); ?>
                                    </span>
                                </div>
                                <div class="text-white-50 small mb-2">
                                    <i class="bi bi-calendar3 me-2"></i><?php echo date('M d, Y', strtotime($ticket['date'])); ?> at <?php echo date('h:i A', strtotime($ticket['time'])); ?><br>
                                    <i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($ticket['location']); ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 border-top border-secondary pt-2">
                                    <small class="text-white-50">Method: <?php echo $ticket['payment_method']; ?></small>
                                    <div class="d-flex gap-2">
                                        <?php if ($ticket['status'] == 'approved'): ?>
                                            <?php if ($ticket['payment_method'] !== 'Free'): ?>
                                                <a href="ticket_action.php?id=<?php echo $ticket['id']; ?>&action=request_refund" class="btn btn-sm btn-outline-warning" onclick="return confirm('Are you sure you want to request a refund?');">Request Refund</a>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-success"><i class="bi bi-download me-1"></i>Ticket</button>
                                        <?php elseif ($ticket['status'] == 'refunded' && !empty($ticket['refund_proof_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($ticket['refund_proof_path']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-file-earmark-check me-1"></i>View Refund Receipt
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-white-50">You haven't purchased any tickets yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Saved Events Column -->
        <div class="col-lg-4">
            <div class="glass-card p-4 h-100">
                <h4 class="mb-4 text-white border-bottom border-secondary pb-2"><i class="bi bi-heart-fill me-2"></i>Saved Events</h4>
                
                <?php if ($saved_result->num_rows > 0): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php while($event = $saved_result->fetch_assoc()): ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="flex-grow-1">
                                    <a href="#" class="text-white text-decoration-none fw-bold d-block"><?php echo htmlspecialchars($event['title']); ?></a>
                                    <small class="text-white-50"><?php echo date('M d', strtotime($event['date'])); ?></small>
                                </div>
                                <div>
                                    <a href="save_event_action.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-warning rounded-circle me-1" title="Unsave"><i class="bi bi-heart-fill"></i></a>
                                    <a href="buy_ticket.php?event_id=<?php echo $event['id']; ?>" class="btn btn-sm btn-premium rounded-pill px-3">Buy</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-white-50">No saved events.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$tickets_stmt->close();
$saved_stmt->close();
include 'footer.php'; 
?>
