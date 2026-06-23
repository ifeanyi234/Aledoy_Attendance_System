<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

// Explicit Connection Include Requirement
require_once __DIR__ . '/connection/connect.php';

// Fetch staff details using the ID passed via the GET parameter
$staff_id_param = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : '';

$query_ed = "SELECT * FROM staff WHERE id = '$staff_id_param' OR staff_id = '$staff_id_param' LIMIT 1";
$result_ed = mysqli_query($db, $query_ed);
$row_ed = mysqli_fetch_assoc($result_ed);

$error = '';
$success = '';

if (!$row_ed) {
    $error = 'Staff record not found or invalid parameter token supplied.';
    $staff_id = $firstname = $lastname = $email = $phone = $staff_type = $course = '';
} else {
    $staff_id   = $row_ed['staff_id'];
    $firstname  = $row_ed['firstname'];
    $lastname   = $row_ed['lastname'];
    $email      = $row_ed['email'];
    $phone      = $row_ed['phone'];
    $staff_type = $row_ed['staff_type'];
    $course     = $row_ed['course'];
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Edit Staff Profile</title>

    <!-- Favicon icon -->
    <link rel="icon" href="../Aledoy-Attendance-system/Images/icon.png" type="image/x-icon">
    <!-- Custom CSS -->
    <link href="css/style.min.css" rel="stylesheet">
    
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
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
        
        <!-- Topbar header -->
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin6">
                    <a class="navbar-brand" href="dashboard.php">
                        <b class="logo-icon">
                            <img src="../Aledoy-Attendance-system/Images/images-removebg-preview.png" style="max-width:120px; height:auto;" alt="homepage" />
                        </b>
                    </a>
                    <a class="nav-toggler waves-effect waves-light text-dark d-block d-md-none" href="javascript:void(0)">
                        <i class="ti-menu ti-close"></i>
                    </a>
                </div>
                
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <ul class="navbar-nav d-none d-md-block d-lg-none">
                        <li class="nav-item">
                            <a class="nav-toggler nav-link waves-effect waves-light text-white" href="javascript:void(0)">
                                <i class="ti-menu ti-close"></i>
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav d-flex align-items-center">
                        <li>
                            <a class="profile-pic" href="#">
                                <img src="plugins/images/users/varun.jpg" alt="user-img" width="36" class="img-circle">
                            </a>
                        </li>
                        <li class="ms-auto">
                            <span class="text-white font-medium" id="current-time"></span>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- Left Sidebar -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar">
                <?php include('side-nav.php'); ?>
            </div>
        </aside>

        <!-- Page wrapper -->
        <div class="page-wrapper" style="margin-top: 30px;">
            <div class="page-breadcrumb bg-white">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title">Edit Staff Member</h4>
                    </div>
                </div>
            </div>

            <!-- Container fluid -->
            <div class="container-fluid">
                <?php if (!empty($error)) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; ?>
                <?php if (!empty($success)) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>'; ?>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box">
                            <h3 class="box-title mb-4">Update Profile Records</h3>
                            
                            <div class="row">
                                <div class="col-md-8 col-lg-6">
                                    <form action="proc-edit-staff.php" method="post" enctype="multipart/form-data">
                                        <!-- Hidden Input carrying primary target identity payload token safely -->
                                        <input type="hidden" value="<?php echo htmlspecialchars($row_ed['id'] ?? ''); ?>" name="id">

                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Staff ID Reference</label>
                                            <input type="text" name="staff_id" class="form-control" value="<?php echo htmlspecialchars($staff_id); ?>" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">First Name</label>
                                            <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($firstname); ?>" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Last Name</label>
                                            <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($lastname); ?>" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Email Address</label>
                                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Phone Number</label>
                                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                                        </div>

                                        <!-- FIXED STAFF ROLES DROPDOWN -->
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Staff Role Type</label>
                                            <select name="staff_type" class="form-control form-select" required>
                                                <option value="">Select Assignment Level</option>
                                                <option value="main staff" <?php if(strtolower(trim($staff_type)) == 'main staff') echo 'selected'; ?>>Main Staff</option>
                                                <option value="academy" <?php if(strtolower(trim($staff_type)) == 'academy') echo 'selected'; ?>>Academy</option>
                                                <option value="occasional" <?php if(strtolower(trim($staff_type)) == 'occasional') echo 'selected'; ?>>Occasional</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label class="form-label fw-bold">Assigned Course Module</label>
                                            <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($course); ?>">
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-success text-white px-4">Save Profile Changes</button>
                                            <a href="staff-list.php" class="btn btn-secondary px-4 ms-2">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer text-center"> 
                Copyright © <?php echo date('Y') ?> - All Rights Reserved Aledoy Solution Limited
            </footer>
        </div>
    </div>

    <!-- All Jquery & Bootstrap Core Assets -->
    <script src="plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app-style-switcher.js"></script>
    <script src="js/waves.js"></script>
    <script src="js/sidebarmenu.js"></script>
    <script src="js/custom.js"></script>

    <!-- Explicit Live Dynamic Date-Time Processing Include -->
    <?php include 'includes/current_dateTime.php'; ?>
</body>

</html>