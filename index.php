<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

// Fetch Upcoming Events (Limit 6 for landing page)
$today = date('Y-m-d');
$sql = "SELECT * FROM events WHERE date >= '$today' ORDER BY date ASC LIMIT 6";
$result = $conn->query($sql);

$saved_events = [];
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $saved_query = $conn->query("SELECT event_id FROM saved_events WHERE user_id = $uid");
    if ($saved_query) {
        while($srow = $saved_query->fetch_assoc()) {
            $saved_events[] = $srow['event_id'];
        }
    }
}
?>

<!-- Hero Section -->
<section class="hero-section text-center text-white">
    <div class="hero-bg"></div>
    <div class="container animate-fade-in">
        <h1 class="display-3 fw-bold mb-3">Experience the Extraordinary</h1>
        <p class="lead mb-5 text-white-50">Discover and book tickets for the hottest events in Ethiopia.</p>
        <a href="events.php" class="btn btn-premium btn-lg px-5 py-3 rounded-pill">Browse All Events</a>
    </div>
</section>

<!-- Upcoming Events Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-white mb-4 border-start border-4 border-primary ps-3">Upcoming Events</h2>
        
        <div class="row g-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card h-100 event-card p-0 overflow-hidden">
                        <!-- Placeholder Image if we had one, for now just a gradient header -->
                        <div class="p-4" style="background: linear-gradient(to bottom, rgba(13, 110, 253, 0.1), transparent);">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary rounded-pill"><?php echo htmlspecialchars($row['theme']); ?></span>
                                <span class="text-white-50 small"><i class="bi bi-calendar3 me-1"></i><?php echo date('M d', strtotime($row['date'])); ?></span>
                            </div>
                            <h4 class="fw-bold mb-2 text-white"><?php echo htmlspecialchars($row['title']); ?></h4>
                            <p class="text-white-50 small mb-2"><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars($row['location']); ?></p>
                        </div>
                        <div class="p-3 border-top border-secondary">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0 text-success fw-bold">
                                    <?php echo ($row['price'] > 0) ? "ETB " . number_format($row['price'], 2) : "Free"; ?>
                                </span>
                                <div>
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <?php $is_saved = in_array($row['id'], $saved_events); ?>
                                        <a href="save_event_action.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-warning btn-sm me-1 rounded-circle" title="<?php echo $is_saved ? 'Unsave' : 'Save Event'; ?>">
                                            <i class="bi <?php echo $is_saved ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="buy_ticket.php?event_id=<?php echo $row['id']; ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">Get Ticket</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h4 class="text-white-50">No upcoming events found. Check back later!</h4>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
        <div class="text-center mt-5">
            <a href="events.php" class="btn btn-outline-light px-4">View More Events</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
