<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Login - Classroom Booking</title>

<link rel="stylesheet"
href="/Classroom-booking/assets/css/liquid-glass.css">

<link rel="stylesheet"
href="/Classroom-booking/assets/css/custom-glass.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container">

<div class="row justify-content-center mt-5">

<div class="col-md-6">

<div class="glass-form-container">

<h2 class="glass-form-title">

Login

</h2>

<?php

if ($_SERVER['REQUEST_METHOD']=='POST') {

require_once 'includes/db.php';

$username=$_POST['username'];

$password=$_POST['password'];

$conn=Database::getInstance()
->getConnection();

$stmt=$conn->prepare(

"SELECT id, username,
password, role, real_name
FROM users
WHERE username=?"

);

$stmt->bind_param(
"s",
$username
);

$stmt->execute();

$result=$stmt->get_result();

if(
$user=$result->fetch_assoc()
){

if(

password_verify(
$password,
$user['password']
)

){

$_SESSION['user_id']
=$user['id'];

$_SESSION['username']
=$user['username'];

$_SESSION['role']
=$user['role'];

$_SESSION['real_name']
=$user['real_name'];

if ($user['role'] === 'admin') {
    header('Location: admin/bookings.php');
} else {
    header('Location: index.php');
}

exit;

}

}

echo '

<div class="glass-alert alert-danger">

Invalid username or password.

</div>

';

}

?>

<form method="POST">

<div class="mb-3">

<label class="glass-label">

Username

</label>

<input
type="text"
name="username"
class="form-control glass-input"
required>

</div>

<div class="mb-3">

<label class="glass-label">

Password

</label>

<input
type="password"
name="password"
class="form-control glass-input"
required>

</div>

<button
type="submit"
class="glass-submit">

Login

</button>

</form>

<div class="mt-4 text-center">

<a
href="register.php"
style="color:white;">

No account? Register

</a>

</div>

<hr>

<div
class="text-center small"
style="color:rgba(255,255,255,.70);">

Test accounts:

<br>

Admin:
admin / admin123

<br>

Teacher:
teacher01 / admin123

<br>

Student:
student01 / admin123

</div>

</div>

</div>

</div>

</div>

</body>

</html>

