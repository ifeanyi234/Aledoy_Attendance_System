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
