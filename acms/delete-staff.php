<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/connection/connect.php';

if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

if (!isset($_SESSION['user_role']) || (int)$_SESSION['user_role'] !== 1) {
    $_SESSION['error'] = "Access Denied: You lack the administrative clearance to delete records.";
    header("Location: staff_list.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid target identity token received.";
    header("Location: staff_list.php");
    exit;
}

$fetch_query = "SELECT passport_image, qr_code FROM staff WHERE id = $id LIMIT 1";
$fetch_result = mysqli_query($db, $fetch_query);

if ($fetch_result && mysqli_num_rows($fetch_result) > 0) {
    $files = mysqli_fetch_assoc($fetch_result);
    
    // Clear the physical passport image from disk storage
    if (!empty($files['passport_image']) && file_exists(__DIR__ . '/' . $files['passport_image'])) {
        @unlink(__DIR__ . '/' . $files['passport_image']);
    }
    
    // Clear the physical identity QR code file from disk storage
    if (!empty($files['qr_code']) && file_exists(__DIR__ . '/' . $files['qr_code'])) {
        @unlink(__DIR__ . '/' . $files['qr_code']);
    }
}

$query = "DELETE FROM staff WHERE id = $id";
$result = mysqli_query($db, $query);

if ($result) {
    $_SESSION['success'] = "Staff registry account and all associated asset files successfully purged.";
} else {
    $_SESSION['error'] = "System failure: Unable to complete data truncation. " . mysqli_error($db);
}

header("Location: staff_list.php");
exit;
?>