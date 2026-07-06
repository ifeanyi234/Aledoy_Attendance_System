<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

if (!isset($_SESSION['user_role']) || (int)$_SESSION['user_role'] !== 1) {
    die("Error: Access Denied. You do not have permission to modify data.");
}

require_once __DIR__ . '/connection/connect.php';

// Fetch staff details using the ID passed via the GET parameter
$staff_id_param = isset($_GET['id']) ? mysqli_real_escape_string($db, trim($_GET['id'])) : '';

$query_ed = "SELECT * FROM staff WHERE id = '$staff_id_param' OR staff_id = '$staff_id_param' LIMIT 1";
$result_ed = mysqli_query($db, $query_ed);
$row_ed = mysqli_fetch_assoc($result_ed);

// Manage Flash Notification pipelines natively
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

if (!$row_ed) {
    $_SESSION['error'] = 'Target staff record context not found or invalid parameter token supplied.';
    header("Location: staff_list.php");
    exit;
}

$staff_id   = $row_ed['staff_id'];
$firstname  = $row_ed['firstname'];
$lastname   = $row_ed['lastname'];
$email      = $row_ed['email'];
$phone      = $row_ed['phone']; // Raw combined string from database (e.g., +2348031234567)
$staff_type = strtolower(trim($row_ed['staff_type']));
$course     = $row_ed['course'];
$current_passport = $row_ed['passport_image'];

// --- REVERSE-ENGINEER PHONE STRING LOGIC ---
$selected_country_code = '+234'; // Default fallback
$phone_digits = '';


$phone = trim($phone ?? ''); 

$allowed_codes = ['+234', '+44', '+1', '+233', '+91', '+971'];
foreach ($allowed_codes as $code) {
    if (!empty($phone) && strpos($phone, $code) === 0) {
        $selected_country_code = $code;
        $phone_digits = substr($phone, strlen($code)); // Extract everything after the code
        break;
    }
}

// Fallback if the saved value has no matched country prefix
if (empty($phone_digits) && !empty($phone)) {
    $phone_digits = $phone;
}

// FIX: Strip ALL non-numeric characters (like + or spaces) so the HTML input element loads it safely
$phone_digits = preg_replace('/[^0-9]/', '', $phone_digits);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Staff Profile</title>
    <link rel="icon" href="../Images/icon.png" type="image/x-icon">
    <link href="css/style.min.css" rel="stylesheet">
</head>

