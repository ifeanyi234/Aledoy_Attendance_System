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

$is_main_admin = (isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 1);

// 1. Capture and sanitize dynamic range parameters instead of single static fields
$filter_search     = isset($_GET['search']) ? mysqli_real_escape_string($db, trim($_GET['search'])) : '';
$filter_course     = isset($_GET['course']) ? mysqli_real_escape_string($db, trim($_GET['course'])) : '';
$filter_status     = isset($_GET['status']) ? mysqli_real_escape_string($db, trim($_GET['status'])) : '';
$filter_start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($db, trim($_GET['start_date'])) : '';
$filter_end_date   = isset($_GET['end_date']) ? mysqli_real_escape_string($db, trim($_GET['end_date'])) : '';

// 2. Build SQL architecture supporting date ranges
$query = "SELECT a.id, a.staff_id, a.log_date, a.log_time, a.status, s.firstname, s.lastname, s.course 
          FROM attendance a 
          INNER JOIN staff s ON a.staff_id = s.staff_id 
          WHERE 1=1";

if (!empty($filter_search)) {
    $query .= " AND (a.staff_id LIKE '%$filter_search%' 
                     OR s.firstname LIKE '%$filter_search%' 
                     OR s.lastname LIKE '%$filter_search%')";
}

if (!empty($filter_course)) {
    $query .= " AND s.course = '$filter_course'";
}

if (!empty($filter_status)) {
    $query .= " AND a.status = '$filter_status'";
}

// FIXED: Process range filters safely depending on what options are selected
if (!empty($filter_start_date) && !empty($filter_end_date)) {
    $query .= " AND a.log_date BETWEEN '$filter_start_date' AND '$filter_end_date'";
} elseif (!empty($filter_start_date)) {
    $query .= " AND a.log_date >= '$filter_start_date'";
} elseif (!empty($filter_end_date)) {
    $query .= " AND a.log_date <= '$filter_end_date'";
}

$query .= " ORDER BY a.log_date DESC, a.log_time DESC";
          
$result = mysqli_query($db, $query);
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance Logs</title>
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
                        <h4 class="page-title">Daily Attendance Logs</h4>
                    </div>
                    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                        <div class="d-md-flex">
                            <a href="../index.php" target="_blank" class="btn btn-light fw-bold text-dark px-4 py-2 shadow-sm ms-auto">
                                <i class="fas fa-external-link-alt me-2"></i>Launch Terminal
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="white-box">
                            
                            <!-- FIXED: Highly Compact 1-Row Filter System -->
                            <div class="card mb-4 bg-light p-2 border-0 shadow-sm" style="border-radius: 8px;">
                                <form method="GET" action="" class="row g-2 align-items-end">
                                    
                                    <!-- Staff Search Block (3 Columns) -->
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 12px;">Staff Name or ID</label>
                                        <input type="text" name="search" value="<?php echo htmlspecialchars($filter_search); ?>" placeholder="e.g. Ifeanyi..." class="form-control bg-white" style="height: 38px; font-size: 13px;">
                                    </div>

                                    <!-- Course Selector Block (2 Columns) -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 12px;">Course</label>
                                        <select name="course" class="form-select bg-white" style="height: 38px; font-size: 13px;">
                                            <option value="">-- All --</option>
                                            <?php
                                            $course_res = mysqli_query($db, "SELECT DISTINCT course FROM staff WHERE course IS NOT NULL AND course != '' ORDER BY course ASC");
                                            while($c_row = mysqli_fetch_assoc($course_res)) {
                                                $selected = ($filter_course === $c_row['course']) ? 'selected' : '';
                                                echo "<option value='".htmlspecialchars($c_row['course'])."' $selected>".htmlspecialchars($c_row['course'])."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- Status Selector Block (2 Columns) -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 12px;">Status</label>
                                        <select name="status" class="form-select bg-white" style="height: 38px; font-size: 13px;">
                                            <option value="">-- All --</option>
                                            <option value="check-in" <?php echo ($filter_status === 'check-in') ? 'selected' : ''; ?>>Clocked In</option>
                                            <option value="check-out" <?php echo ($filter_status === 'check-out') ? 'selected' : ''; ?>>Clocked Out</option>
                                        </select>
                                    </div>

                                    <!-- MERGED: Connected Date Range Picker Block (3 Columns) -->
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted mb-1" style="font-size: 12px;">Date Period Range</label>
                                        <div class="input-group" style="height: 38px;">
                                            <!-- Start Date Input -->
                                            <input type="date" name="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>" class="form-control bg-white" style="font-size: 13px; height: 100%;">
                                            
                                            <!-- Separator Span -->
                                            <span class="input-group-text bg-white text-muted small" style="font-size: 11px; font-weight: 600; display: flex; align-items: center;">to</span>
                                            
                                            <!-- End Date Input -->
                                            <input type="date" name="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>" class="form-control bg-white" style="font-size: 13px; height: 100%;">
                                        </div>
                                    </div>

                                    <!-- Action Controls Block (2 Columns) -->
                                    <div class="col-md-2 d-flex gap-1">
                                        <button type="submit" class="btn text-white fw-bold flex-grow-1 d-flex align-items-center justify-content-center gap-1" style="background-color: #2F323E; height: 38px; font-size: 13px;">
                                            <i class="fa fa-search"></i> Filter
                                        </button>
                                        <?php if (!empty($filter_search) || !empty($filter_course) || !empty($filter_status) || !empty($filter_start_date) || !empty($filter_end_date)): ?>
                                            <a href="attendance_log.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height: 38px; width: 42px; padding: 0;" title="Reset Grid View">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                </form>
                            </div>

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
                                            <?php if ($is_main_admin): ?>
                                                <th style="width: 100px; text-align: center;">Action</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $num = mysqli_num_rows($result);
                                        if ($num == 0) {
                                            echo '<tr><td colspan="' . ($is_main_admin ? '8' : '7') . '" class="text-center text-muted py-4">No attendance activity logged matching selection range.</td></tr>';
                                        }
                                        
                                        for ($i = 0; $i < $num; $i++) {
                                            $row = mysqli_fetch_array($result);
                                            
                                            $log_date_formatted = date('d M Y', strtotime($row['log_date']));
                                            $log_time_formatted = date('h:i A', strtotime($row['log_time']));
                                            
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
                                                
                                                <?php if ($is_main_admin): ?>
                                                    <td class="text-center">
                                                        <a style="font-size: 20px;" href="delete-attendance.php?id=<?php echo $row['id']; ?>" title="Delete Entry" onclick="return confirm('Are you sure you want to permanently erase this logs transaction?');">
                                                            <i class="fa fa-trash text-danger"></i>
                                                        </a>
                                                    </td>
                                                <?php endif; ?>
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