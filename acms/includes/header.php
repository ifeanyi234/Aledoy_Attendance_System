<?php
// Ensure sessions are initialized to safely read authentication states
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default layout variables if session tokens are absent
$display_username = 'Guest';
$is_admin = false;

// If an authenticated session is active, fetch current profile parameters
if (isset($_SESSION['acms_valid_user']) && isset($db)) {
    $display_username = $_SESSION['acms_valid_user'];
    
    // Sanitize user context strings before database validation queries
    $safe_header_user = mysqli_real_escape_string($db, $display_username);
    $header_query = "SELECT role FROM users WHERE username = '$safe_header_user' LIMIT 1";
    $header_result = mysqli_query($db, $header_query);
    
    if ($header_result && mysqli_num_rows($header_result) > 0) {
        $header_user_data = mysqli_fetch_assoc($header_result);
        // Identify if account holds full configuration authority (role = 1)
        if ((int)$header_user_data['role'] === 1) {
            $is_admin = true;
        }
    }
}
?>
<header class="topbar" data-navbarbg="skin5">
    <nav class="navbar top-navbar navbar-expand-md navbar-dark" style="min-height: 64px; height: 64px;">
        <div class="navbar-header" data-logobg="skin6" style="height: 64px; display: flex; align-items: center;">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php" style="height: 100%; padding: 10px 15px;">
                <span class="logo-text d-flex align-items-center">
                    <!-- Clamped logo image styles to prevent high-res layout blowouts -->
                    <img src="../Images/images-removebg-preview.png" alt="homepage" 
                         style="max-height: 40px; max-width: 130px; width: auto; height: auto; object-fit: contain;" />
                </span>
            </a>
            <a class="nav-toggler waves-effect waves-light text-dark d-block d-md-none"
                href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
        </div>
        
        <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5" style="height: 64px;">
            <ul class="navbar-nav me-auto d-flex align-items-center">
                <li class="nav-item ps-3 text-white">
                    <i class="far fa-clock me-2"></i>
                    <span id="current-system-time" class="fw-medium text-white opacity-75">Loading System Time...</span>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto d-flex align-items-center mb-0">
                <li class="nav-item dropdown pe-3">
                    <span class="profile-pic d-flex align-items-center text-decoration-none">
                        <?php if ($is_admin): ?>
                            <img src="plugins/images/users/admin.jfif" alt="admin-avatar" width="36" height="36" class="img-circle border border-2 border-white-50" style="object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center bg-light text-dark rounded-circle fw-bold shadow-sm" 
                                 style="width: 36px; height: 36px; border: 2px solid rgba(255,255,255,0.6); font-size: 14px; user-select: none;">
                                <?php echo strtoupper(substr($display_username, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <span class="text-white font-medium ms-2 d-inline-block" style="line-height: 1.2;">
                            <?php echo htmlspecialchars(ucfirst($display_username)); ?>
                            <?php if ($is_admin): ?>
                                <small style="font-size: 10px; display: block; color: #a0aec0; margin-top: 2px; font-weight: normal;">Main Admin</small>
                            <?php else: ?>
                                <small style="font-size: 10px; display: block; color: #a0aec0; margin-top: 2px; font-weight: normal;">Staff Member</small>
                            <?php endif; ?>
                        </span>
                    </span>
                </li>
            </ul>
        </div>
    </nav>
</header>