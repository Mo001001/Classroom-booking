<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Classroom Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        require_once 'includes/db.php';
                        $username = $_POST['username'];
                        $password = $_POST['password'];
                        
                        $conn = Database::getInstance()->getConnection();
                        $stmt = $conn->prepare("SELECT id, username, password, role, real_name FROM users WHERE username = ?");
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($user = $result->fetch_assoc()) {
                            if (password_verify($password, $user['password'])) {
                                $_SESSION['user_id'] = $user['id'];
                                $_SESSION['username'] = $user['username'];
                                $_SESSION['role'] = $user['role'];
                                $_SESSION['real_name'] = $user['real_name'];
                                header('Location: index.php');
                                exit;
                            }
                        }
                        echo '<div class="alert alert-danger">Invalid username or password.</div>';
                    }
                    ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="register.php">No account? Register</a>
                    </div>
                    <hr>
                    <div class="text-center text-muted small">
                        Test accounts:<br>
                        Admin: admin / admin123<br>
                        Teacher: teacher01 / admin123<br>
                        Student: student01 / admin123
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>