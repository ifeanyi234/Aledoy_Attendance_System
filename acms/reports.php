<?php
require_once __DIR__ . '/includes/app.php';

startAppSession();
requireAuth();
$period = trim((string)($_GET['period'] ?? 'today'));
$staffType = trim((string)($_GET['staff_type'] ?? 'all'));
$reportType = trim((string)($_GET['report_type'] ?? 'attendance'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Staff Attendance Management</title>
    <link rel="stylesheet" href="css_login/bootstrap.min.css">
    <link rel="icon" href="../Images/icon.png" type="image/x-icon">
    <style>
        body { min-height: 100vh; background: #f8f9fc; }
        .sidebar-frame { min-width: 220px; }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include __DIR__ . '/admin_sidebar.php'; ?>
        <div class="flex-fill p-4">
            <div class="mb-4">
                <h2 class="mb-1">Reports</h2>
                <p class="text-muted mb-0">Select filters on the left, then review the report placeholder on the right.</p>
            </div>

            <div class="row gy-4">
                <div class="col-12 col-xl-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Report Filters</h5>
                            <form method="get">
                                <div class="mb-3">
                                    <label for="period" class="form-label">Date Period</label>
                                    <select id="period" name="period" class="form-select">
                                        <option value="today"<?php echo $period === 'today' ? ' selected' : ''; ?>>Today</option>
                                        <option value="week"<?php echo $period === 'week' ? ' selected' : ''; ?>>This Week</option>
                                        <option value="month"<?php echo $period === 'month' ? ' selected' : ''; ?>>This Month</option>
                                        <option value="custom"<?php echo $period === 'custom' ? ' selected' : ''; ?>>Custom Date Range</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="staff_type" class="form-label">Staff Type</label>
                                    <select id="staff_type" name="staff_type" class="form-select">
                                        <option value="all"<?php echo $staffType === 'all' ? ' selected' : ''; ?>>All</option>
                                        <option value="main"<?php echo $staffType === 'main' ? ' selected' : ''; ?>>Main</option>
                                        <option value="academy"<?php echo $staffType === 'academy' ? ' selected' : ''; ?>>Academy</option>
                                        <option value="occasional"<?php echo $staffType === 'occasional' ? ' selected' : ''; ?>>Occasional</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="report_type" class="form-label">Report Type</label>
                                    <select id="report_type" name="report_type" class="form-select">
                                        <option value="attendance"<?php echo $reportType === 'attendance' ? ' selected' : ''; ?>>Attendance Summary</option>
                                        <option value="staff"<?php echo $reportType === 'staff' ? ' selected' : ''; ?>>Staff Roster</option>
                                        <option value="activity"<?php echo $reportType === 'activity' ? ' selected' : ''; ?>>Activity Analysis</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-8">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">Report Preview</h5>
                            <div class="border rounded-3 p-4 h-100 d-flex flex-column justify-content-center align-items-center text-center bg-white">
                                <div class="mb-3">
                                    <span class="badge bg-secondary">Placeholder</span>
                                </div>
                                <p class="text-muted mb-2">Chart and summary panels will render here after filter selection.</p>
                                <p class="mb-0">Selected period: <strong><?php echo htmlspecialchars(ucfirst($period), ENT_QUOTES, 'UTF-8'); ?></strong></p>
                                <p class="mb-0">Selected staff type: <strong><?php echo htmlspecialchars(ucfirst($staffType), ENT_QUOTES, 'UTF-8'); ?></strong></p>
                                <p class="mb-0">Report type: <strong><?php echo htmlspecialchars(ucfirst($reportType), ENT_QUOTES, 'UTF-8'); ?></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
