<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/connection/connect.php';

$valid_user = $_SESSION['acms_valid_user'];

// $password = $_POST['password'];
$old_password = mysqli_real_escape_string($db, $_POST['old_password']);
$new_password = mysqli_real_escape_string($db, $_POST['new_password']);
$confirm_password = mysqli_real_escape_string($db, $_POST['confirm_password']);

if ($old_password == '' || $new_password == '' || $confirm_password == '') {
    $error = "All fields are required";
    include('change-password.php');
    exit;
}

if ($new_password != $confirm_password) {
    $error = 'Password do not match';
    include('change-password.php');
    exit;
}

// --- STRICT ACCOUNT PASSWORD COMPLEXITY RULES ENGINE ---
$uppercase = preg_match('@[A-Z]@', $new_password);
$lowercase = preg_match('@[a-z]@', $new_password);
$number    = preg_match('@[0-9]@', $new_password);
$specialchar = preg_match('@[^A-Za-z0-9]@', $new_password);

if (strlen($new_password) < 8) {
    $error = 'Password should be at least 8 characters in length and should include at least one uppercase letter, one lowercase, one number, and one special character.';
    include('change-password.php');
    exit;
}

if (!$uppercase) {
    $error = 'Password should include an uppercase letter';
    include('change-password.php');
    exit;
}

if (!$lowercase) {
    $error = 'Password should include a lowercase letter';
    include('change-password.php');
    exit;
}

if (!$number) {
    $error = 'Password should include a number';
    include('change-password.php');
    exit;
}

if (!$specialchar) {
    $error = 'Password should include a special character';
    include('change-password.php');
    exit;
}
// --- END RULES ENGINE ---

$sql = "SELECT * from users  WHERE username = '$valid_user' and password = '$old_password'";
$result = mysqli_query($db, $sql);
$num = mysqli_num_rows($result);
if ($num > 0) {
    $sql_2 = "UPDATE users  SET password ='$new_password' WHERE username='$valid_user'";
    mysqli_query($db, $sql_2);
    $success = "Password Changed Sucessfully";
    include('change-password.php');
    exit;
} else {
    $error = "Old Password is not correct";
    include('change-password.php');
    exit;
}