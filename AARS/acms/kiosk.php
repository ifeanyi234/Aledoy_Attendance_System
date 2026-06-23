<?php
require_once __DIR__ . '/includes/app.php';

startAppSession();
$pdo = getPdoConnection();
$message = null;
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffId = trim((string)($_POST['staff_id'] ?? ''));

    if ($staffId === '') {
        $message = 'Please scan or enter a valid Staff ID.';
        $messageType = 'danger';
    } else {
        $result = recordAttendance($pdo, $staffId);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Kiosk</title>
    <link rel="stylesheet" href="css_login/bootstrap.min.css">
    <style>
        body { min-height: 100vh; background: linear-gradient(180deg, #0d6efd 0%, #7aa7ff 100%); color: #fff; }
        .kiosk-shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .kiosk-card { width: 100%; max-width: 520px; border-radius: 1.5rem; background: rgba(255,255,255,0.12); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.16); }
        .kiosk-card .card-body { padding: 3rem; }
        .logo-banner { height: 72px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.12); border-radius: .75rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="container kiosk-shell">
        <div class="card kiosk-card shadow-lg">
            <div class="card-body text-center">
                <div class="logo-banner mb-4">
                    <div>
                        <h4 class="mb-0">Organization Logo</h4>
                        <p class="small text-white-50 mb-0">Attendance Kiosk Terminal</p>
                    </div>
                </div>

                <?php if ($message !== null): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> text-dark" role="alert">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="mb-0">
                    <div class="mb-4 text-start">
                        <label for="staff_id" class="form-label text-white">Staff ID / Barcode</label>
                        <input type="text" id="staff_id" name="staff_id" class="form-control form-control-lg" placeholder="Scan or enter staff code" autofocus autocomplete="off" required>
                    </div>
                    <button type="submit" class="btn btn-light btn-lg w-100 fw-semibold">Attendance</button>
                </form>
                <p class="mt-3 text-white-50 small">Place the cursor in the field and scan barcode stream. Press Enter to submit.</p>
            </div>
        </div>
    </div>
</body>
</html>
