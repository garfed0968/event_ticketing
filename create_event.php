<?php include 'header.php'; ?>
<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: login.php");
    exit;
}
?>

<div class="container py-5 d-flex justify-content-center">
    <div class="glass-card p-5 animate-fade-in" style="width: 100%; max-width: 800px;">
        <div class="d-flex align-items-center mb-4 border-bottom border-secondary pb-3">
            <a href="organizer_dashboard.php" class="text-white me-3"><i class="bi bi-arrow-left h4"></i></a>
            <h2 class="fw-bold mb-0">Create New Event</h2>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="save_event.php" method="POST" onsubmit="return validateEventForm()">
            <div class="mb-3">
                <label for="title" class="form-label">Event Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" required>
                    <div id="dateFeedback" class="invalid-feedback d-block text-danger small"></div>
                </div>
                <div class="col-md-6">
                    <label for="time" class="form-label">Time</label>
                    <input type="time" class="form-control" id="time" name="time" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Millennium Hall" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="theme" class="form-label">Theme</label>
                    <select class="form-select" id="theme" name="theme" required>
                        <option value="Music">Music</option>
                        <option value="Technology">Technology</option>
                        <option value="Art">Art</option>
                        <option value="Business">Business</option>
                        <option value="Education">Education</option>
                        <option value="Sports">Sports</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="price" class="form-label">Ticket Price (ETB)</label>
                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="0.00" required>
                    <div class="form-text text-white-50">Enter 0 for free events.</div>
                </div>
            </div>

            <div class="mb-4">
                <label for="items" class="form-label">Available Items / Services</label>
                <textarea class="form-control" id="items" name="items" rows="3" placeholder="e.g., Free Snacks, VIP Parking, Refreshments..."></textarea>
            </div>

            <h5 class="fw-bold mb-3 border-bottom border-secondary pb-2 text-white">Payment Accounts (Optional)</h5>
            <div class="alert alert-info bg-transparent border-info text-white-50 small mb-3">
                <i class="bi bi-info-circle me-2"></i>Provide account numbers for the banks you want to accept payments through. Only the ones you fill out will be shown to ticket buyers.
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="cbe_account" class="form-label">CBE Account</label>
                    <input type="text" class="form-control" id="cbe_account" name="cbe_account" placeholder="e.g., 1000123456789">
                </div>
                <div class="col-md-4">
                    <label for="awash_account" class="form-label">Awash Bank</label>
                    <input type="text" class="form-control" id="awash_account" name="awash_account">
                </div>
                <div class="col-md-4">
                    <label for="dashen_account" class="form-label">Dashen Bank</label>
                    <input type="text" class="form-control" id="dashen_account" name="dashen_account">
                </div>
                <div class="col-md-6">
                    <label for="abissinia_account" class="form-label">Abissinia Bank</label>
                    <input type="text" class="form-control" id="abissinia_account" name="abissinia_account">
                </div>
                <div class="col-md-6">
                    <label for="telebirr_account" class="form-label">Telebirr Number</label>
                    <input type="text" class="form-control" id="telebirr_account" name="telebirr_account" placeholder="e.g., +2519... or 09...">
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-premium btn-lg">Publish Event</button>
            </div>
        </form>
    </div>
</div>

<script>
function validateEventForm() {
    const dateInput = document.getElementById('date');
    const priceInput = document.getElementById('price');
    const today = new Date().toISOString().split('T')[0];
    let isValid = true;

    // Reset feedback
    document.getElementById('dateFeedback').innerText = '';

    // Date Validation (Must check past dates)
    if (dateInput.value < today) {
        document.getElementById('dateFeedback').innerText = 'Event date cannot be in the past.';
        isValid = false;
    }

    // Price Validation (Cannot be negative)
    if (priceInput.value < 0) {
        alert('Price cannot be negative.');
        isValid = false;
    }

    return isValid;
}
</script>

<?php include 'footer.php'; ?>
