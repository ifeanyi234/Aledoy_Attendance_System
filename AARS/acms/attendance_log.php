<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/connection/connect.php';
require_once('fns.php');

$error = '';
$success = '';

if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}


$query = "SELECT a.id, a.staff_id, a.log_date, a.log_time, a.status, s.firstname, s.lastname, s.course 
          FROM attendance a 
          INNER JOIN staff s ON a.staff_id = s.staff_id 
          ORDER BY a.log_date DESC, a.log_time DESC";
          
$result = mysqli_query($db, $query);
          
$result = mysqli_query($db, $query);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Attendance Logs</title>

    <link rel="icon" href="../Aledoy-Attendance-system/Images/icon.png" type="image/x-icon">
    <link href="css/style.min.css" rel="stylesheet">
    </head>

<body>
    <!-- <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div> -->

    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
        
        <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin6">
                    <a class="navbar-brand" href="dashboard.php">
                        <b class="logo-icon">
                            <img src="../Aledoy-Attendance-system/Images/images-removebg-preview.png" style="max-width:120px; height:auto;" alt="homepage" />
                        </b>
                    </a>
                    <a class="nav-toggler waves-effect waves-light text-dark d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                </div>
                
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <ul class="navbar-nav me-auto d-flex align-items-center">
                        <li class="nav-item ps-3 text-white">
                            <i class="far fa-clock me-2"></i>
                            <span id="current-system-time" class="fw-medium text-white opacity-75">Loading System Time...</span>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav ms-auto d-flex align-items-center mb-0">
                        <li class="nav-item dropdown pe-3">
                            <span class="profile-pic d-flex align-items-center text-decoration-none">
                                <img src="plugins/images/users/admin.jfif" alt="admin-avatar" width="36" class="img-circle border border-2 border-white-50">
                                <span class="text-white font-medium ms-2">Admin</span>
                            </span>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <aside class="left-sidebar" data-sidebarbg="skin6">
            <div class="scroll-sidebar">
                <?php include('side-nav.php'); ?>
            </div>
        </aside>

        <div class="page-wrapper" style="margin-top: 30px;">
            <div class="page-breadcrumb bg-white">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title">Daily Attendance Logs</h4>
                    </div>
                    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                        <div class="d-md-flex">
                            <ol class="breadcrumb ms-auto">
                                <li><a href="#" class="fw-normal"></a></li>
                            </ol>
                            <a href="clock_in.php" class="btn btn-success d-none d-md-block pull-right ms-3 hidden-xs hidden-sm waves-effect waves-light text-white">
                                Open Clock-In Portal
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box">
                            <?php if (!empty($error)) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; ?>
                            <?php if (!empty($success)) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>'; ?>
                            
                            <div class="table-responsive">
                                <table class="table text-nowrap table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th>Staff ID</th>
                                            <th>Staff Name</th>
                                            <th>Assigned Course</th>
                                            <th>Log Date</th>
                                            <th>Log Time</th>
                                            <th>Activity Status</th>
                                            <th style="width: 100px; text-align: center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $num = mysqli_num_rows($result);
                                        if ($num == 0) {
                                            echo '<tr><td colspan="8" class="text-center text-muted py-4">No attendance activity logged yet.</td></tr>';
                                        }
                                        
                                        for ($i = 0; $i < $num; $i++) {
                                            $row = mysqli_fetch_array($result);
                                            
                                            // Human-readable formatting for dates and times
                                            $log_date_formatted = date('d M Y', strtotime($row['log_date']));
                                            $log_time_formatted = date('h:i A', strtotime($row['log_time']));
                                            
                                            // Contextual design configuration for tracking events
                                            if ($row['status'] == 'check-in') {
                                                $status_badge = '<span class="badge bg-success text-white rounded-pill px-3 py-1.5"><i class="fas fa-sign-in-alt me-1"></i> Clocked In</span>';
                                            } else {
                                                $status_badge = '<span class="badge bg-warning text-dark rounded-pill px-3 py-1.5"><i class="fas fa-sign-out-alt me-1"></i> Clocked Out</span>';
                                            }
                                        ?>
                                            <tr>
                                                <td><?php echo $i + 1; ?></td>
                                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['staff_id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['course'] ?: 'N/A'); ?></td>
                                                <td><?php echo $log_date_formatted; ?></td>
                                                <td><?php echo $log_time_formatted; ?></td>
                                                <td><?php echo $status_badge; ?></td>
                                                <td class="text-center">
                                                    <a style="margin-right: 15px; font-size: 20px;" href="edit-attendance.php?id=<?php echo $row['id']; ?>" title="Edit Entry">
                                                        <i class="fa fa-edit text-secondary"></i>
                                                    </a>
                                                    <a style="font-size: 20px;" href="delete-attendance.php?id=<?php echo $row['id']; ?>" title="Delete Entry" onclick="return confirm('Are you sure you want to permanently erase this logs transaction?');">
                                                        <i class="fa fa-trash text-danger"></i>
                                                    </a>
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

            <footer class="footer text-center"> Copyright © <?php echo date('Y') ?> - All Rights Reserved Aledoy Solution Limited</footer>
        </div>
    </div>

    <script src="plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app-style-switcher.js"></script>
    <script src="js/waves.js"></script>
    <script src="js/sidebarmenu.js"></script>
    <script src="js/custom.js"></script>
    
    <?php include(__DIR__ . '/includes/current_dateTime.php'); ?>
</body>
</html>