<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/connection/connect.php';

$message = '';
$messageType = '';

if (isset($_SESSION['attendance_success'])) {
    $message = $_SESSION['attendance_success'];
    $messageType = 'success';
    unset($_SESSION['attendance_success']);
} elseif (isset($_SESSION['attendance_error'])) {
    $message = $_SESSION['attendance_error'];
    $messageType = 'danger';
    unset($_SESSION['attendance_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../attendsystem.css">
    <link rel="icon" href="../Images/icon.png" type="image/x-icon">
    <title>Attendance Kiosk Terminal</title>
    
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" type="text/javascript"></script>

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            /* background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); */
            font-family: system-ui, -apple-system, sans-serif;
        }
        .form-container {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            width: 100%;
            max-width: 480px;
        }
        .company-logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 1rem;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        /* Camera UI Box Wrapper */
        .scanner-viewport {
            width: 100%;
            background: #f8f9fa;
            border: 2px dashed #ced4da;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            margin-bottom: 1.5rem;
        }
        #reader {
            width: 100%;
        }
        
        /* Status Selection Elements */
        .status-toggle-group {
            display: flex;
            gap: 10px;
            margin-bottom: 1.5rem;
        }
        .status-option {
            flex: 1;
            position: relative;
        }
        .status-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .status-label {
            display: block;
            padding: 12px;
            background: #f0f2f5;
            color: #333;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }
        .status-option input[type="radio"]:checked + .status-label.in {
            background: #28a745;
            color: white;
        }
        .status-option input[type="radio"]:checked + .status-label.out {
            background: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <div class="logo-badge">
            <img src="../Images/images-removebg-preview.png" alt="Company Logo" class="company-logo">
        </div>
        
        <h2 style="color: #333; margin-bottom: 0.5rem;">QR Attendance Kiosk</h2> 
        <p style="color: #666; margin-bottom: 1.5rem; font-size: 0.9rem;">Select action and flash your QR Code badge</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form action="proc-kiosk.php" method="POST" id="kioskForm">
            
            <div class="status-toggle-group">
                <div class="status-option">
                    <input type="radio" id="status_in" name="status" value="check-in" checked>
                    <label for="status_in" class="status-label in">🟢 CLOCK IN</label>
                </div>
                <div class="status-option">
                    <input type="radio" id="status_out" name="status" value="check-out">
                    <label for="status_out" class="status-label out">🟡 CLOCK OUT</label>
                </div>
            </div>
            
            <div class="scanner-viewport">
                <div id="reader"></div>
            </div>
            
            <input type="hidden" id="hidden_staff_id" name="staff_id">
        </form>
        
        <p style="margin-top: 1rem; color: #999; font-size: 0.8rem;">
            📷 Please allow camera access permissions if requested by the browser environment.
        </p>
    </div>

    <script>
        // Initialize the programmatic HTML5-QR Code Scanner instance
        const html5QrCode = new Html5Qrcode("reader");
        
        // Scan parameters: Configured to process 10 frame captures per second
        const qrConfig = { 
            fps: 10, 
            qrbox: { width: 250, height: 250 } 
        };

        // Execution callback triggered precisely when a QR boundary matching data is found
        function onScanSuccess(decodedText, decodedResult) {
            // 1. Assign target content payload data directly to the form payload
            document.getElementById('hidden_staff_id').value = decodedText;
            
            // 2. Shut down the camera stream cleanly to prevent processing double overlaps
            html5QrCode.stop().then(() => {
                // 3. Fire form transmission straight down to proc-kiosk.php
                document.getElementById('kioskForm').submit();
            }).catch((err) => {
                console.error("Failed to halt tracking thread stream loop properly: ", err);
                // Fallback submission if stopping takes too long
                document.getElementById('kioskForm').submit();
            });
        }

        // Auto-launch sequence: Requests the system webcam instantly upon window completion loading
        // 'facingMode: "user"' defaults to front-facing/selfie webcam. Change to "environment" for rear cameras.
        html5QrCode.start(
            { facingMode: "user" }, 
            qrConfig, 
            onScanSuccess
        ).catch((err) => {
            console.error("Unable to bind camera authorization tracking context stream: ", err);
            document.getElementById('reader').innerHTML = 
                `<div style="padding:20px; color:#721c24; background:#f8d7da; font-size:0.85rem;">` +
                `❌ Camera initialization error. Check browser security exceptions or configuration blocks.` +
                `</div>`;
        });
    </script>
</body>
</html>