<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/plugins/phpqrcode/qrlib.php'; // QR Engine
require_once __DIR__ . '/connection/connect.php';
require_once __DIR__ . '/fns.php';

$date_added = date('Y-m-d H:i:s');

$staff_id_raw    = $_POST['staff_id'] ?? '';
$firstname_raw   = $_POST['firstname'] ?? '';
$lastname_raw    = $_POST['lastname'] ?? '';
$email_raw       = $_POST['email'] ?? '';
$country_code    = $_POST['country_code'] ?? '+234';
$phone_digits    = $_POST['phone_digits'] ?? '';
$staff_type_raw  = $_POST['staff_type'] ?? 'main';
$course_raw      = $_POST['course'] ?? '';
$date_joined_raw = $_POST['date_created'] ?? '';

$phone_combined_raw = '';
if (!empty($phone_digits)) {
    $clean_digits = ltrim(trim($phone_digits), '0');
    $phone_combined_raw = trim($country_code) . $clean_digits;
}

$staff_id = mysqli_real_escape_string($db, trim($staff_id_raw));
$firstname = mysqli_real_escape_string($db, trim($firstname_raw));
$lastname = mysqli_real_escape_string($db, trim($lastname_raw));
$email = mysqli_real_escape_string($db, trim($email_raw));
$phone = mysqli_real_escape_string($db, $phone_combined_raw);
$staff_type  = mysqli_real_escape_string($db, trim($staff_type_raw));
$course = mysqli_real_escape_string($db, trim($_POST['course'] ?? ''));
$date_joined = mysqli_real_escape_string($db, trim($date_joined_raw));

if (empty($date_joined)) {
    $date_joined = date('Y-m-d');
}

// Preserve form state so the admin doesn't retype everything on failure
$form_state_data = [
    'staff_id'     => $staff_id_raw,
    'firstname'    => $firstname_raw,
    'lastname'     => $lastname_raw,
    'email'        => $email_raw,
    'country_code' => $country_code,
    'phone_digits' => $phone_digits,
    'staff_type'   => $staff_type_raw,
    'course'       => $course_raw,
    'date_created' => $date_joined_raw
];

// Mandatory Text Validations
if ($staff_id === '' || $firstname === '' || $lastname === '' || $email === '') {
    $_SESSION['error'] = 'All required fields marked with * must be filled!';
    $_SESSION['form_data'] = $form_state_data;
    header('Location: new_staff.php');
    exit;
}

// Passport File Validation Gate
if (!isset($_FILES['passport_image']) || $_FILES['passport_image']['error'] == UPLOAD_ERR_NO_FILE) {
    $_SESSION['error'] = 'Please select and upload a valid Staff Passport Photo.';
    $_SESSION['form_data'] = $form_state_data;
    header('Location: new_staff.php');
    exit;
}

// Uniqueness Check
$check_query = "SELECT id FROM staff WHERE staff_id = '$staff_id' OR email = '$email' LIMIT 1";
$check_result = mysqli_query($db, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    $_SESSION['error'] = 'A staff member with this Staff ID or Email is already registered!';
    $_SESSION['form_data'] = $form_state_data;
    header('Location: new_staff.php');
    exit;
}

// --- 4. PASSPORT IMAGE PROCESSING ---
$file_name = $_FILES['passport_image']['name'];
$file_tmp  = $_FILES['passport_image']['tmp_name'];
$file_size = $_FILES['passport_image']['size'];
$file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
$allowed_extensions = array("jpg", "jpeg", "png");

if (!in_array($file_ext, $allowed_extensions)) {
    $_SESSION['error'] = 'Invalid image format. Only JPG, JPEG, and PNG files are permitted.';
    $_SESSION['form_data'] = $form_state_data;
    header('Location: new_staff.php');
    exit;
}

if ($file_size > 3145728) { // 3MB Limit
    $_SESSION['error'] = 'The uploaded passport image is too large. Max size limit is 3MB.';
    $_SESSION['form_data'] = $form_state_data;
    header('Location: new_staff.php');
    exit;
}

$passport_dir = __DIR__ . '/uploads/passports/';
$qr_dir       = __DIR__ . '/qrcodes/';

if (!file_exists($passport_dir)) mkdir($passport_dir, 0755, true);
if (!file_exists($qr_dir)) mkdir($qr_dir, 0755, true);

// Clean up filename from unexpected special characters
$clean_id_filename = preg_replace('/[^A-Za-z0-9_\-]/', '', $staff_id);
$passport_filename = $clean_id_filename . '.' . $file_ext;
$qr_filename       = $clean_id_filename . '.png';

if (move_uploaded_file($file_tmp, $passport_dir . $passport_filename)) {
    
    // --- AUTOMATED QR COMPILATION ---
    $qr_target = $qr_dir . $qr_filename;
    QRcode::png($staff_id, $qr_target, 'H', 6, 2);
    
    $db_passport_path = 'uploads/passports/' . $passport_filename;
    $db_qr_path       = 'qrcodes/' . $qr_filename;
    
    // --- DATABASE COMMIT ---
    $query = "INSERT INTO staff (staff_id, firstname, lastname, email, phone, staff_type, course, date_created, passport_image, qr_code) 
              VALUES ('$staff_id', '$firstname', '$lastname', '$email', '$phone', '$staff_type', '$course', '$date_joined', '$db_passport_path', '$db_qr_path')";
    
    if (mysqli_query($db, $query)) {
        $_SESSION['success'] = 'Staff member successfully registered, passport uploaded, and identity QR generated!';
        unset($_SESSION['form_data']); 
        header('Location: new_staff.php');
        exit;
    } else {
        $_SESSION['error'] = 'Database System Error: Could not save records. ' . mysqli_error($db);
        $_SESSION['form_data'] = $form_state_data;
        header('Location: new_staff.php');
        exit;
    }
} else {
    $_SESSION['error'] = 'System Write Failure: Failed to save the passport image to the server storage directory.';
    $_SESSION['form_data'] = $form_state_data;
    header('Location: new_staff.php');
    exit;
}
?>