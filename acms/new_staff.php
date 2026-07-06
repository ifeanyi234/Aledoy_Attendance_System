<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/plugins/phpqrcode/qrlib.php'; // Local QR engine dependency[cite: 8]
require_once __DIR__ . '/connection/connect.php'; 

if (!isset($_SESSION['acms_valid_user'])) { 
    include('index.php'); 
    exit; 
}

$error = ''; 
$success = ''; 

// Initialize default non-identity form structural elements[cite: 8]
$firstname = ''; 
$lastname = ''; 
$email = ''; 
$phone = ''; 
$staff_type = ''; 
$course = ''; 
$date_created = date('Y-m-d'); // Defaults to today's date[cite: 8]

// --- SEQUENTIAL IDENTITY SCANNER LOGIC BLOCK ---
$current_year = date('Y');
$id_prefix = "ALS-" . $current_year . "-";
$staff_id = '';

$counter = 1;
while (true) {
    // Left-pad the number sequence index to 3 characters (e.g., 001, 002)
    $padded_sequence = str_pad($counter, 3, '0', STR_PAD_LEFT);
    $candidate_id = $id_prefix . $padded_sequence;
    
    // Check if this specific candidate ID is already claimed in the database
    $check_seq = mysqli_query($db, "SELECT id FROM staff WHERE staff_id = '$candidate_id' LIMIT 1");
    if (mysqli_num_rows($check_seq) == 0) {
        // Free identity slot found! Assign it and exit loop execution
        $staff_id = $candidate_id;
        break;
    }
    $counter++;
}

