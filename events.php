<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

// Build Query with Filters
$where_clauses = ["date >= CURDATE()"];
$params = [];
$types = "";

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where_clauses[] = "location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
    $types .= "s";
}

if (isset($_GET['date']) && !empty($_GET['date'])) {
    $where_clauses[] = "date = ?";
    $params[] = $_GET['date'];
    $types .= "s";
}

if (isset($_GET['theme']) && !empty($_GET['theme'])) {
    $where_clauses[] = "theme = ?";
    $params[] = $_GET['theme'];
    $types .= "s";
}

$sql = "SELECT * FROM events WHERE " . implode(" AND ", $where_clauses) . " ORDER BY date ASC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

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

<div class="container py-5">
    <h2 class="text-white mb-4 text-center fw-bold">Browse Events</h2>

    <!-- Filter Section -->
    <div class="glass-card p-4 mb-5 animate-fade-in">
        <form action="events.php" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="location" placeholder="Location..." value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" name="date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="theme">
                    <option value="">All Themes</option>
                    <option value="Music" <?php if(isset($_GET['theme']) && $_GET['theme'] == 'Music') echo 'selected'; ?>>Music</option>
                    <option value="Technology" <?php if(isset($_GET['theme']) && $_GET['theme'] == 'Technology') echo 'selected'; ?>>Technology</option>
                    <option value="Art" <?php if(isset($_GET['theme']) && $_GET['theme'] == 'Art') echo 'selected'; ?>>Art</option>
                    <option value="Business" <?php if(isset($_GET['theme']) && $_GET['theme'] == 'Business') echo 'selected'; ?>>Business</option>
                    <option value="Education" <?php if(isset($_GET['theme']) && $_GET['theme'] == 'Education') echo 'selected'; ?>>Education</option>
                    <option value="Sports" <?php if(isset($_GET['theme']) && $_GET['theme'] == 'Sports') echo 'selected'; ?>>Sports</option>
                    <option value="Other" <?php if(isset($_GET['theme']) && $_GET['theme'] == 'Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary">Filter Events</button>
            </div>
        </form>
    </div>

    <!-- Events Grid -->
    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="glass-card h-100 event-card p-0 overflow-hidden">
                    <div class="p-4" style="background: linear-gradient(to bottom, rgba(118, 75, 162, 0.1), transparent);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-secondary rounded-pill"><?php echo htmlspecialchars($row['theme']); ?></span>
                            <span class="text-white-50 small"><i class="bi bi-calendar3 me-1"></i><?php echo date('M d', strtotime($row['date'])); ?></span>
                        </div>
                        <h4 class="fw-bold mb-2 text-white"><?php echo htmlspecialchars($row['title']); ?></h4>
                        <p class="text-white-50 small mb-2"><i class="bi bi-geo-alt-fill me-1"></i><?php echo htmlspecialchars($row['location']); ?></p>
                        <p class="text-white small mb-0"><?php echo htmlspecialchars(substr($row['items'], 0, 50)) . '...'; ?></p>
                    </div>
                    <div class="p-3 border-top border-secondary">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-column">
                                <span class="h5 mb-0 text-success fw-bold">
                                    <?php echo ($row['price'] > 0) ? "ETB " . number_format($row['price'], 2) : "Free"; ?>
                                </span>
                            </div>
                                <div>
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <?php $is_saved = in_array($row['id'], $saved_events); ?>
                                        <a href="save_event_action.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-warning btn-sm me-1 rounded-circle" title="<?php echo $is_saved ? 'Unsave' : 'Save Event'; ?>">
                                            <i class="bi <?php echo $is_saved ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="buy_ticket.php?event_id=<?php echo $row['id']; ?>" class="btn btn-premium btn-sm rounded-pill px-3">Buy Ticket</a>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <h4 class="text-white-50">No events found matching your criteria.</h4>
                <a href="events.php" class="btn btn-link text-info">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
