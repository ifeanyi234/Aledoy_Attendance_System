<?php
session_start();

require_once __DIR__ . '/connection/connect.php';

// Security check: Match your dashboard.php logic
if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

// Track privilege status layer parameter locally
$is_main_admin = (isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 1);

// Capture and sanitize active filters from URL parameters
$filter_course = isset($_GET['course']) ? mysqli_real_escape_string($db, trim($_GET['course'])) : '';
$filter_type   = isset($_GET['staff_type']) ? mysqli_real_escape_string($db, trim($_GET['staff_type'])) : '';

$query = "SELECT * FROM staff WHERE 1=1";

if (!empty($filter_course)) {
    $query .= " AND course = '$filter_course'";
}

if (!empty($filter_type)) {
    $query .= " AND staff_type = '$filter_type'";
}

$query .= " ORDER BY Id DESC";
$result = mysqli_query($db, $query);

?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Directory</title>
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

        <div class="page-wrapper" >
            <div class="page-breadcrumb bg-white">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title">Staff List</h4>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box">
                            <div class="d-md-flex align-items-center mb-3">
                                <h3 class="box-title mb-0">Staff Registry Directory</h3>
                            </div>
                            
                            <div class="card mb-4 bg-light p-3 border-0 shadow-sm" style="border-radius: 8px;">
                                <form method="GET" action="" class="row g-3 align-items-end">
                                    
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Filter by Course</label>
                                        <select name="course" class="form-select bg-white">
                                            <option value="">-- All Courses --</option>
                                            <?php
                                            $course_res = mysqli_query($db, "SELECT DISTINCT course FROM staff WHERE course IS NOT NULL AND course != '' ORDER BY course ASC");
                                            while($c_row = mysqli_fetch_assoc($course_res)) {
                                                $selected = ($filter_course === $c_row['course']) ? 'selected' : '';
                                                echo "<option value='".htmlspecialchars($c_row['course'])."' $selected>".htmlspecialchars($c_row['course'])."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Filter by Staff Type</label>
                                        <select name="staff_type" class="form-select bg-white">
                                            <option value="">-- All Types --</option>
                                            <?php
                                            $type_res = mysqli_query($db, "SELECT DISTINCT staff_type FROM staff WHERE staff_type IS NOT NULL AND staff_type != '' ORDER BY staff_type ASC");
                                            while($t_row = mysqli_fetch_assoc($type_res)) {
                                                $selected = ($filter_type === $t_row['staff_type']) ? 'selected' : '';
                                                echo "<option value='".htmlspecialchars($t_row['staff_type'])."' $selected>".htmlspecialchars($t_row['staff_type'])."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4 d-flex gap-2">
                                        <button type="submit" class="btn text-white w-100 fw-bold" style="background-color: #2F323E;">
                                            <i class="fa fa-search me-1"></i> Apply Filter
                                        </button>
                                        <?php if (!empty($filter_course) || !empty($filter_type)): ?>
                                            <a href="staff_list.php" class="btn btn-outline-secondary w-50">Clear</a>
                                        <?php endif; ?>
                                    </div>

                                </form>
                            </div>
                            <div class="table-responsive">
                                <table class="table text-nowrap table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th style="width: 60px; text-align: center;">Photo</th>
                                            <th>Staff ID</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Email Address</th>
                                            <th>Phone Number</th>
                                            <th>Staff Type</th>
                                            <th>Assigned Course</th>
                                            <th>Date Registered</th>
                                            <th style="width: 160px; text-align: center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $count = 1;
                                        if($result && mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)) { 
                                                
                                                if (!empty($row['date_created']) && $row['date_created'] != '0000-00-00 00:00:00') {
                                                    $date_formatted = date('d M Y', strtotime($row['date_created']));
                                                } else {
                                                    $date_formatted = '<span class="text-muted italic">Not Set</span>';
                                                }
                                                
                                                $staff_type_clean = strtolower(trim($row['staff_type']));
                                                if ($staff_type_clean === 'main' || $staff_type_clean === 'admin') {
                                                    $type_badge = '<span class="badge bg-primary text-white rounded-pill px-2.5 py-1">Main</span>';
                                                } else if($staff_type_clean === 'part-time' ) {
                                                    $type_badge = '<span class="badge bg-warning text-white rounded-pill px-2.5 py-1">Part-Time</span>';
                                                } else {
                                                    $type_badge = '<span class="badge bg-info text-white rounded-pill px-2.5 py-1">' . htmlspecialchars($row['staff_type']) . '</span>';
                                                }

                                                // Image Path Resolution Strategy
                                                $passport_photo = !empty($row['passport_image']) && file_exists(__DIR__ . '/' . $row['passport_image']) 
                                                    ? htmlspecialchars($row['passport_image']) 
                                                    : 'images/users/d1.jpg'; // Adjust fallback avatar path if needed
                                        ?>
                                            <tr>
                                                <td><?php echo $count++; ?></td>
                                                <!-- Passport Thumbnail Component Block -->
                                                <td class="text-center">
                                                    <img src="<?php echo $passport_photo; ?>" alt="Passport" width="40" height="40" class="rounded-circle border" style="object-fit: cover; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                                </td>
                                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['staff_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                                <td><span class="text-muted"><?php echo htmlspecialchars($row['email']); ?></span></td>
                                                <td><?php echo htmlspecialchars($row['phone'] ?: 'N/A'); ?></td>
                                                <td><?php echo $type_badge; ?></td>
                                                <td><?php echo htmlspecialchars($row['course'] ?: 'N/A'); ?></td>
                                                <td><?php echo $date_formatted; ?></td>
                                                
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                                        <!-- Identity Card Presentation Bridge View Layer -->
                                                        <a href="view_id.php?id=<?php echo $row['id']; ?>" class="btn btn-sm text-white px-2.5 py-1" style="background-color: #2F323E; font-size: 12px; border-radius: 4px;" title="Print Identity Badge">
                                                            <i class="fa fa-id-card me-1"></i> ID Card
                                                        </a>

                                                        <?php if ($is_main_admin): ?>
                                                            <a class="p-1" href="edit-staff.php?id=<?php echo $row['id']; ?>" title="Edit Profile" style="font-size: 16px;">
                                                                <i class="fa fa-edit text-secondary"></i>
                                                            </a>
                                                            <a class="p-1" href="delete-staff.php?id=<?php echo $row['id']; ?>" title="Remove Staff" onclick="return confirm('Are you sure you want to permanently remove this staff member from the system?');" style="font-size: 16px;">
                                                                <i class="fa fa-trash text-danger"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } 
                                        } else { ?>
                                            <tr>
                                                <td colspan="11" class="text-center text-muted py-4">
                                                    No registered staff records found matching selection. <a href="new_staff.php" class="fw-bold text-success ms-1">Add Staff Member</a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
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