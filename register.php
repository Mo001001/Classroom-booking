<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Classroom Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">User Registration</h4>
                </div>
                <div class="card-body">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        require_once 'includes/db.php';
                        $username = $_POST['username'];
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $role = $_POST['role'];
                        $real_name = $_POST['real_name'];
                        $email = $_POST['email'];
                        
                        $conn = Database::getInstance()->getConnection();
                        $stmt = $conn->prepare("INSERT INTO users (username, password, role, real_name, email) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssss", $username, $password, $role, $real_name, $email);
                        
                        if ($stmt->execute()) {
                            echo '<div class="alert alert-success">Registration successful! <a href="login.php">Login here</a></div>';
                        } else {
                            echo '<div class="alert alert-danger">Username already exists or registration failed.</div>';
                        }
                    }
                    ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Username (Student/Staff ID)</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Full Name</label>
                            <input type="text" name="real_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" class="form-control">
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="login.php">Already have an account? Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>