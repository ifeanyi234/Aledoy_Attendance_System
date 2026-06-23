<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication Check
if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

require_once __DIR__ . '/connection/connect.php';
require_once('fns.php');

// Sanitize and capture incoming staff payload parameters
$id         = mysqli_real_escape_string($db, $_POST['id']);
$staff_id   = mysqli_real_escape_string($db, $_POST['staff_id']);
$firstname  = mysqli_real_escape_string($db, $_POST['firstname']);
$lastname   = mysqli_real_escape_string($db, $_POST['lastname']);
$email      = mysqli_real_escape_string($db, $_POST['email']);
$phone      = mysqli_real_escape_string($db, $_POST['phone']);
$staff_type = mysqli_real_escape_string($db, $_POST['staff_type']); // 'main staff', 'academy', or 'occasional'
$course     = mysqli_real_escape_string($db, $_POST['course']);

$error = '';
$success = '';

// Handle Profile Picture upload mechanics if a new file is specified
if (isset($_FILES['userfile']) && !empty($_FILES['userfile']['tmp_name'])) {
    $userfile = $_FILES['userfile']['tmp_name'];
    $userfile_name = $_FILES['userfile']['name'];
    $ext = strtolower(pathinfo($userfile_name, PATHINFO_EXTENSION));
    $folder = '../dist/uploads/';

    if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
        // Construct a clean, predictable filename using the staff entry row identifier
        $newfile = 'staff_' . $id . '.' . $ext;
        move_uploaded_file($userfile, $folder . $newfile);

        // Update database photo reference string column (e.g., profile_image or photo)
        $query_img = "update staff set profile_image = '$newfile' where id = '$id'";
        mysqli_query($db, $query_img);
    } else {
        $error = 'The uploaded image format is not supported. Use JPG, JPEG, or PNG.';
        include('edit-staff.php');
        exit;
    }
}

// Update text fields inside the core staff registry system table 
$query = "update staff set 
            staff_id = '$staff_id', 
            firstname = '$firstname', 
            lastname = '$lastname', 
            email = '$email', 
            phone = '$phone', 
            staff_type = '$staff_type', 
            course = '$course' 
          where id = '$id'";

$result = mysqli_query($db, $query);

if ($result) {
    $success = 'Staff record updated successfully !!!';
    // Clear variables out of execution scope safely before loading target view
    $staff_id = $firstname = $lastname = $email = $phone = $staff_type = $course = '';
    include('staff_list.php'); 
    exit;
} else {
    $error = 'An error occurred while updating the record: ' . mysqli_error($db);
    include('staff_list.php');
    exit;
}
?>