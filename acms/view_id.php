<?php
session_start();
require_once __DIR__ . '/connection/connect.php';

// Security Gate: Ensure user is logged in
if (!isset($_SESSION['acms_valid_user'])) {
    include('index.php');
    exit;
}

// Fetch and sanitize parameter ID
$staff_id_pk = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($staff_id_pk <= 0) {
    $_SESSION['error'] = "Invalid access criteria. Target staff identification parameter missing.";
    header("Location: staff_list.php");
    exit;
}

// Execute database retrieval match
$query = "SELECT * FROM staff WHERE id = $staff_id_pk LIMIT 1";
$result = mysqli_query($db, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    $_SESSION['error'] = "Requested staff profile ledger record could not be located.";
    header("Location: staff_list.php");
    exit;
}

$staff = mysqli_fetch_assoc($result);

// Resolve file path locations with smart fallbacks
$passport_photo = !empty($staff['passport_image']) && file_exists(__DIR__ . '/' . $staff['passport_image']) 
    ? htmlspecialchars($staff['passport_image']) 
    : 'images/users/d1.jpg';

$qr_code_img = !empty($staff['qr_code']) && file_exists(__DIR__ . '/' . $staff['qr_code']) 
    ? htmlspecialchars($staff['qr_code']) 
    : '';

