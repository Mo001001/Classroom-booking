<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Register - Classroom Booking</title>

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

User Registration

</h2>

<?php

if ($_SERVER['REQUEST_METHOD']=='POST') {

require_once 'includes/db.php';

$username=$_POST['username'];

$password=password_hash(
$_POST['password'],
PASSWORD_DEFAULT
);

$role=$_POST['role'];

$real_name=$_POST['real_name'];

$email=$_POST['email'];

$conn=Database::getInstance()
->getConnection();

$stmt=$conn->prepare(

"INSERT INTO users
(username,password,role,real_name,email)

VALUES (?,?,?,?,?)"

);

$stmt->bind_param(

"sssss",

$username,
$password,
$role,
$real_name,
$email

);

if($stmt->execute()){

echo '

<div class="glass-alert alert-success">

Registration successful!

<a href="login.php">

Login here

</a>

</div>

';

}

else{

echo '

<div class="glass-alert alert-danger">

Username already exists
or registration failed.

</div>

';

}

}

?>

<form method="POST">

<div class="mb-3">

<label class="glass-label">

Username (Student/Staff ID)

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

<div class="mb-3">

<label class="glass-label">

Full Name

</label>

<input
type="text"
name="real_name"
class="form-control glass-input"
required>

</div>

<div class="mb-3">

<label class="glass-label">

Email

</label>

<input
type="email"
name="email"
class="form-control glass-input"
required>

</div>

<div class="mb-3">

<label class="glass-label">

Role

</label>

<select
name="role"
class="form-control glass-select">

<option value="student">

Student

</option>

<option value="teacher">

Teacher

</option>

</select>

</div>

<button
type="submit"
class="glass-submit">

Register

</button>

</form>

<div class="mt-4 text-center">

<a
href="login.php"
style="color:white;">

Already have an account?

Login

</a>

</div>

</div>

</div>

</div>

</div>

</body>

</html>
