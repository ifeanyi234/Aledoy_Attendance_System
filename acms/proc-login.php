<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/connection/connect.php';

$username = isset($_POST['username']) ? mysqli_real_escape_string($db, trim($_POST['username'])) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

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

$query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
$result = mysqli_query($db, $query);

if ($result && mysqli_num_rows($result) > 0) {
  $row = mysqli_fetch_assoc($result);
 
  // Cryptographically verify the password, with a temporary plain-text fallback
  if (password_verify($password, $row['password']) || $password === $row['password']) {
    $_SESSION['acms_valid_user'] = $username;
    
    // Capture user role from database (1 = Main Admin, 0 = Staff)
    $_SESSION['user_role'] = (int)$row['role']; 
    
    header('Location: dashboard.php');
    exit;
  }
}

// Fallback logic for incorrect credentials
$_SESSION['login_error'] = true;
$_SESSION['login_username'] = $username;
header('Location: index.php');
exit;
?>