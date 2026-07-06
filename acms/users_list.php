<?php
session_start();
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
    echo "<script>alert('Access Denied: Only Main Admins can access the User Management Station.'); window.location.href='dashboard.php';</script>";
    exit;
}

$error = '';
$success = '';

// --- ADMINISTRATIVE ACTION ENGINE ---

//  Handle Role Toggle Requests (Promote to Admin / Demote to Staff)
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $target_id = (int)$_GET['id'];
    
    // Fetch target user credentials to check identity
    $user_check = mysqli_query($db, "SELECT username, role FROM users WHERE id = $target_id LIMIT 1");
    if (mysqli_num_rows($user_check) > 0) {
        $target_profile = mysqli_fetch_assoc($user_check);
        
        // Safety Lock: Prevent current user from demoting themselves
        if ($target_profile['username'] === $_SESSION['acms_valid_user']) {
            $error = "Administrative Action Denied: You cannot change your own role to prevent losing access.";
        } else {
            $new_role = ((int)$target_profile['role'] === 1) ? 0 : 1;
            $update_query = "UPDATE users SET role = $new_role WHERE id = $target_id";
            if (mysqli_query($db, $update_query)) {
                $success = "Privilege privileges for '<strong>" . htmlspecialchars($target_profile['username']) . "</strong>' updated successfully.";
            } else {
                $error = "Database synchronization error. Please try again.";
            }
        }
    }
}

// 2. Handle Dashboard Account Revocation (Delete User)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $target_id = (int)$_GET['id'];
    
    $user_check = mysqli_query($db, "SELECT username FROM users WHERE id = $target_id LIMIT 1");
    if (mysqli_num_rows($user_check) > 0) {
        $target_profile = mysqli_fetch_assoc($user_check);
        
        // Safety Lock: Prevent current user from deleting themselves
        if ($target_profile['username'] === $_SESSION['acms_valid_user']) {
            $error = "Administrative Action Denied: You cannot delete your own active administrative session profile.";
        } else {
            $delete_query = "DELETE FROM users WHERE id = $target_id";
            if (mysqli_query($db, $delete_query)) {
                $success = "Dashboard account access for '<strong>" . htmlspecialchars($target_profile['username']) . "</strong>' has been permanently revoked.";
            } else {
                $error = "Database execution error. Please try again.";
            }
        }
    }
}

// Fetch all registered dashboard accounts
$query = "SELECT id, username, email, role FROM users ORDER BY id DESC";
$result = mysqli_query($db, $query);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Access & User Control Station</title>
    <link rel="icon" href="../Images/icon.png" type="image/x-icon">
    <link href="css/style.min.css" rel="stylesheet">
</head>

<body>
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        
        <!-- Top Navigation Bar -->
        <?php include('includes/header.php'); ?>
        
        <!-- Left Side Navigation -->
        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar">
                <?php include('side-nav.php'); ?>
            </div>
        </aside>

        <div class="page-wrapper">
            <div class="page-breadcrumb bg-white">
                <div class="row align-items-center">
                    <div class="col-lg-5 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title">User Account Control Console</h4>
                    </div>
                    <div class="col-lg-7 col-sm-8 col-md-8 col-xs-12">
                        <div class="d-md-flex justify-content-end">
                            <a href="register.php" class="btn text-white" style="background-color: #2F323E; font-weight: 500; border-radius: 4px;">
                                <i class="fas fa-user-plus me-1"></i> Register New User Account
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box">
                            <div class="d-md-flex align-items-center mb-3">
                                <h3 class="box-title mb-0">Active System Users</h3>
                            </div>
                            
                            <!-- Context Alerts Feedback Layer -->
                            <?php if(!empty($error)){ ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
                                </div>
                            <?php } ?>
                            
                            <?php if(!empty($success)){ ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert" style="color: #155724; background-color: #d4edda; border-color: #c3e6cb; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                                </div>
                            <?php } ?>

                            <div class="table-responsive shadow-sm rounded">
                                <table class="table text-nowrap table-bordered table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th>Dashboard Username</th>
                                            <th>Email Address</th>
                                            <th>Authorization Access Rank</th>
                                            <th style="width: 180px; text-align: center;">Control Management Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $count = 1;
                                        if($result && mysqli_num_rows($result) > 0) {
                                            while($row = mysqli_fetch_assoc($result)) { 
                                                $is_row_self = ($row['username'] === $_SESSION['acms_valid_user']);
                                                
                                                // Dynamic UI Badging System
                                                if ((int)$row['role'] === 1) {
                                                    $role_badge = '<span class="badge bg-danger text-white rounded-pill px-3 py-1.5"><i class="fas fa-user-shield me-1"></i> Main Admin</span>';
                                                    $toggle_title = "Demote to Regular Staff";
                                                    $toggle_btn_class = "btn-outline-warning";
                                                    $toggle_icon = "fa-user-minus";
                                                } else {
                                                    $role_badge = '<span class="badge bg-secondary text-white rounded-pill px-3 py-1.5"><i class="fas fa-user me-1"></i> Staff Member</span>';
                                                    $toggle_title = "Promote to Main Admin";
                                                    $toggle_btn_class = "btn-outline-success";
                                                    $toggle_icon = "fa-user-plus";
                                                }
                                        ?>
                                            <tr <?php if($is_row_self) echo 'style="background-color: #f7fafc;"'; ?>>
                                                <td><?php echo $count++; ?></td>
                                                <td class="fw-bold text-dark">
                                                    <?php echo htmlspecialchars($row['username']); ?>
                                                    <?php if($is_row_self): ?>
                                                        <span class="text-muted small fw-normal ms-1 italic">(You)</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="text-muted"><?php echo htmlspecialchars($row['email']); ?></span></td>
                                                <td><?php echo $role_badge; ?></td>
                                                
                                                <td class="text-center">
                                                    <div class="btn-group gap-2" role="group">
                                                        <!-- Privilege Toggle Control Trigger -->
                                                        <a href="users_list.php?action=toggle&id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-sm <?php echo $toggle_btn_class; ?> d-inline-flex align-items-center"
                                                           title="<?php echo $toggle_title; ?>"
                                                           <?php if($is_row_self) echo 'style="pointer-events: none; opacity: 0.5;" disabled'; ?>
                                                           onclick="return confirm('Are you sure you want to change the authorization permissions for this account?');">
                                                            <i class="fa <?php echo $toggle_icon; ?> me-1"></i> Role
                                                        </a>
                                                        
                                                        <!-- System Access Revocation Control Trigger -->
                                                        <a href="users_list.php?action=delete&id=<?php echo $row['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger d-inline-flex align-items-center" 
                                                           title="Revoke System Access"
                                                           <?php if($is_row_self) echo 'style="pointer-events: none; opacity: 0.5;" disabled'; ?>
                                                           onclick="return confirm('CRITICAL WARNING: Are you sure you want to permanently erase this user\'s dashboard login privileges? This action cannot be undone.');">
                                                            <i class="fa fa-trash me-1"></i> Revoke
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } 
                                        } else { ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No logged records found in the users table.</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
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