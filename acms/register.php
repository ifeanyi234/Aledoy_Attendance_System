<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/connection/connect.php';

// Access Control Gatekeeper: Verify user is authenticated
if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

// Access Control Gatekeeper: Verify authenticated user is a Main Admin (role = 1)
$current_user = mysqli_real_escape_string($db, $_SESSION['acms_valid_user']);
$role_check_query = "SELECT role FROM users WHERE username = '$current_user' LIMIT 1";
$role_result = mysqli_query($db, $role_check_query);
$user_role_data = mysqli_fetch_assoc($role_result);

if (!$user_role_data || (int)$user_role_data['role'] !== 1) {
    echo "<script>alert('Access Denied: Only the Main Admin can register sub-admin or staff accounts.'); window.location.href='dashboard.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aledoy Attendance - Register User</title>
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
            <div class="page-breadcrumb bg-white mb-4">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title">User Registration</h4>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-md-12 col-sm-12">
                        <div class="white-box shadow-sm" style="border-radius: 8px; border: none; padding: 30px;">
                            <div class="mb-4">
                                <h3 class="box-title mb-1 text-dark fw-bold">Create Dashboard Account</h3>
                                <p class="text-muted small mb-0">Provision a secure operational dashboard account profile for sub-admins or staff members.</p>
                            </div>
                            
                            <form action="proc-register.php" method="POST">
                                <?php if(isset($error) && !empty($error)){ ?>
                                    <div class="alert alert-danger" style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: 12px; border-radius: 4px; margin-bottom: 25px; font-size: 14px;"><i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?></div>
                                <?php } ?>
                              
                                <?php if(isset($success) && !empty($success)){ ?>
                                    <div class="alert alert-success" style="color: #155724; background-color: #d4edda; border-color: #c3e6cb; padding: 12px; border-radius: 4px; margin-bottom: 25px; font-size: 14px;"><i class="fas fa-check-circle me-2"></i> <?php echo $success; ?></div>
                                <?php } ?>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-semibold text-dark small">Account Username</label>
                                            <input type="text" class="form-control p-2.5" id="username" name="username" placeholder="Enter custom username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" style="border-radius: 4px; font-size: 14px; background-color: #fcfcfc;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-semibold text-dark small">Email Address</label>
                                            <input type="email" class="form-control p-2.5" id="email" name="email" placeholder="name@company.com" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" style="border-radius: 4px; font-size: 14px; background-color: #fcfcfc;">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-semibold text-dark small">Assign Account Authority Role</label>
                                            <select class="form-select p-2.5" name="role" required style="border-radius: 4px; font-size: 14px; color: <?php echo isset($role) && $role !== '' ? '#333' : '#757575'; ?>; background-color: #fcfcfc;" onchange="this.style.color = (this.value === '') ? '#757575' : '#333';">
                                                <option value="" <?php echo !isset($role) || $role === '' ? 'selected' : ''; ?> disabled hidden>Choose System Privilege ...</option>
                                                <option value="0" <?php echo (isset($role) && $role !== '' && (int)$role === 0) ? 'selected' : ''; ?> style="color: #333;">Regular Staff (Read-only Dashboard View Layout)</option>
                                                <option value="1" <?php echo (isset($role) && $role !== '' && (int)$role === 1) ? 'selected' : ''; ?> style="color: #333;">Main Admin (Full Console Access Control Authorization)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-semibold text-dark small">Account Password</label>
                                            <input type="password" class="form-control p-2.5" id="password" name="password" placeholder="••••••••" required style="border-radius: 4px; font-size: 14px; background-color: #fcfcfc;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label class="form-label fw-semibold text-dark small">Confirm Security Password</label>
                                            <input type="password" class="form-control p-2.5" id="confirm_password" name="confirm_password" placeholder="••••••••" required style="border-radius: 4px; font-size: 14px; background-color: #fcfcfc;">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-4 offset-md-4">
                                        <input type="submit" name="sub" class="btn text-white w-100 py-2.5 fw-bold" value="Provision Account" style="background-color: #2F323E; border-radius: 4px; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        
                                        <div class="text-center mt-3">
                                            <a href="dashboard.php" class="text-muted small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Return to Dashboard</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer text-center bg-transparent border-0 py-4 text-muted"> 
                Copyright © <?php echo date('Y') ?> - All Rights Reserved Aledoy Solution Limited
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