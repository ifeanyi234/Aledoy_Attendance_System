<?php
session_start();
require_once __DIR__ . '/connection/connect.php';

if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

$error = '';
$success = '';

// Initialize form values to stay clean if submission fails
$staff_id = '';
$firstname = '';
$lastname = '';
$email = '';
$phone = '';
$staff_type = '';
$course = '';
$date_created = date('Y-m-d'); // Defaults to today's date

// Processing form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id    = mysqli_real_escape_string($db, trim($_POST['staff_id']));
    $firstname   = mysqli_real_escape_string($db, trim($_POST['firstname']));
    $lastname    = mysqli_real_escape_string($db, trim($_POST['lastname']));
    $email       = mysqli_real_escape_string($db, trim($_POST['email']));
    $phone       = mysqli_real_escape_string($db, trim($_POST['phone']));
    $staff_type  = mysqli_real_escape_string($db, trim($_POST['staff_type']));
    $course      = mysqli_real_escape_string($db, trim($_POST['course']));
    $date_created = mysqli_real_escape_string($db, trim($_POST['date_created']));

    $country_code = trim($_POST['country_code']);
    $phone_digits = trim($_POST['phone_digits']);
    
    if (!empty($phone_digits)) {
        $phone_digits = ltrim($phone_digits, '0');
        $phone = mysqli_real_escape_string($db, $country_code . $phone_digits);
    } else {
        $phone = '';
    }
    // ------------------------------------------

    $staff_type  = mysqli_real_escape_string($db, trim($_POST['staff_type']));

    if (empty($staff_id) || empty($firstname) || empty($lastname) || empty($email)) {
        $error = "Please fill in all mandatory fields (Staff ID, Names, and Email).";
    } else {
        $check_query = "SELECT id FROM staff WHERE staff_id = '$staff_id' OR email = '$email' LIMIT 1";
        $check_res = mysqli_query($db, $check_query);
        
        if (mysqli_num_rows($check_res) > 0) {
            $error = "A staff member with this Staff ID or Email already exists.";
        } else {
            // Insert data using matching field structures
            $insert_query = "INSERT INTO staff (staff_id, firstname, lastname, email, phone, staff_type, course, date_created) 
                             VALUES ('$staff_id', '$firstname', '$lastname', '$email', '$phone', '$staff_type', '$course', '$date_created')";
            
            if (mysqli_query($db, $insert_query)) {
                $success = "Staff member successfully registered!";
                // Reset form values on success
                $staff_id = $firstname = $lastname = $email = $phone = $staff_type = $course = '';
                $date_created = date('Y-m-d');
            } else {
                $error = "System Error: Could not save record. " . mysqli_error($db);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Add New Staff</title>

    <link rel="icon" href="../Aledoy-Attendance-system/Images/icon.png" type="image/x-icon">
    <link href="css/style.min.css" rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>

    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        
        <?php include('includes/header.php'); ?>

        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar">
                <?php include('side-nav.php'); ?>
            </div>
        </aside>

        <div class="page-wrapper" style="margin-top: 30px;">
            <div class="page-breadcrumb bg-white">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title">Register New Staff</h4>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <?php if (!empty($error)) echo '<div class="alert alert-danger">' . $error . '</div>'; ?>
                <?php if (!empty($success)) echo '<div class="alert alert-success">' . $success . '</div>'; ?>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box">
                            <form action="" method="post">
                                <div class="row">
                                    <!-- Staff ID Column -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Staff ID / Barcode <span class="text-danger">*</span></label>
                                        <input type="text" name="staff_id" class="form-control" placeholder="e.g., ALS-2026-001" value="<?php echo htmlspecialchars($staff_id); ?>" required>
                                    </div>
                                    <!-- Date Joined -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Date Joined</label>
                                        <input type="date" name="date_created" class="form-control" value="<?php echo htmlspecialchars($date_created); ?>">
                                    </div>
                                    
                                    <!-- First Name -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($firstname); ?>" required>
                                    </div>
                                    <!-- Last Name -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($lastname); ?>" required>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" placeholder="name@aledoy.com" value="<?php echo htmlspecialchars($email); ?>" required>
                                    </div>
                                    <!-- Phone Number -->
                                    <!-- Phone Number Column with International Dialing Codes -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <div class="input-group">
                                            <!-- Country Code Dropdown Menu -->
                                            <select name="country_code" class="form-control form-select text-center" style="max-width: 110px; background-color: #f8f9fa;">
                                                <option value="+234" selected>🇳🇬 +234</option>
                                                <option value="+44">🇬🇧 +44</option>
                                                <option value="+1">🇺🇸 +1</option>
                                                <option value="+233">🇬🇭 +233</option>
                                                <option value="+91">🇮🇳 +91</option>
                                                <option value="+971">🇦🇪 +971</option>
                                            </select>
                                            
                                            <!-- Raw digits field -->
                                            <input type="tel" name="phone_digits" class="form-control" 
                                                placeholder="e.g., 8031234567" 
                                                pattern="[0-9]{5,15}" 
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                        </div>
                                        <small class="text-muted">Select code and enter digits without the leading 0.</small>
                                    </div>

                                    <!-- Staff Type Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Staff Type</label>
                                        <select name="staff_type" class="form-control form-select">
                                            <option value="main" <?php if($staff_type == 'main') echo 'selected'; ?>>Main Staff</option>
                                            <option value="academy" <?php if($staff_type == 'academy') echo 'selected'; ?>>Academy</option>
                                            <option value="occasional" <?php if($staff_type == 'occasional') echo 'selected'; ?>>Occasional</option>
                                        </select>
                                    </div>
                                    <!-- Course / Department -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Assigned Course / Department</label>
                                        <input type="text" name="course" class="form-control" placeholder="e.g., PHP Development" value="<?php echo htmlspecialchars($course); ?>">
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-danger text-white px-4">Add Staff Member</button>
                                    <a href="staff_list.php" class="btn btn-secondary text-white px-4 ms-2">Back to List</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer text-center"> Copyright © <?php echo date('Y') ?> - All Rights Reserved Aledoy Solution Limited</footer>
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