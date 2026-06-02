<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Classroom Booking System</title>

<link rel="stylesheet"
href="/Classroom-booking/assets/css/liquid-glass.css">

<link rel="stylesheet"
href="/Classroom-booking/assets/css/custom-glass.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>

body{

padding-top:90px;

}

.container{

max-width:1400px;

}

.navbar{

background:rgba(255,255,255,0.10)!important;

backdrop-filter:blur(20px);

-webkit-backdrop-filter:blur(20px);

border:1px solid rgba(255,255,255,0.15);

box-shadow:0 8px 32px rgba(0,0,0,0.20);

}

.navbar-brand{

font-weight:700;

}

.nav-link{

border-radius:12px;

transition:.3s ease;

}

.nav-link:hover{

background:rgba(255,255,255,0.10);

}

.card{

background:rgba(255,255,255,0.10);

backdrop-filter:blur(20px);

border:1px solid rgba(255,255,255,0.15);

border-radius:22px;

box-shadow:0 8px 32px rgba(0,0,0,0.20);

}

.status-badge{

padding:3px 8px;

border-radius:20px;

font-size:12px;

}

.status-pending{

background:#ffc107;

color:#000;

}

.status-confirmed{

background:#0d6efd;

color:#fff;

}

.status-using{

background:#198754;

color:#fff;

}

.status-completed{

background:#6c757d;

color:#fff;

}

.status-cancelled{

background:#dc3545;

color:#fff;

}

</style>

</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">

<div class="container">

<a class="navbar-brand"
href="index.php">

<i class="fas fa-chalkboard-user"></i>

Classroom Booking

</a>

<button
class="navbar-toggler"
type="button"
data-bs-toggle="collapse"
data-bs-target="#navbarNav">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse"
id="navbarNav">

<ul class="navbar-nav ms-auto">

<?php if (isset($_SESSION['user_id'])): ?>

<li class="nav-item">

<a class="nav-link"
href="my_bookings.php">

<i class="fas fa-calendar-alt"></i>

My Bookings

</a>

</li>

<?php if (
isset($_SESSION['role'])
&& $_SESSION['role']=='admin'
): ?>

<li class="nav-item">

<a class="nav-link"
href="admin/">

<i class="fas fa-cog"></i>

Admin Panel

</a>

</li>

<?php endif; ?>

<li class="nav-item">

<a class="nav-link"
href="logout.php">

<i class="fas fa-sign-out-alt"></i>

<?php
echo htmlspecialchars(
$_SESSION['real_name']
);
?>

Logout

</a>

</li>

<?php else: ?>

<li class="nav-item">

<a class="nav-link"
href="login.php">

<i class="fas fa-sign-in-alt"></i>

Login

</a>

</li>

<li class="nav-item">

<a class="nav-link"
href="register.php">

<i class="fas fa-user-plus"></i>

Register

</a>

</li>

<?php endif; ?>

<li class="nav-item">

<a class="nav-link"
href="#"
data-bs-toggle="modal"
data-bs-target="#helpModal">

<i class="fas fa-question-circle"></i>

Help

</a>

</li>

</ul>

</div>

</div>

</nav>

<div class="container">