<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/connection/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capture and sanitize incoming barcode/QR payload details
    $staff_id = isset($_POST['staff_id']) ? mysqli_real_escape_string($db, trim($_POST['staff_id'])) : '';
    $status   = isset($_POST['status']) ? mysqli_real_escape_string($db, trim($_POST['status'])) : 'check-in';

    // Enforce matching values with your database ENUM configuration definitions
    if (!in_array($status, ['check-in', 'check-out'])) {
        $status = 'check-in';
    }

    if (empty($staff_id)) {
        $_SESSION['attendance_error'] = "Scan error: No character data received. Please flash the QR card again.";
        header("Location: kiosk.php");
        exit;
    }

    // Step 1: Query your registry to confirm this Staff ID is valid and registered
    $check_staff = "SELECT firstname, lastname FROM staff WHERE staff_id = '$staff_id' LIMIT 1";
    $staff_result = mysqli_query($db, $check_staff);

    if ($staff_result && mysqli_num_rows($staff_result) > 0) {
        $staff_row = mysqli_fetch_assoc($staff_result);
        $fullname  = $staff_row['firstname'] . ' ' . $staff_row['lastname'];

        // Step 2: Establish correct regional system time offsets (West Africa Time)
        date_default_timezone_set('Africa/Lagos');
        $current_date = date('Y-m-d');
        $current_time = date('H:i:s');

        // Step 3: Insert log entry structure straight into your attendance matrix table
        $query = "INSERT INTO attendance (staff_id, log_date, log_time, status) 
                  VALUES ('$staff_id', '$current_date', '$current_time', '$status')";
        
        if (mysqli_query($db, $query)) {
            // Personalize the kiosk alert banner text dynamically based on selection state
            if ($status === 'check-in') {
                $_SESSION['attendance_success'] = "🟢 Welcome, $fullname! You clocked in successfully at " . date('h:i A');
            } else {
                $_SESSION['attendance_success'] = "🟡 Goodbye, $fullname! You clocked out successfully at " . date('h:i A');
            }
        } else {
            $_SESSION['attendance_error'] = "System Error: Couldn't write tracking logs transaction details to the server.";
        }

    } else {
        // Handle misreads or unregistered code tags safely
        $_SESSION['attendance_error'] = "❌ Access Denied: Scanned ID card \"$staff_id\" does not match any registered staff record.";
    }

} else {
    $_SESSION['attendance_error'] = "Invalid access attempt protocol method rejected.";
}

// Route system state back down cleanly to refresh the interface focus loop
header("Location: kiosk.php");
exit;
?>