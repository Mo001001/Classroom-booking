<?php require_once 'includes/header.php'; ?>
<div class="card">
    <div class="card-body">
        <h2>Welcome to Classroom Booking System</h2>
        <p>This is a temporary homepage. The interactive campus map will appear here in the next phase.</p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>You are logged in as <strong><?= htmlspecialchars($_SESSION['real_name']) ?></strong> (<?= $_SESSION['role'] ?>).</p>
            <a href="my_bookings.php" class="btn btn-primary">View My Bookings</a>
        <?php else: ?>
            <p>Please <a href="login.php">login</a> to book classrooms.</p>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>