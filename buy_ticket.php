<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

if (!isset($_GET['event_id'])) {
    header("Location: events.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Enforce login to buy
    exit;
}

$event_id = $_GET['event_id'];
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<div class='container py-5 text-white'>Event not found.</div>";
    include 'footer.php';
    exit;
}

$event = $result->fetch_assoc();

$is_saved = false;
$uid = intval($_SESSION['user_id']);
$check_save = $conn->prepare("SELECT id FROM saved_events WHERE user_id = ? AND event_id = ?");
if ($check_save) {
    $check_save->bind_param("ii", $uid, $event_id);
    $check_save->execute();
    if ($check_save->get_result()->num_rows > 0) {
        $is_saved = true;
    }
    $check_save->close();
}
?>

<div class="container py-5 d-flex justify-content-center">
    <div class="glass-card p-4 p-md-5 animate-fade-in" style="width: 100%; max-width: 900px;">
        <div class="row">
            <div class="col-md-5 border-end border-secondary mb-4 mb-md-0">
                <h4 class="fw-bold mb-3 text-white">Event Details</h4>
                <div class="mb-3">
                    <span class="text-white-50">Event</span><br>
                    <span class="h5 text-white"><?php echo htmlspecialchars($event['title']); ?></span>
                </div>
                <div class="mb-3">
                    <span class="text-white-50">Date & Time</span><br>
                    <span class="text-white">
                        <i class="bi bi-calendar3 me-2"></i><?php echo date('M d, Y', strtotime($event['date'])); ?><br>
                        <i class="bi bi-clock me-2"></i><?php echo date('h:i A', strtotime($event['time'])); ?>
                    </span>
                </div>
                <div class="mb-3">
                    <span class="text-white-50">Location</span><br>
                    <span class="text-white"><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($event['location']); ?></span>
                </div>
                <div class="mb-3">
                    <span class="text-white-50">Price</span><br>
                    <span class="h3 text-success fw-bold">
                        <?php echo ($event['price'] > 0) ? "ETB " . number_format($event['price'], 2) : "Free"; ?>
                    </span>
                </div>
            </div>
            
            <div class="col-md-7 ps-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-2">
                    <h3 class="fw-bold text-white mb-0">Complete Purchase</h3>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="save_event_action.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-warning" title="<?php echo $is_saved ? 'Unsave' : 'Save Event'; ?>">
                            <i class="bi <?php echo $is_saved ? 'bi-heart-fill' : 'bi-heart'; ?> me-2"></i><?php echo $is_saved ? 'Saved' : 'Save Event'; ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if ($event['price'] > 0): ?>
                    <?php 
                    $has_banks = !empty($event['cbe_account']) || !empty($event['awash_account']) || !empty($event['dashen_account']) || !empty($event['abissinia_account']) || !empty($event['telebirr_account']);
                    ?>
                    <?php if ($has_banks): ?>
                        <div class="alert alert-info bg-transparent text-white border-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            This is a manual payment system. Please transfer the total amount to one of the banks below and upload the receipt.
                        </div>

                        <form action="process_ticket.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            
                            <div class="mb-4">
                                <label class="form-label mb-2">Select Payment Method</label>
                                <select class="form-select bg-dark text-white border-secondary" id="payment_method" name="payment_method" required onchange="updateBankDetails()">
                                    <option value="">Choose a bank...</option>
                                    <?php if(!empty($event['cbe_account'])): ?><option value="CBE">Commercial Bank of Ethiopia</option><?php endif; ?>
                                    <?php if(!empty($event['awash_account'])): ?><option value="Awash">Awash Bank</option><?php endif; ?>
                                    <?php if(!empty($event['dashen_account'])): ?><option value="Dashen">Dashen Bank</option><?php endif; ?>
                                    <?php if(!empty($event['abissinia_account'])): ?><option value="Abissinia">Abissinia Bank</option><?php endif; ?>
                                    <?php if(!empty($event['telebirr_account'])): ?><option value="Telebirr">Telebirr</option><?php endif; ?>
                                </select>
                                <div id="bankDetails" class="mt-3 p-3 rounded bg-dark border border-secondary d-none">
                                    <h6 class="text-info mb-1" id="bankName">Bank Name</h6>
                                    <p class="mb-0 text-white-50 font-monospace" id="accountNumber">1000123456789</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="proof" class="form-label">Upload Payment Proof (Image/PDF)</label>
                                <input type="file" class="form-control" id="proof" name="proof" required accept="image/*,.pdf">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-premium btn-lg">Confirm Purchase</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning bg-transparent border-warning text-white">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            The organizer has not provided any bank accounts to receive payments for this event yet. Please check back later.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-success bg-transparent border-success text-white mb-4">
                        <i class="bi bi-check-circle me-2"></i>
                        This event is completely free! You don't need to make any payments. Just click below to secure your ticket.
                    </div>
                    <form action="process_ticket.php" method="POST">
                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        <input type="hidden" name="payment_method" value="Free">
                        <div class="d-grid">
                            <button type="submit" class="btn btn-premium btn-lg">Get Free Ticket</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function updateBankDetails() {
    const select = document.getElementById('payment_method');
    const detailsDiv = document.getElementById('bankDetails');
    const bankNameEl = document.getElementById('bankName');
    const accNumEl = document.getElementById('accountNumber');
    
    const accounts = {
        'CBE': '<?php echo htmlspecialchars($event['cbe_account'] ?? ''); ?>',
        'Awash': '<?php echo htmlspecialchars($event['awash_account'] ?? ''); ?>',
        'Dashen': '<?php echo htmlspecialchars($event['dashen_account'] ?? ''); ?>',
        'Abissinia': '<?php echo htmlspecialchars($event['abissinia_account'] ?? ''); ?>',
        'Telebirr': '<?php echo htmlspecialchars($event['telebirr_account'] ?? ''); ?>'
    };

    if (select.value) {
        detailsDiv.classList.remove('d-none');
        bankNameEl.textContent = select.options[select.selectedIndex].text;
        accNumEl.textContent = accounts[select.value] || 'N/A';
    } else {
        detailsDiv.classList.add('d-none');
    }
}
</script>

<?php include 'footer.php'; ?>
