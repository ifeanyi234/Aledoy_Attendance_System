<?php
session_start();

require_once __DIR__ . '/connection/connect.php';
// Security check: Match your dashboard.php logic
if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

// Fetch Data (Using $db and your specific table: newsletter_subscribers)
$query = "SELECT * FROM staff ORDER BY Id DESC";
$result = mysqli_query($db, $query);

?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Newsletter Subscribers</title>
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

        <div class="page-wrapper" style="margin-top: 30px;">
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
                
                <div class="table-responsive">
                    <table class="table text-nowrap table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Staff ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email Address</th>
                                <th>Phone Number</th>
                                <th>Staff Type</th>
                                <th>Assigned Course</th>
                                <th>Date Registered</th>
                                <th style="width: 100px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            if($result && mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) { 
                                    
                                    // Make the date_created string look human-readable and clean
                                    if (!empty($row['date_created']) && $row['date_created'] != '0000-00-00 00:00:00') {
                                        $date_formatted = date('d M Y | h:i A', strtotime($row['date_created']));
                                    } else {
                                        $date_formatted = '<span class="text-muted italic">Not Set</span>';
                                    }
                                    
                                    // Optional: Render professional badges based on staff clearance/type
                                    $staff_type_clean = strtolower(trim($row['staff_type']));
                                    if ($staff_type_clean === 'main' || $staff_type_clean === 'admin') {
                                        $type_badge = '<span class="badge bg-primary text-white rounded-pill px-2.5 py-1">Main</span>';
                                    } else {
                                        $type_badge = '<span class="badge bg-info text-white rounded-pill px-2.5 py-1">' . htmlspecialchars($row['staff_type']) . '</span>';
                                    }
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['staff_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['firstname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lastname']); ?></td>
                                    <td><span class="text-muted"><?php echo htmlspecialchars($row['email']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['phone'] ?: 'N/A'); ?></td>
                                    <td><?php echo $type_badge; ?></td>
                                    <td><?php echo htmlspecialchars($row['course'] ?: 'N/A'); ?></td>
                                    <td><?php echo $date_formatted; ?></td>
                                    <td class="text-center">
                                        <a style="margin-right: 15px; font-size: 20px;" href="edit-staff.php?id=<?php echo $row['id'] ?? $row['staff_id']; ?>" title="Edit Profile">
                                            <i class="fa fa-edit text-secondary"></i>
                                        </a>
                                        <a style="font-size: 20px;" href="delete-staff.php?id=<?php echo $row['id'] ?? $row['staff_id']; ?>" title="Remove Staff" onclick="return confirm('Are you sure you want to permanently remove this staff member from the system?');">
                                            <i class="fa fa-trash text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } 
                            } else { ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        No registered staff records found in system. <a href="new_staff.php" class="fw-bold text-success ms-1">Add Staff Member</a>
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