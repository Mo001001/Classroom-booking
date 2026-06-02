<?php require_once 'includes/header.php'; ?>

<div class="card">

<div class="card-body p-5">

<h1 class="mb-4">

Welcome to Classroom Booking System

</h1>

<p class="lead">

This is a temporary homepage.

The interactive campus map
will appear here in the next phase.

</p>

<hr>

<?php if (isset($_SESSION['user_id'])): ?>

<p>

You are logged in as

<strong>

<?= htmlspecialchars(
$_SESSION['real_name']
) ?>

</strong>

(<?= $_SESSION['role'] ?>)

</p>

<a
href="my_bookings.php"
class="glass-submit d-inline-block text-center text-decoration-none">

View My Bookings

</a>

<?php else: ?>

<p>

Please

<a
href="login.php"
style="color:#93c5fd;">

login

</a>

to book classrooms.

</p>

<a
href="register.php"
class="glass-submit d-inline-block text-center text-decoration-none">

Create Account

</a>

<?php endif; ?>

</div>

</div>

<?php require_once 'includes/footer.php'; ?>