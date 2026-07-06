<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

if (!isset($_SESSION['user_role']) || (int)$_SESSION['user_role'] !== 1) {
    $_SESSION['error'] = "Unauthorized system modifications blocked.";
    header("Location: staff_list.php");
    exit;
}

require_once __DIR__ . '/plugins/phpqrcode/qrlib.php'; // Engine load boundary link
require_once __DIR__ . '/connection/connect.php';

$id         = (int)($_POST['id'] ?? 0);
$staff_id   = mysqli_real_escape_string($db, trim($_POST['staff_id'] ?? ''));
$firstname  = mysqli_real_escape_string($db, trim($_POST['firstname'] ?? ''));
$lastname   = mysqli_real_escape_string($db, trim($_POST['lastname'] ?? ''));
$email      = mysqli_real_escape_string($db, trim($_POST['email'] ?? ''));
$staff_type = mysqli_real_escape_string($db, trim($_POST['staff_type'] ?? 'main'));
$course     = mysqli_real_escape_string($db, trim($_POST['course'] ?? ''));

// Form sends these as separate pieces, so we capture and combine them natively
$country_code = trim($_POST['country_code'] ?? '+234');
$phone_digits = trim($_POST['phone_digits'] ?? '');

if (!empty($phone_digits)) {
    // Strip out any accidental leading zeros typed by the user
    $phone_digits = ltrim($phone_digits, '0');
    $phone = mysqli_real_escape_string($db, $country_code . $phone_digits);
} else {
    $phone = '';
}
// -------------------------------------

if ($id <= 0 || empty($staff_id) || empty($firstname) || empty($lastname) || empty($email)) {
    $_SESSION['error'] = "Validation Failure: All required elements must be completely populated.";
    header("Location: edit-staff.php?id=" . $id);
    exit;
}

// Check uniqueness across other profiles
$unique_check = mysqli_query($db, "SELECT id FROM staff WHERE (staff_id = '$staff_id' OR email = '$email') AND id != $id LIMIT 1");
if (mysqli_num_rows($unique_check) > 0) {
    $_SESSION['error'] = "The updated Staff ID or Email Address is already allocated to another profile.";
    header("Location: edit-staff.php?id=" . $id);
    exit;
}

// Pull historical state records
$old_res = mysqli_query($db, "SELECT staff_id, passport_image, qr_code FROM staff WHERE id = $id LIMIT 1");
$old_data = mysqli_fetch_assoc($old_res);

$db_passport_path = $old_data['passport_image'] ?? '';
$db_qr_path       = $old_data['qr_code'] ?? '';

$clean_filename_base = preg_replace('/[^A-Za-z0-9_\-]/', '', $staff_id);
$passport_dir = __DIR__ . '/uploads/passports/';
$qr_dir       = __DIR__ . '/qrcodes/';

// Handle Passport Image Uploads
if (isset($_FILES['passport_image']) && $_FILES['passport_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file_tmp  = $_FILES['passport_image']['tmp_name'];
    $file_size = $_FILES['passport_image']['size'];
    $file_ext  = strtolower(pathinfo($_FILES['passport_image']['name'], PATHINFO_EXTENSION));
    
    if (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
        if ($file_size <= 3145728) {
            // Delete old file footprint
            if (!empty($db_passport_path) && file_exists(__DIR__ . '/' . $db_passport_path)) {
                @unlink(__DIR__ . '/' . $db_passport_path);
            }
            
            $new_passport_name = $clean_filename_base . '_' . time() . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $passport_dir . $new_passport_name)) {
                $db_passport_path = 'uploads/passports/' . $new_passport_name;
            }
        } else {
            $_SESSION['error'] = "The new image file exceeds the maximum 3MB file size limitation.";
            header("Location: edit-staff.php?id=" . $id);
            exit;
        }
    } else {
        $_SESSION['error'] = "Unsupported file configuration format. Please select a valid JPG, JPEG, or PNG asset.";
        header("Location: edit-staff.php?id=" . $id);
        exit;
    }
}

if ($old_data['staff_id'] !== $staff_id || empty($db_qr_path) || !file_exists(__DIR__ . '/' . $db_qr_path)) {
    if (!empty($db_qr_path) && file_exists(__DIR__ . '/' . $db_qr_path)) {
        @unlink(__DIR__ . '/' . $db_qr_path);
    }
    
    $new_qr_filename = $clean_filename_base . '.png';
    QRcode::png($staff_id, $qr_dir . $new_qr_filename, 'H', 6, 2);
    $db_qr_path = 'qrcodes/' . $new_qr_filename;
}

$update_query = "UPDATE staff SET 
                    staff_id = '$staff_id', 
                    firstname = '$firstname', 
                    lastname = '$lastname', 
                    email = '$email', 
                    phone = '$phone', 
                    staff_type = '$staff_type', 
                    course = '$course',
                    passport_image = '$db_passport_path',
                    qr_code = '$db_qr_path'
                 WHERE id = $id";

if (mysqli_query($db, $update_query)) {
    $_SESSION['success'] = "Staff identity registration profile records successfully saved.";
} else {
    $_SESSION['error'] = "Database Transaction Defect: Modification aborted. " . mysqli_error($db);
}

header("Location: staff_list.php");
exit;
?>