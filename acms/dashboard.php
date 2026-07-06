<?php
session_start();
require_once __DIR__ . '/connection/connect.php';

// Access Control Gatekeeper: Verify user is authenticated
if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

$current_username = $_SESSION['acms_valid_user'];

// --- DATA ENGINE: RUN CONTEXTUAL INVENTORY AGGREGATIONS ---

// 1. Total Registered Staff
$total_staff_query = "SELECT COUNT(*) as total FROM staff";
$total_staff_res = mysqli_query($db, $total_staff_query);
$total_staff = mysqli_fetch_assoc($total_staff_res)['total'] ?? 0;

// 2. Today's Live Clock-Ins
$today_date = date('Y-m-d');
$clock_ins_query = "SELECT COUNT(*) as total FROM attendance WHERE log_date = '$today_date' AND status = 'check-in'";
$clock_ins_res = mysqli_query($db, $clock_ins_query);
$today_clock_ins = mysqli_fetch_assoc($clock_ins_res)['total'] ?? 0;

// 3. Today's Live Clock-Outs
$clock_outs_query = "SELECT COUNT(*) as total FROM attendance WHERE log_date = '$today_date' AND status = 'check-out'";
$clock_outs_res = mysqli_query($db, $clock_outs_query);
$today_clock_outs = mysqli_fetch_assoc($clock_outs_res)['total'] ?? 0;

// 4. Distinct Active Operational Courses
$courses_query = "SELECT COUNT(DISTINCT course) as total FROM staff WHERE course IS NOT NULL AND course != ''";
$courses_res = mysqli_query($db, $courses_query);
$active_courses = mysqli_fetch_assoc($courses_res)['total'] ?? 0;

// 5. Recent Activity Feed Pipeline (Fetch last 5 transactions)
$activity_query = "SELECT a.log_time, a.log_date, a.status, s.firstname, s.lastname, s.staff_id 
                   FROM attendance a 
                   INNER JOIN staff s ON a.staff_id = s.staff_id 
                   ORDER BY a.id DESC LIMIT 5";
$recent_activities = mysqli_query($db, $activity_query);
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Aledoy Attendance Management Console</title>
    
    <link rel="icon" href="../Images/icon.png" type="image/x-icon">
    <link href="css/style.min.css" rel="stylesheet">
    
    <style>
        
    </style>
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
            <div class="container-fluid">
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="p-4 rounded shadow-sm text-white d-md-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #2f323e 0%, #1f212a 100%);">
                            <div>
                                <h2 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars(ucfirst($current_username)); ?>!</h2>
                                <p class="mb-0 opacity-75">Here is an overview of today's workplace activities and configuration vectors.</p>
                            </div>
                            <div class="mt-3 mt-md-0">
                                <a href="../index.php" target="_blank" class="btn btn-light fw-bold text-dark px-4 py-2 shadow-sm">
                                    <i class="fas fa-external-link-alt me-2"></i>Launch Terminal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card kpi-card p-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold small tracking-wider">Total Staff</span>
                                    <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $total_staff; ?></h3>
                                </div>
                                <div class="icon-box bg-light-info text-info" style="background-color: #ebf8ff; color: #3182ce;">
                                    <i class="fa fa-users fa-lg"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="staff_list.php" class="small text-decoration-none fw-medium" style="color: #3182ce;">View Staff Directory →</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card kpi-card p-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold small tracking-wider">Today's In</span>
                                    <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $today_clock_ins; ?></h3>
                                </div>
                                <div class="icon-box" style="background-color: #f0fff4; color: #38a169;">
                                    <i class="fas fa-sign-in-alt fa-lg"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="attendance_log.php?status=check-in&log_date=<?php echo $today_date; ?>" class="small text-decoration-none fw-medium" style="color: #38a169;">Review check-ins →</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card kpi-card p-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold small tracking-wider">Today's Out</span>
                                    <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $today_clock_outs; ?></h3>
                                </div>
                                <div class="icon-box" style="background-color: #fffaf0; color: #dd6b20;">
                                    <i class="fas fa-sign-out-alt fa-lg"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="attendance_log.php?status=check-out&log_date=<?php echo $today_date; ?>" class="small text-decoration-none fw-medium" style="color: #dd6b20;">Review check-outs →</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card kpi-card p-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-bold small tracking-wider">Active Courses</span>
                                    <h3 class="fw-bold text-dark mt-1 mb-0"><?php echo $active_courses; ?></h3>
                                </div>
                                <div class="icon-box" style="background-color: #faf5ff; color: #805ad5;">
                                    <i class="fas fa-graduation-cap fa-lg"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="small text-muted fw-normal">Syllabus clusters tracked</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm p-4 h-100" style="background: #fff; border-radius: 10px;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h4 class="fw-bold text-dark mb-0">Recent Operational Activities</h4>
                                <a href="attendance_log.php" class="btn btn-sm btn-outline-secondary fw-semibold px-3">View Full Logs</a>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table align-middle text-nowrap mb-0 table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Staff Profile</th>
                                            <th>Log Stamp</th>
                                            <th>Action Metric</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($recent_activities) > 0): ?>
                                            <?php while($act = mysqli_fetch_assoc($recent_activities)): 
                                                $time_stamp = date('h:i A', strtotime($act['log_time']));
                                                $date_stamp = date('d M', strtotime($act['log_date']));
                                            ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div>
                                                                <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($act['firstname'] . ' ' . $act['lastname']); ?></h6>
                                                                <small class="text-muted text-uppercase" style="font-size: 11px;"><?php echo htmlspecialchars($act['staff_id']); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="fw-medium text-dark d-block"><?php echo $time_stamp; ?></span>
                                                        <small class="text-muted" style="font-size: 11px;"><?php echo $date_stamp; ?></small>
                                                    </td>
                                                    <td>
                                                        <?php if ($act['status'] === 'check-in'): ?>
                                                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 11px;"><i class="fas fa-long-arrow-alt-right me-1"></i> Clocked In</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1" style="font-size: 11px;"><i class="fas fa-long-arrow-alt-left me-1"></i> Clocked Out</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No structural activity events recorded on terminal channels today.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm p-4 h-100" style="background: #fff; border-radius: 10px;">
                            <h4 class="fw-bold text-dark mb-3">Quick Utilities</h4>
                            <p class="text-muted small mb-4">Immediate administrative control functions for operations management.</p>
                            
                            <div class="d-flex flex-column">
                                <a href="new_staff.php" class="action-link">
                                    <i class="fas fa-user-plus text-success me-3" style="width: 20px;"></i>
                                    <span>Register New Staff Profile</span>
                                </a>
                                <a href="register.php" class="action-link">
                                    <i class="fas fa-user-shield text-primary me-3" style="width: 20px;"></i>
                                    <span>Create Sub-Admin Account</span>
                                </a>
                                <a href="change-password.php" class="action-link">
                                    <i class="fas fa-key text-warning me-3" style="width: 20px;"></i>
                                    <span>Update Account Credentials</span>
                                </a>
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