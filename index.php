<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/acms/connection/connect.php';

// Set timezone to ensure accurate local time detection
date_default_timezone_set('Africa/Lagos');
$current_hour = (int)date('H');

// Auto-switch default selection: 'check-in' before 12 PM, 'check-out' from 12 PM onwards
$default_status = ($current_hour < 12) ? 'check-in' : 'check-out';

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
    <link rel="stylesheet" href="attendsystem.css">
    <link rel="icon" href="images/icon.png" type="image/x-icon">
    <title>Aledoy :: Attendance Terminal 2</title>
    
    <script   src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    </script>

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
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

        .manual-entry-box {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        .manual-input-container {
            display: flex;
            gap: 8px;
        }
        .manual-input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
        }
        .manual-input:focus {
            border-color: #2a5298;
        }
        .manual-btn {
            padding: 12px 20px;
            background-color: #2F323E;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .manual-btn:hover {
            background-color: #1e2027;
        }
    </style>
</head>
<body>
    <!-- Real-time Offline Warning Banner -->
    <div id="networkOfflineAlert" class="alert alert-danger" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 10000; width: 90%; max-width: 440px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); font-weight: bold;">
        ❌ CONNECTION LOST: This kiosk terminal is offline. Attendance logs cannot be saved right now.
    </div>

    <div class="form-container">
        <div class="logo-badge">
            <img src="images/images-removebg-preview.png" alt="Company Logo" class="company-logo">
        </div>
        
        <h2 style="color: #333; margin-bottom: 0.5rem;">QR Attendance Terminal</h2> 
        <p style="color: #666; margin-bottom: 1.5rem; font-size: 0.9rem;">Flash your QR Code badge</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>" id="statusAlertBox">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form action="proc-kiosk.php" method="POST" <?php  if($_SERVER['HTTP_HOST'] != 'aledoy.com') { echo 'id="kioskForm"'; } ?>>
            
            <div class="status-toggle-group">
                <div class="status-option">
                    <input type="radio" id="status_in" name="status" value="check-in" <?php echo ($default_status === 'check-in') ? 'checked' : ''; ?>>
                    <label for="status_in" class="status-label in">🟢 CLOCK IN</label>
                </div>
                <div class="status-option">
                    <input type="radio" id="status_out" name="status" value="check-out" <?php echo ($default_status === 'check-out') ? 'checked' : ''; ?>>
                    <label for="status_out" class="status-label out">🟡 CLOCK OUT</label>
                </div>
            </div>
            
            <div class="scanner-viewport">
                <div id="reader"></div>
            </div>

            <div class="manual-entry-box">
                <div style="color: #888; margin-bottom: 0.6rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">— Or Enter Manually —</div>
                <div class="manual-input-container">
                    <input type="text" id="manual_staff_id" name="txt_staff_id" class="manual-input" placeholder="Type Staff ID here..." autofocus>
                    <?php  if($_SERVER['HTTP_HOST'] != 'aledoy.com') { ?>

                    <button type="button" id="manualSubmitBtn" class="manual-btn">Submit</button>
                    <?php } else {?>
                    <button type="submit" class="manual-btn">Submit</button>
                    <?php } ?>
                </div>
            </div>
            
            <input type="hidden" id="hidden_staff_id" name="staff_id">
        </form>
        
        <p style="margin-top: 1.5rem; color: #999; font-size: 0.8rem;">
            📷 Please allow camera access permissions if requested by the browser environment.
        </p>
    </div>

    <script>
        const html5QrCode = new Html5Qrcode("reader");
        const qrConfig = { 
            fps: 10, 
            qrbox: { width: 250, height: 250 } 
        };

        // --- FIXED SOUND ALERTS VIA WEB AUDIO API ---
        function triggerAudioNotification(statusTone) {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                
                const context = new AudioCtx();
                const oscillator = context.createOscillator();
                const gainNode = context.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(context.destination);
                
                if (statusTone === 'success') {
                    // Double pleasant chime (high pitch)
                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(587.33, context.currentTime); // D5
                    oscillator.frequency.setValueAtTime(880.00, context.currentTime + 0.1); // A5
                    gainNode.gain.setValueAtTime(0.15, context.currentTime);
                    oscillator.start();
                    oscillator.stop(context.currentTime + 0.25);
                } else if (statusTone === 'danger') {
                    oscillator.type = 'sawtooth'; 
                    oscillator.frequency.setValueAtTime(260.00, context.currentTime); // Lower mid-range grit
                    
                    // Pump up the volume
                    gainNode.gain.setValueAtTime(0.4, context.currentTime);
                    
                    // Smoothly fade out at the very end to prevent speaker popping
                    gainNode.gain.linearRampToValueAtTime(0.01, context.currentTime + 0.4);
                    
                    oscillator.start();
                    oscillator.stop(context.currentTime + 0.4);
                }
            } catch (error) {
                console.error("Audio system blocked by strict client-side browser context:", error);
            }
        }

        // --- REAL-TIME NETWORK MONITORING ---
        function evaluateNetworkConnectivity() {
            const offlineBanner = document.getElementById('networkOfflineAlert');
            const manualButton = document.getElementById('manualSubmitBtn');
            const manualInput = document.getElementById('manual_staff_id');

            if (!navigator.onLine) {
                // Device is offline
                offlineBanner.style.display = 'block';
                if (manualButton) manualButton.disabled = true;
                if (manualInput) manualInput.disabled = true;
                
                // Play the error buzz sound if it's a fresh disconnect
                if (typeof triggerAudioNotification === 'function') {
                    triggerAudioNotification('danger');
                }
            } else {
                // Device is back online
                offlineBanner.style.display = 'none';
                if (manualButton) manualButton.disabled = false;
                if (manualInput) manualInput.disabled = false;
            }
        }

        // Attach listeners for real-time network drops/recovery
        window.addEventListener('online', evaluateNetworkConnectivity);
        window.addEventListener('offline', evaluateNetworkConnectivity);

        // Run an initial check the exact moment the page renders
        window.addEventListener('DOMContentLoaded', evaluateNetworkConnectivity);

        // Fire sound notification instantly if a server flash message exists on frame render
        window.addEventListener('DOMContentLoaded', () => {
            const alertBox = document.getElementById('statusAlertBox');
            if (alertBox) {
                if (alertBox.classList.contains('alert-success')) {
                    triggerAudioNotification('success');
                } else if (alertBox.classList.contains('alert-danger')) {
                    triggerAudioNotification('danger');
                }
            }

            // --- FIXED AUTO-REFRESH EXACTLY AT 12:00 PM ---
            const rightNow = new Date();
            const targetDeadline = new Date();
            targetDeadline.setHours(12, 0, 0, 0); // Target exact noon marker

            if (rightNow < targetDeadline) {
                const timeRemainingDifference = targetDeadline.getTime() - rightNow.getTime();
                setTimeout(() => {
                    window.location.reload();
                }, timeRemainingDifference);
            }
        });

        function processKioskSubmission(staffIdValue) {
            const cleanId = staffIdValue.trim();
            if (!cleanId) {
                alert("Please scan a valid badge or type a Staff ID entry manually.");
                return;
            }

            document.getElementById('hidden_staff_id').value = cleanId;
            
            html5QrCode.stop().then(() => {
                document.getElementById('kioskForm').submit();
            }).catch((err) => {
                console.warn("Camera pipeline closure bypass executed: ", err);
                document.getElementById('kioskForm').submit();
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            processKioskSubmission(decodedText);
        }

        const manualInputField = document.getElementById('manual_staff_id');
        const manualSubmitButton = document.getElementById('manualSubmitBtn');

        manualInputField.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); 
                processKioskSubmission(manualInputField.value);
            }
        });

        manualSubmitButton.addEventListener('click', function() {
            processKioskSubmission(manualInputField.value);
        });

        // --- FIXED: COOLDOWN SYSTEM TO PREVENT GHOST DOUBLE-SCANS ---
        function startCameraPipeline() {
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
        }

        const activeAlertMessage = document.getElementById('statusAlertBox');
        
        if (activeAlertMessage) {
            setTimeout(() => {
                startCameraPipeline();
            }, 4000); 
        } else {
            startCameraPipeline();
        }
    </script>
</body>
</html>