// Parse and normalize badge classifications
$staff_type_clean = strtolower(trim($staff['staff_type'] ?? 'main'));
if ($staff_type_clean === 'main' || $staff_type_clean === 'admin') {
    $badge_class = 'badge-main';
    $badge_title = 'MAIN STAFF';
} else if ($staff_type_clean === 'part-time') {
    $badge_class = 'badge-part-time';
    $badge_title = 'PART-TIME STAFF';
} else {
    $badge_class = 'badge-academy';
    $badge_title = strtoupper(htmlspecialchars($staff['staff_type']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Badge - <?php echo htmlspecialchars($staff['firstname'] . ' ' . $staff['lastname']); ?></title>
    <link rel="icon" href="../Images/icon.png" type="image/x-icon">
    <link href="css/style.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Action Bar Controller Wrapper */
        .control-panel-wrapper {
            max-width: 700px;
            margin: 30px auto 10px auto;
        }

        /* Flexible Flex Wrapper to hold both card faces */
        .badge-print-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 25px;
            flex-wrap: wrap;
            max-width: 900px;
            margin: 10px auto 50px auto;
        }

        /* Standard CR80 Card Dimensions */
        .id-badge-canvas {
            width: 340px;
            height: 520px;
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        /* ==========================================
           FRONT FACE SPECIFIC LAYOUT STYLES
           ========================================== */
        .badge-header-band {
            background-color: #2F323E;
            height: 115px;
            padding: 20px 15px;
            text-align: center;
            color: #ffffff;
        }
        .badge-header-band .brand-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .badge-header-band .brand-subtitle {
            font-size: 10px;
            color: #a0aec0;
            margin: 3px 0 0 0;
            letter-spacing: 0.5px;
        }

        .badge-avatar-container {
            margin-top: -50px;
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 5;
        }
        .badge-avatar-frame {
            width: 115px;
            height: 115px;
            border-radius: 50%;
            border: 4px solid #ffffff;
            object-fit: cover;
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        }

        .badge-body-content {
            padding: 20px;
            text-align: center;
        }
        .staff-fullname {
            font-size: 22px;
            font-weight: 700;
            color: #1a202c;
            margin: 0 0 4px 0;
            text-transform: capitalize;
        }
        .staff-role-dept {
            font-size: 13px;
            color: #4a5568;
            font-weight: 600;
            margin: 0 0 15px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .type-pill {
            display: inline-block;
            padding: 5px 16px;
            font-size: 10px;
            font-weight: 700;
            border-radius: 50px;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }
        .badge-main { background-color: #e1effe; color: #1e429f; border: 1px solid #b3d1ff; }
        .badge-part-time { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-academy { background-color: #e3f2fd; color: #0d47a1; border: 1px solid #bbdefb; }

        .metadata-grid {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px dashed #cbd5e0;
            margin-top: 20px;
        }
        .metadata-item { text-align: left; }
        .metadata-item.right { text-align: right; }
        .metadata-label {
            font-size: 9px;
            color: #718096;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .metadata-value {
            font-size: 12px;
            font-weight: 700;
            color: #2d3748;
        }

        /* ==========================================
           BACK FACE SPECIFIC LAYOUT STYLES
           ========================================== */
        .back-header-line {
            background-color: #2F323E;
            height: 12px;
            width: 100%;
        }
        .back-body-content {
            padding: 25px 20px;
            text-align: center;
        }
        .terms-heading {
            font-size: 11px;
            font-weight: 700;
            color: #2f323e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .terms-list {
            padding-left: 15px;
            text-align: left;
            margin-bottom: 25px;
        }
        .terms-list li {
            font-size: 10px;
            color: #4a5568;
            line-height: 1.5;
            margin-bottom: 8px;
        }
        
        .badge-qr-container {
            margin: 20px 0;
            display: flex;
            justify-content: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 20px;
        }
        .badge-qr-matrix {
            /* width: 120px;
            height: 120px; */
            width:75%;
            padding: 6px;
            border: 1px solid #cbd5e0;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.04);
        }

        .signature-block {
            margin-top: 20px;
            border-top: 1px dashed #a0aec0;
            width: 60%;
            margin-left: auto;
            margin-right: auto;
            padding-top: 4px;
            font-size: 9px;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-system-footer-text {
            font-size: 8px;
            color: #a0aec0;
            text-align: center;
            position: absolute;
            bottom: 12px;
            left: 0;
            right: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ==========================================
           CRUCIAL MEDIA PRINT SEPARATION ENGINE
           ========================================== */
        @media print {
            body {
                background: #ffffff !important;
                margin: 0;
                padding: 0;
            }
            .no-print, .control-panel-wrapper, footer {
                display: none !important;
            }
            .badge-print-container {
                display: flex !important;
                flex-direction: row !important;
                justify-content: center !important;
                gap: 20px !important;
                margin: 40px auto !important;
                box-shadow: none !important;
                background: transparent !important;
            }
            .id-badge-canvas {
                box-shadow: none !important;
                border: 1px solid #a0aec0 !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>

    <!-- Upper Action Bar Controls (Hidden during physical print run) -->
    <div class="control-panel-wrapper no-print">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 shadow-sm" style="border-radius: 8px; border: none;">
            <a href="staff_list.php" class="btn btn-outline-secondary btn-sm fw-bold px-3">
                <i class="fa fa-arrow-left me-1"></i> Return to Directory
            </a>
            <button onclick="window.print();" class="btn btn-sm text-white fw-bold px-4" style="background-color: #2F323E;">
                <i class="fa fa-print me-1"></i> Print Double-Sided Card
            </button>
        </div>
    </div>

    <!-- Main Flex Badge Assembly Line Container -->
    <div class="badge-print-container">
        
        <!-- ==================== FRONT FACE OF BADGE ==================== -->
        <div class="id-badge-canvas">
            <div class="badge-header-band">
                <h5 class="brand-title">Aledoy Solutions</h5>
                <p class="brand-subtitle">Staff Identity Access Badge</p>
            </div>

            <div class="badge-avatar-container">
                <img src="<?php echo $passport_photo; ?>" alt="Staff Passport Pic" class="badge-avatar-frame">
            </div>

            <div class="badge-body-content">
                <h2 class="staff-fullname"><?php echo htmlspecialchars($staff['firstname'] . ' ' . $staff['lastname']); ?></h2>
                <p class="staff-role-dept"><?php echo htmlspecialchars($staff['course'] ?: 'Enterprise Infrastructure'); ?></p>
                
                <span class="type-pill <?php echo $badge_class; ?>"><?php echo $badge_title; ?></span>

                <div class="metadata-grid">
                    <div class="metadata-item">
                        <div class="metadata-label">System ID</div>
                        <div class="metadata-value" style="letter-spacing: 0.5px;"><?php echo htmlspecialchars($staff['staff_id']); ?></div>
                    </div>
                    <div class="metadata-item right">
                        <div class="metadata-label">Issued Date</div>
                        <div class="metadata-value">
                            <?php 
                            echo (!empty($staff['date_created']) && $staff['date_created'] !== '0000-00-00 00:00:00') 
                                ? date('M Y', strtotime($staff['date_created'])) 
                                : date('M Y'); 
                            ?>
                        </div>
                    </div>
                </div>
                <div class="company-logo"><img src="../acms/images/logo.png" alt="Company Logo" style="margin-top: 30px;"> </div>
            </div>

            <div class="badge-system-footer-text">
                Authorized Access Badge • Front Face
            </div>
        </div>


        <!-- ==================== BACK FACE OF BADGE ==================== -->
        <div class="id-badge-canvas" style="background-color: #fafbfc;">
            <div class="back-header-line"></div>
            
            <div class="back-body-content">
                <h4 class="terms-heading">Security Instructions & Terms</h4>
                
                <ul class="terms-list">
                    <li>This card is the official property of Aledoy Solution Limited and must be worn conspicuously while on institutional premises.</li>
                    <!-- <li>The access token and embedded matrix elements are unique to the assignee and are strictly non-transferable.</li>
                    <li>Loss or displacement of this identification badge asset must be reported to the security/HR department immediately.</li> -->
                    <li>If discovered or found outside company domains, please return directly to the address below.</li>
                </ul>

                <h4 class="terms-heading">Identity Scan Verification</h4>
                <div class="badge-qr-container">
                    <?php if (!empty($qr_code_img)): ?>
                        <img src="<?php echo $qr_code_img; ?>" alt="Scan Token Matrix" class="badge-qr-matrix">
                    <?php else: ?>
                        <div class="text-danger small fw-bold py-4">Matrix Block Uncompiled</div>
                    <?php endif; ?>
                </div>

                <!-- <div class="signature-block">
                    Authorized Signatory
                </div> -->
            </div>

            <div class="badge-system-footer-text">
                Aledoy Solution Ltd • info@aledoy.com
            </div>
        </div>

    </div>

</body>
</html>