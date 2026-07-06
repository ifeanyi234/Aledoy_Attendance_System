<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load the database connection so header.php can read user parameters
require_once __DIR__ . '/connection/connect.php';

if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

// Only initialize if they aren't already set by the processing script
$error = isset($error) ? $error : '';
$success = isset($success) ? $success : '';
$old_password = isset($old_password) ? $old_password : '';
$new_password = isset($new_password) ? $new_password : '';
$confirm_password = isset($confirm_password) ? $confirm_password : '';
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Change Password</title>
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
                        <h4 class="page-title">Security Settings</h4>
                    </div>
                </div>
            </div>
            
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-md-12 col-sm-12">
                        <div class="white-box shadow-sm" style="border-radius: 8px; border: none; padding: 30px;">
                            <div class="mb-4">
                                <h3 class="box-title mb-1 text-dark fw-bold">Update Account Password</h3>
                                <p class="text-muted small mb-0">Modify your login credentials. Ensure your new secret key meets systemic complexity policies.</p>
                            </div>

                            <?php if(!empty($error)){ ?>
                                <div class="alert alert-danger" style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: 12px; border-radius: 4px; margin-bottom: 25px; font-size: 14px;"><i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?></div>
                            <?php } ?>
                          
                            <?php if(!empty($success)){ ?>
                                <div class="alert alert-success" style="color: #155724; background-color: #d4edda; border-color: #c3e6cb; padding: 12px; border-radius: 4px; margin-bottom: 25px; font-size: 14px;"><i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success); ?></div>
                            <?php } ?>

                            <form method="post" action="proc-change-password.php">
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group mb-4">
                                            <label class="form-label fw-semibold text-dark small">Current Password</label>
                                            <input name="old_password" type="password" class="form-control p-2.5" placeholder="••••••••" required style="border-radius: 4px; font-size: 14px; background-color: #fcfcfc;">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label class="form-label fw-semibold text-dark small">New Security Password</label>
                                            <input name="new_password" type="password" class="form-control p-2.5" placeholder="••••••••" required style="border-radius: 4px; font-size: 14px; background-color: #fcfcfc;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label class="form-label fw-semibold text-dark small">Confirm New Password</label>
                                            <input name="confirm_password" type="password" class="form-control p-2.5" placeholder="••••••••" required style="border-radius: 4px; font-size: 14px; background-color: #fcfcfc;">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-md-4 offset-md-4">
                                        <button name="change" type="submit" class="btn text-white w-100 py-2.5 fw-bold" style="background-color: #2F323E; border-radius: 4px; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            Commit New Credentials
                                        </button>
                                        
                                        <div class="text-center mt-3">
                                            <a href="dashboard.php" class="text-muted small text-decoration-none"><i class="fas fa-arrow-left me-1"></i> Cancel & Return</a>
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