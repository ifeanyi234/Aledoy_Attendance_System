<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/connection/connect.php';
require_once __DIR__ . '/fns.php';

$date_added = date('Y-m-d H:i:s');

// Grab raw inputs to keep form re-populated on failure
$staff_id_raw    = $_POST['staff_id'] ?? '';
$firstname_raw   = $_POST['firstname'] ?? '';
$lastname_raw    = $_POST['lastname'] ?? '';
$email_raw       = $_POST['email'] ?? '';
$country_code    = $_POST['country_code'] ?? '+234';
$phone_digits    = $_POST['phone_digits'] ?? '';
$staff_type_raw  = $_POST['staff_type'] ?? 'main';
$course_raw      = $_POST['course'] ?? '';
$date_joined_raw = $_POST['date_joined'] ?? '';

// Handle Phone Number Combination & Leading Zero Trimming
$phone_combined_raw = '';
if (!empty($phone_digits)) {
    // Strip accidental leading zero (e.g., converts '0803...' to '803...')
    $clean_digits = ltrim(trim($phone_digits), '0');
    $phone_combined_raw = trim($country_code) . $clean_digits;
}

// Sanitize fields for safe SQL queries
$staff_id    = mysqli_real_escape_string($db, trim($staff_id_raw));
$firstname   = mysqli_real_escape_string($db, trim($firstname_raw));
$lastname    = mysqli_real_escape_string($db, trim($lastname_raw));
$email       = mysqli_real_escape_string($db, trim($email_raw));
$phone       = mysqli_real_escape_string($db, $phone_combined_raw);
$staff_type  = mysqli_real_escape_string($db, trim($staff_type_raw));
$course      = mysqli_real_escape_string($db, trim($course_raw));
$date_joined = mysqli_real_escape_string($db, trim($date_joined_raw));

// If no date was assigned, fall back to today's date
if (empty($date_joined)) {
    $date_joined = date('Y-m-d');
}

// Prepare arrays for form state tracking on error redirect
$form_state_data = [
    'staff_id'     => $staff_id_raw,
    'firstname'    => $firstname_raw,
    'lastname'     => $lastname_raw,
    'email'        => $email_raw,
    'country_code' => $country_code,
    'phone_digits' => $phone_digits,
    'staff_type'   => $staff_type_raw,
    'course'       => $course_raw,
    'date_joined'  => $date_joined_raw
];

// Validation: Check required fields
if ($staff_id === '' || $firstname === '' || $lastname === '' || $email === '') {
    $_SESSION['error'] = 'All required fields marked with * must be filled!';
    $_SESSION['form_data'] = $form_state_data;
    header('Location: new_staff.php');
    exit;
}

// Validation: Check if Staff ID or Email already exists to prevent duplicate keys
$check_query = "SELECT id FROM staff WHERE staff_id = '$staff_id' OR email = '$email' LIMIT 1";
$check_result = mysqli_query($db, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    $_SESSION['error'] = 'A staff member with this Staff ID or Email is already registered!';
    $_SESSION['form_data'] = $form_state_data;
    header('Location: new_staff.php');
    exit;
}

// Insert into your updated staff registry table
$query = "INSERT INTO staff (staff_id, firstname, lastname, email, phone, staff_type, course, date_joined, created_at) 
          VALUES ('$staff_id', '$firstname', '$lastname', '$email', '$phone', '$staff_type', '$course', '$date_joined', '$date_added')";

$result = mysqli_query($db, $query);

if ($result) {
    $_SESSION['success'] = 'Staff member has been added successfully!';
    // Unset old data if successfully inserted so form resets next time
    unset($_SESSION['form_data']); 
    header('Location: new_staff.php');
    exit;
} else {
    $_SESSION['error'] = 'An unexpected database system error occurred.';
    $_SESSION['form_data'] = $form_state_data;
    header('Location: new_staff.php');
    exit;
}
?>