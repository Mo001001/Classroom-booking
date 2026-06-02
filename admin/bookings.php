<?php
// 如果 session 还没开始，才启动
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check login and permission
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// ============================================
// 数据库连接
// ============================================
$host = 'localhost';
$dbname = 'classroom_booking';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle force cancel with reason
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $booking_id = (int)$_POST['cancel_booking_id'];
    $cancel_reason = trim($_POST['cancel_reason']);
    
    if (!empty($cancel_reason)) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled', cancel_reason = ? WHERE booking_id = ?");
        $stmt->execute([$cancel_reason, $booking_id]);
        header("Location: bookings.php?msg=Booking cancelled and notification sent");
        exit;
    }
}

// Handle approve / reject
if (isset($_GET['action']) && isset($_GET['booking_id'])) {
    $booking_id = (int)$_GET['booking_id'];
    $action = $_GET['action'];
    
    if (in_array($action, ['approve', 'reject'])) {
        $status = ($action === 'approve') ? 'confirmed' : 'cancelled';
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $stmt->execute([$status, $booking_id]);
        header("Location: bookings.php?msg=Operation successful");
        exit;
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$classroom_filter = $_GET['room_id'] ?? '';
$date_filter = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT 
            b.booking_id,
            b.user_id,
            b.room_id,
            b.booking_date,
            b.start_time,
            b.end_time,
            b.status,
            b.cancel_reason,
            u.username,
            c.room_name,
            c.floor
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN classrooms c ON b.room_id = c.room_id
        WHERE 1=1";

$params = [];

if ($status_filter !== '') {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
}

if ($classroom_filter !== '') {
    $sql .= " AND b.room_id = ?";
    $params[] = $classroom_filter;
}

if ($date_filter !== '') {
    $sql .= " AND b.booking_date = ?";
    $params[] = $date_filter;
}

if ($search !== '') {
    $sql .= " AND u.username LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY b.booking_date DESC, b.start_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get classrooms for filter
$classrooms = $pdo->query("SELECT room_id, room_name FROM classrooms ORDER BY floor, room_name")->fetchAll();

// 辅助函数：格式化日期
function formatDate($date) {
    if (empty($date)) return '';
    if (strpos($date, '年') !== false || strpos($date, '月') !== false || strpos($date, '日') !== false) {
        $date = str_replace('年', '-', $date);
        $date = str_replace('月', '-', $date);
        $date = str_replace('日', '', $date);
        return date('Y-m-d', strtotime($date));
    }
    return $date;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
            margin: 0;
        }
        
        .glass-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .glass-title {
            color: #333;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .filter-bar {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            align-items: flex-end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-group label {
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }
        
        .filter-group input,
        .filter-group select {
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 10px 16px;
            color: #333;
            font-size: 14px;
            min-height: 42px;
            cursor: pointer;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .filter-group input[type="date"] {
            cursor: pointer;
        }
        
        .filter-group input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
        }
        
        .glass-btn {
            background: #667eea;
            border: none;
            border-radius: 12px;
            padding: 10px 24px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .glass-btn:hover {
            background: #5a6fd6;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            color: #333;
        }
        
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background: rgba(102, 126, 234, 0.1);
            font-weight: 600;
        }
        
        tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background: #ffc107;
            color: #333;
        }
        
        .status-confirmed {
            background: #28a745;
            color: white;
        }
        
        .status-cancelled {
            background: #dc3545;
            color: white;
        }
        
        .status-completed {
            background: #6c757d;
            color: white;
        }
        
        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
            margin: 2px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        
        .btn-sm:hover {
            opacity: 0.85;
        }
        
        .msg-success {
            background: rgba(40, 167, 69, 0.15);
            border: 1px solid #28a745;
            border-radius: 12px;
            padding: 12px 20px;
            color: #155724;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .back-link {
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.3);
            padding: 8px 20px;
            border-radius: 20px;
        }
        
        .back-link:hover {
            background: rgba(0, 0, 0, 0.5);
            color: white;
            text-decoration: none;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 30px;
            width: 500px;
            max-width: 90%;
        }
        
        .modal-content h3 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .modal-content textarea {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
            resize: vertical;
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }
        
        .quick-reasons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .quick-reason {
            padding: 6px 14px;
            background: #f0f0f0;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            color: #333;
        }
        
        .quick-reason:hover {
            background: #e0e0e0;
        }
        
        .text-center {
            text-align: center;
        }
        
        hr {
            border-color: rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="glass-container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        
        <div class="glass-card">
            <h1 class="glass-title">📋 Booking Management</h1>
            
            <?php if (isset($_GET['msg'])): ?>
                <div class="msg-success">✅ <?= htmlspecialchars($_GET['msg']) ?></div>
            <?php endif; ?>
            
            <div class="filter-bar">
                <div class="filter-group">
                    <label>Status</label>
                    <select id="status_filter" onchange="applyFilters()">
                        <option value="">All</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Classroom</label>
                    <select id="classroom_filter" onchange="applyFilters()">
                        <option value="">All Classrooms</option>
                        <?php foreach ($classrooms as $c): ?>
                            <option value="<?= $c['room_id'] ?>" <?= $classroom_filter == $c['room_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['room_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Date</label>
                    <input type="date" id="date_filter" value="<?= $date_filter ?>" onchange="applyFilters()">
                </div>
                
                <div class="filter-group">
                    <label>Search User</label>
                    <input type="text" id="search_input" placeholder="Username" value="<?= htmlspecialchars($search) ?>" onkeypress="if(event.key==='Enter') applyFilters()">
                </div>
                
                <div class="filter-group">
                    <button class="glass-btn" onclick="applyFilters()">🔍 Filter</button>
                    <button class="glass-btn" onclick="resetFilters()" style="background: #6c757d;">🔄 Reset</button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Classroom</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($bookings) > 0): ?>
                            <?php foreach ($bookings as $row): ?>
                                <tr>
                                    <td><?= $row['booking_id'] ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['room_name']) ?> (<?= $row['floor'] ?>F)</td>
                                    <td><?= formatDate($row['booking_date']) ?></td>
                                    <td><?= substr($row['start_time'], 0, 5) ?> - <?= substr($row['end_time'], 0, 5) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $row['status'] ?>">
                                            <?php
                                            switch ($row['status']) {
                                                case 'pending': echo 'Pending'; break;
                                                case 'confirmed': echo 'Confirmed'; break;
                                                case 'cancelled': echo 'Cancelled'; break;
                                                case 'completed': echo 'Completed'; break;
                                                default: echo $row['status'];
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <a href="?action=approve&booking_id=<?= $row['booking_id'] ?>" class="btn-sm btn-success" onclick="return confirm('Approve this booking?')">✅ Approve</a>
                                            <a href="?action=reject&booking_id=<?= $row['booking_id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Reject this booking?')">❌ Reject</a>
                                        <?php endif; ?>
                                        
                                        <?php if ($row['status'] !== 'cancelled'): ?>
                                            <button class="btn-sm btn-warning" onclick="showCancelModal(<?= $row['booking_id'] ?>, '<?= htmlspecialchars($row['username']) ?>')">🔧 Force Cancel</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No bookings found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <hr>
            <div class="text-center" style="color: #666; font-size: 12px;">
                Total: <?= count($bookings) ?> booking(s)
            </div>
        </div>
    </div>
    
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <h3>⚠️ Force Cancel Booking</h3>
            <p id="cancelUserInfo" style="color: #666; margin-bottom: 15px;"></p>
            
            <div class="quick-reasons">
                <span class="quick-reason" onclick="setCancelReason('Classroom under maintenance')">🔧 Classroom under maintenance</span>
                <span class="quick-reason" onclick="setCancelReason('Schedule conflict with event')">📅 Schedule conflict with event</span>
                <span class="quick-reason" onclick="setCancelReason('Usage violates policy')">⚠️ Usage violates policy</span>
                <span class="quick-reason" onclick="setCancelReason('Other')">📝 Other</span>
            </div>
            
            <textarea id="cancelReason" rows="4" placeholder="Please enter cancellation reason (will be sent to user)..."></textarea>
            
            <form id="cancelForm" method="POST" action="">
                <input type="hidden" name="cancel_booking_id" id="cancelBookingId">
                <div class="modal-buttons">
                    <button type="button" class="glass-btn" onclick="closeModal()" style="background: #6c757d;">Close</button>
                    <button type="submit" class="glass-btn" style="background: #dc3545;">Confirm & Send Notification</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function applyFilters() {
            const status = document.getElementById('status_filter').value;
            const room_id = document.getElementById('classroom_filter').value;
            const date = document.getElementById('date_filter').value;
            const search = document.getElementById('search_input').value;
            
            let url = '?';
            if (status) url += `status=${status}&`;
            if (room_id) url += `room_id=${room_id}&`;
            if (date) url += `date=${date}&`;
            if (search) url += `search=${encodeURIComponent(search)}&`;
            
            window.location.href = url.slice(0, -1);
        }
        
        function resetFilters() {
            window.location.href = 'bookings.php';
        }
        
        function showCancelModal(bookingId, username) {
            document.getElementById('cancelBookingId').value = bookingId;
            document.getElementById('cancelUserInfo').innerText = `Cancelling: ${username}'s booking`;
            document.getElementById('cancelReason').value = '';
            document.getElementById('cancelModal').classList.add('show');
        }
        
        function setCancelReason(reason) {
            const textarea = document.getElementById('cancelReason');
            if (reason === 'Other') {
                textarea.value = '';
                textarea.placeholder = 'Please enter cancellation reason...';
            } else {
                textarea.value = reason;
            }
        }
        
        function closeModal() {
            document.getElementById('cancelModal').classList.remove('show');
        }
        
        document.getElementById('cancelModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>