<body>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        
        <?php include('includes/header.php'); ?>

        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar">
                <?php include('side-nav.php'); ?>
            </div>
        </aside>

        <div class="page-wrapper">
            <div class="page-breadcrumb bg-white">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title">Modify Staff Settings</h4>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <?php if (!empty($error)) echo '<div class="alert alert-danger" style="padding: 12px; border-radius:4px;"><i class="fas fa-exclamation-circle me-2"></i> ' . htmlspecialchars($error) . '</div>'; ?>
                <?php if (!empty($success)) echo '<div class="alert alert-success" style="padding: 12px; border-radius:4px;"><i class="fas fa-check-circle me-2"></i> ' . htmlspecialchars($success) . '</div>'; ?>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box" style="border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <h3 class="box-title mb-4">Update Profile Records</h3>
                            
                            <form action="proc-edit-staff.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" value="<?php echo htmlspecialchars($row_ed['id']); ?>" name="id">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Staff ID Reference <span class="text-danger">*</span></label>
                                         <input type="text" name="staff_id" class="form-control bg-light fw-bold text-primary" value="<?php echo htmlspecialchars($staff_id); ?>" readonly style="border-radius: 4px; font-size: 14px; letter-spacing: 0.5px;">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required style="border-radius: 4px;">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($firstname); ?>" required style="border-radius: 4px;">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($lastname); ?>" required style="border-radius: 4px;">
                                    </div>

                                    <!-- RE-ALIGNED PHONE FIELD WITH COUNTRY CODE PICKER -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <div class="input-group">
                                            <select name="country_code" class="form-control form-select text-center" style="max-width: 110px; background-color: #f8f9fa; border-radius: 4px 0 0 4px; font-size: 14px;">
                                                <option value="+234" <?php if($selected_country_code == '+234') echo 'selected'; ?>>🇳🇬 +234</option>
                                                <option value="+44" <?php if($selected_country_code == '+44') echo 'selected'; ?>>🇬🇧 +44</option>
                                                <option value="+1" <?php if($selected_country_code == '+1') echo 'selected'; ?>>🇺🇸 +1</option>
                                                <option value="+233" <?php if($selected_country_code == '+233') echo 'selected'; ?>>🇬🇭 +233</option>
                                                <option value="+91" <?php if($selected_country_code == '+91') echo 'selected'; ?>>🇮🇳 +91</option>
                                                <option value="+971" <?php if($selected_country_code == '+971') echo 'selected'; ?>>🇦🇪 +971</option>
                                            </select>
                                            <input type="tel" name="phone_digits" class="form-control" placeholder="e.g., 8031234567" pattern="[0-9]{5,15}" oninput="this.value = this.value.replace(/[^0-9]/g, '');" value="<?php echo htmlspecialchars($phone_digits); ?>" style="border-radius: 0 4px 4px 0; font-size: 14px;">
                                        </div>
                                        <small class="text-muted">Select code and enter digits without the leading 0.</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Staff Assignment Type <span class="text-danger">*</span></label>
                                        <select name="staff_type" class="form-control form-select" required style="border-radius: 4px;">
                                            <option value="main" <?php if($staff_type == 'main' || $staff_type == 'main staff') echo 'selected'; ?>>Main Staff</option>
                                            <option value="academy" <?php if($staff_type == 'academy') echo 'selected'; ?>>Academy</option>
                                            <option value="part-time" <?php if($staff_type == 'part-time') echo 'selected'; ?>>Part-Time</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-bold">Assigned Course Module</label>
                                        <select name="course" id="courseSelect" class="form-control form-select" style="border-radius: 4px;">
                                            <option value="" <?php echo empty($course) ? 'selected' : ''; ?>>-- Select Course --</option>
                                            <?php
                                            $course_query = mysqli_query($db, "SELECT course_name FROM courses ORDER BY course_name ASC");
                                            while ($c_row = mysqli_fetch_assoc($course_query)) {
                                                $c_name = $c_row['course_name'];
                                                $selected = ($course === $c_name) ? 'selected' : '';
                                                echo "<option value='" . htmlspecialchars($c_name) . "' $selected>" . htmlspecialchars($c_name) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <div class="p-3 d-flex align-items-center gap-3" style="border: 1px solid #e4e7ed; border-radius: 6px; background-color: #fafafa;">
                                            <?php if(!empty($current_passport) && file_exists(__DIR__ . '/' . $current_passport)): ?>
                                                <img src="<?php echo htmlspecialchars($current_passport); ?>" alt="Current" width="65" height="65" class="rounded border" style="object-fit: cover;">
                                            <?php endif; ?>
                                            <div class="flex-grow-1">
                                                <label class="form-label fw-bold mb-0">Replace Staff Passport Photograph</label>
                                                <p class="text-muted small mb-2">Leave unselected to keep current asset photograph. (Max: 3MB | JPG, PNG).</p>
                                                <input type="file" name="passport_image" class="form-control" accept=".jpg,.jpeg,.png" style="background-color: #ffffff;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <button type="submit" class="btn text-white px-4 py-2 fw-bold" style="background-color: #2F323E; border-radius:4px;">Save Staff Changes</button>
                                    <a href="staff_list.php" class="btn btn-secondary px-4 py-2 ms-2" style="border-radius:4px;">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer text-center"> 
                Copyright © <?php echo date('Y') ?> - Aledoy Solution Limited
            </footer>
        </div>
    </div>

    <script src="plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app-style-switcher.js"></script>
    <script src="js/waves.js"></script>
    <script src="js/sidebarmenu.js"></script>
    <script src="js/custom.js"></script>
    <?php include 'includes/current_dateTime.php'; ?>
</body>
</html>