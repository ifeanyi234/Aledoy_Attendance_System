<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'acms/connection/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $staff_id = isset($_POST['staff_id']) ? mysqli_real_escape_string($db, trim($_POST['staff_id'])) : '';
    $status   = isset($_POST['status']) ? mysqli_real_escape_string($db, trim($_POST['status'])) : 'check-in';

    if (!in_array($status, ['check-in', 'check-out'])) {
        $status = 'check-in';
    }

    if($_POST['txt_staff_id'])
        {
            $staff_id = mysqli_real_escape_string($db, trim($_POST['txt_staff_id']));
        }

    if (empty($staff_id)) {
        $_SESSION['attendance_error'] = "Scan error: No valid ID data detected.";
        header("Location: index.php");
        exit;
    }

    date_default_timezone_set('Africa/Lagos');
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');

    $clock_in_deadline = "14:00:00"; 
    $clock_out_start   = "14:00:00";

    // Verify the scanned staff profile exists
    $check_staff = "SELECT firstname, lastname FROM staff WHERE staff_id = '$staff_id' LIMIT 1";
    $staff_result = mysqli_query($db, $check_staff);

    if ($staff_result && mysqli_num_rows($staff_result) > 0) {
        $staff_row = mysqli_fetch_assoc($staff_result);
        $fullname  = $staff_row['firstname'] . ' ' . $staff_row['lastname'];

        // Fetch any attendance logs recorded for this staff member TODAY
        $log_check_query = "SELECT status FROM attendance WHERE staff_id = '$staff_id' AND log_date = '$current_date'";
        $log_check_result = mysqli_query($db, $log_check_query);

        $has_clocked_in  = false;
        $has_clocked_out = false;

        while ($log_row = mysqli_fetch_assoc($log_check_result)) {
            if ($log_row['status'] === 'check-in')  $has_clocked_in = true;
            if ($log_row['status'] === 'check-out') $has_clocked_out = true;
        }

        if ($status === 'check-in') {
            // FIXED LAYER: If past 12 PM but BEFORE 10 PM (22:00), deny normal entry. 
            // If it is past 10 PM, allow night shifts to record but flag them as late.
            if ($current_time > $clock_in_deadline && $current_time < "22:00:00") {
                $readable_deadline = date("h:i A", strtotime($clock_in_deadline));
                $_SESSION['attendance_error'] = "❌ Clock-In Closed: You cannot clock in past $readable_deadline. You are marked late/absent.";
                header("Location: index.php");
                exit;
            }

            if ($has_clocked_in) {
                $_SESSION['attendance_error'] = "⚠️ Hello $fullname, you have already clocked in for today.";
                header("Location: index.php");
                exit;
            }
        }

        if ($status === 'check-out') {
            if ($current_time < $clock_out_start) {
                $readable_deadline = date("h:i A", strtotime($clock_out_start));
                $_SESSION['attendance_error'] = "❌ Clock-Out Not Open: You cannot clock out before $readable_deadline.";
                header("Location: index.php");
                exit;
            }
            if (!$has_clocked_in) {
                $_SESSION['attendance_error'] = "❌ Access Denied: $fullname, you cannot Clock Out because you didn't Clock In this morning.";
                header("Location: index.php");
                exit;
            }
            if ($has_clocked_out) {
                $_SESSION['attendance_error'] = "⚠️ Hello $fullname, you have already clocked out for today.";
                header("Location: index.php");
                exit;
            }
        }

        // Run database insert if all rules pass successfully
        $query = "INSERT INTO attendance (staff_id, log_date, log_time, status) 
                  VALUES ('$staff_id', '$current_date', '$current_time', '$status')";
        
        if (mysqli_query($db, $query)) {
            if ($status === 'check-in') {
                // FIXED LAYER: Append warning payload natively if past 10 PM marker threshold
                if ($current_time >= "22:00:00") {
                    $_SESSION['attendance_success'] = "Welcome, $fullname! Clocked in successfully at " . date('h:i A') . " - YOU ARE LATE";
                } else {
                    $_SESSION['attendance_success'] = "Welcome, $fullname! Clocked in successfully at " . date('h:i A');
                }
            } else {
                $_SESSION['attendance_success'] = "Goodbye, $fullname! Clocked out successfully at " . date('h:i A');
            }
        } else {
            $_SESSION['attendance_error'] = "System Database error writing logs.";
        }

    } else {
        $_SESSION['attendance_error'] = "❌ Access Denied: Unrecognized staff badge reference.";
    }

} else {
    $_SESSION['attendance_error'] = "Invalid access method.";
}

header("Location: index.php");
exit;
?>