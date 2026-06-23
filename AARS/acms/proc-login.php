<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include('connection/connect.php');

// ensure POST keys exist
$username = isset($_POST['username']) ? mysqli_real_escape_string($db, $_POST['username']) : '';
$password = isset($_POST['password']) ? mysqli_real_escape_string($db, $_POST['password']) : '';

$password_length = strlen($password);

if (!$username) {
  $_SESSION['username_err'] = true;
  $_SESSION['login_username'] = $username;
  header('Location: index.php');
  exit;
}

if (!$password) {
  $_SESSION['password_err'] = true;
  $_SESSION['login_username'] = $username;
  header('Location: index.php');
  exit;
}

if ($password_length < 8) {
  $_SESSION['passwordlength_err'] = true;
  $_SESSION['login_username'] = $username;
  header('Location: index.php');
  exit;
}

$query = "select * from users where username = '$username' and password = '$password'";
$result = mysqli_query($db, $query);
$num = mysqli_num_rows($result);
if ($num > 0) {
  $_SESSION['acms_valid_user'] = $username;
  header('Location: dashboard.php');
  exit;
} else {
  $_SESSION['login_error'] = true;
  $_SESSION['login_username'] = $username;
  header('Location: index.php');
  exit;
}