// Processing form submission[cite: 8]
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // For absolute security, regenerate/re-verify code on server save step to avoid concurrent session conflicts
    $counter = 1;
    while (true) {
        $padded_sequence = str_pad($counter, 3, '0', STR_PAD_LEFT);
        $candidate_id = $id_prefix . $padded_sequence;
        $check_seq = mysqli_query($db, "SELECT id FROM staff WHERE staff_id = '$candidate_id' LIMIT 1");
        if (mysqli_num_rows($check_seq) == 0) {
            $staff_id = $candidate_id;
            break;
        }
        $counter++;
    }

    $firstname   = mysqli_real_escape_string($db, trim($_POST['firstname'])); 
    $lastname    = mysqli_real_escape_string($db, trim($_POST['lastname'])); 
    $email       = mysqli_real_escape_string($db, trim($_POST['email'])); 
    $staff_type  = mysqli_real_escape_string($db, trim($_POST['staff_type'])); 
    $course      = mysqli_real_escape_string($db, trim($_POST['course'])); 
    $date_created = mysqli_real_escape_string($db, trim($_POST['date_created'])); 

    // Phone parsing layer[cite: 8]
    $country_code = trim($_POST['country_code']); 
    $phone_digits = trim($_POST['phone_digits']); 
    if (!empty($phone_digits)) { 
        $phone_digits = ltrim($phone_digits, '0'); 
        $phone = mysqli_real_escape_string($db, $country_code . $phone_digits); 
    } else { 
        $phone = ''; 
    } 

    // Mandatory validation verification gates[cite: 8]
    if (empty($staff_id) || empty($firstname) || empty($lastname) || empty($email)) { 
        $error = "Please fill in all mandatory fields."; 
    } elseif (!isset($_FILES['passport_image']) || $_FILES['passport_image']['error'] == UPLOAD_ERR_NO_FILE) { 
        $error = "Please select and upload a valid Staff Passport Photo."; 
    } else { 
        // Double-check email identity parameters exclusively (ID checked by loop sequence)
        $check_query = "SELECT id FROM staff WHERE email = '$email' LIMIT 1"; 
        $check_res = mysqli_query($db, $check_query); 
        
        if (mysqli_num_rows($check_res) > 0) { 
            $error = "A staff member with this email address already exists."; 
        } else { 
            
            // --- PASSPORT IMAGE UPLOAD PROCESSING MODULE ---[cite: 8]
            $file_name = $_FILES['passport_image']['name']; 
            $file_tmp  = $_FILES['passport_image']['tmp_name']; 
            $file_size = $_FILES['passport_image']['size']; 
            
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); 
            $allowed_extensions = array("jpg", "jpeg", "png"); 
            
            if (!in_array($file_ext, $allowed_extensions)) { 
                $error = "Invalid image format. Only JPG, JPEG, and PNG files are permitted."; 
            } elseif ($file_size > 3145728) { // 3MB Maximum Limit boundary check[cite: 8]
                $error = "The uploaded file is too large. Maximum size boundary limit is 3MB."; 
            } else { 
                
                $passport_dir = __DIR__ . '/uploads/passports/'; 
                $qr_dir       = __DIR__ . '/qrcodes/'; 
                
                if (!file_exists($passport_dir)) mkdir($passport_dir, 0755, true); 
                if (!file_exists($qr_dir)) mkdir($qr_dir, 0755, true); 
                
                $clean_id_filename = preg_replace('/[^A-Za-z0-9_\-]/', '', $staff_id); 
                
                $passport_filename = $clean_id_filename . '.' . $file_ext; 
                $passport_target   = $passport_dir . $passport_filename; 
                
                $qr_filename       = $clean_id_filename . '.png'; 
                $qr_target         = $qr_dir . $qr_filename; 
                
                if (move_uploaded_file($file_tmp, $passport_target)) { 
                    
                    // --- LOCAL QR MATRIX GENERATOR PIPELINE ---[cite: 8]
                    QRcode::png($staff_id, $qr_target, QR_ECLEVEL_H, 6, 2); 
                    
                    $db_passport_path = 'uploads/passports/' . $passport_filename; 
                    $db_qr_path       = 'qrcodes/' . $qr_filename; 
                    
                    // --- DATABASE RECORD COMMITMENT ---[cite: 8]
                    $insert_query = "INSERT INTO staff (staff_id, firstname, lastname, email, phone, staff_type, course, passport_image, qr_code, date_created) 
                                     VALUES ('$staff_id', '$firstname', '$lastname', '$email', '$phone', '$staff_type', '$course', '$db_passport_path', '$db_qr_path', '$date_created')"; 
                    
                    if (mysqli_query($db, $insert_query)) { 
                        $success = "Staff member successfully registered with ID " . htmlspecialchars($staff_id) . ", passport processed, and identity QR compiled!";
                        
                        // Clear out form inputs for next cycle[cite: 8]
                        $firstname = $lastname = $email = $phone = $staff_type = $course = ''; 
                        $date_created = date('Y-m-d'); 
                        
                        // Recalculate immediate next available sequence token for seamless workflow
                        $counter = 1;
                        while (true) {
                            $padded_sequence = str_pad($counter, 3, '0', STR_PAD_LEFT);
                            $candidate_id = $id_prefix . $padded_sequence;
                            $check_seq = mysqli_query($db, "SELECT id FROM staff WHERE staff_id = '$candidate_id' LIMIT 1");
                            if (mysqli_num_rows($check_seq) == 0) {
                                $staff_id = $candidate_id;
                                break;
                            }
                            $counter++;
                        }
                    } else { 
                        $error = "Database Error: Could not commit row entry tracking data. " . mysqli_error($db); 
                    } 
                } else { 
                    $error = "System Write Exception: Failed to move uploaded passport to filesystem directory storage."; 
                } 
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
    <title>Add New Staff</title>
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
                        <h4 class="page-title">Register New Staff</h4>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <?php if (!empty($error)) echo '<div class="alert alert-danger" style="padding: 12px; border-radius: 4px; font-size: 14px;"><i class="fas fa-exclamation-circle me-2"></i> ' . $error . '</div>'; ?>
                <?php if (!empty($success)) echo '<div class="alert alert-success" style="padding: 12px; border-radius: 4px; font-size: 14px;"><i class="fas fa-check-circle me-2"></i> ' . $success . '</div>'; ?>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box" style="border-radius: 8px; border: none; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <form action="" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    <!-- Dynamic Auto-Generated Staff ID Column -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold text-dark">Staff ID</label>
                                        <input type="text" name="staff_id" class="form-control bg-light fw-bold text-primary" value="<?php echo htmlspecialchars($staff_id); ?>" readonly style="border-radius: 4px; font-size: 14px; letter-spacing: 0.5px;">
                                    </div>
                                    <!-- Date Joined -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Date Joined</label>
                                        <input type="date" name="date_created" class="form-control" value="<?php echo htmlspecialchars($date_created); ?>" style="border-radius: 4px; font-size: 14px;">
                                    </div>
                                    
                                    <!-- First Name -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($firstname); ?>" required style="border-radius: 4px; font-size: 14px;">
                                    </div>
                                    <!-- Last Name -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($lastname); ?>" required style="border-radius: 4px; font-size: 14px;">
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" placeholder="name@aledoy.com" value="<?php echo htmlspecialchars($email); ?>" required style="border-radius: 4px; font-size: 14px;">
                                    </div>
                                    <!-- Phone Number -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <div class="input-group">
                                            <select name="country_code" class="form-control form-select text-center" style="max-width: 110px; background-color: #f8f9fa; border-radius: 4px 0 0 4px; font-size: 14px;">
                                                <option value="+234" selected>🇳🇬 +234</option>
                                                <option value="+44">🇬🇧 +44</option>
                                                <option value="+1">🇺🇸 +1</option>
                                                <option value="+233">🇬🇭 +233</option>
                                                <option value="+91">🇮🇳 +91</option>
                                                <option value="+971">🇦🇪 +971</option>
                                            </select>
                                            <input type="tel" name="phone_digits" class="form-control" placeholder="e.g., 8031234567" pattern="[0-9]{5,15}" oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="border-radius: 0 4px 4px 0; font-size: 14px;">
                                        </div>
                                    </div>

                                    <!-- Staff Type Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Staff Type</label>
                                        <select name="staff_type" class="form-control form-select" style="border-radius: 4px; font-size: 14px;">
                                            <option value="main" <?php if($staff_type == 'main') echo 'selected'; ?>>Main Staff</option>
                                            <option value="academy" <?php if($staff_type == 'academy') echo 'selected'; ?>>Academy</option>
                                            <option value="part-time" <?php if($staff_type == 'part-time') echo 'selected'; ?>>Part-Time</option>
                                        </select>
                                    </div>
                                    <!-- Course / Department -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Assigned Course / Department</label>
                                        <select name="course" id="courseSelect" class="form-control form-select" style="border-radius: 4px; font-size: 14px;">
                                            <option value="" disabled <?php echo empty($course) ? 'selected' : ''; ?>>-- Select Course --</option>
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

                                    <div class="col-md-12 mb-2 mt-2">
                                        <div class="p-3" style="border: 2px dashed #e4e7ed; border-radius: 6px; background-color: #fafafa;">
                                            <label class="form-label fw-bold text-dark mb-1">Upload Official Staff Passport Photograph <span class="text-danger">*</span></label>
                                            <p class="text-muted small mb-2">Supported formats: JPG, JPEG, or PNG formats only. Ideal asset framing layout should be a 1:1 ratio block (Max file size: 3MB).</p>
                                            <input type="file" name="passport_image" class="form-control" accept=".jpg,.jpeg,.png" required style="font-size: 14px; background-color: #ffffff;">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 text-center">
                                    <button type="submit" class="btn text-white px-4 py-2.5 fw-bold" style="background-color: #2F323E; border-radius: 4px; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">Add Staff Member</button>
                                    <a href="staff_list.php" class="btn btn-secondary text-white px-4 py-2.5 ms-2" style="border-radius: 4px; font-size: 14px;">Back to List</a>
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