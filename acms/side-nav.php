<nav class="sidebar-nav">
    <ul id="sidebarnav">
        <li class="sidebar-item pt-2">
            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="dashboard.php" aria-expanded="false">
                <i class="fa fa-tachometer-alt" aria-hidden="true"></i>
                <span class="hide-menu">Dashboard</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="staff_list.php" aria-expanded="false">
                <i class="fa fa-users" aria-hidden="true"></i>
                <span class="hide-menu">Staff List</span>
            </a>
        </li>
<?php if(isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 1): ?>
        <li class="sidebar-item">
            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="new_staff.php" aria-expanded="false">
                <i class="fa fa-user-plus" aria-hidden="true"></i>
                <span class="hide-menu">New Staff</span>
            </a>
        </li>
<?php endif; ?>
        <li class="sidebar-item">
            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="attendance_log.php" aria-expanded="false">
                <i class="fa fa-calendar-check" aria-hidden="true"></i>
                <span class="hide-menu">Attendance</span>
            </a>
        </li>

        <?php if(isset($_SESSION['user_role']) && (int)$_SESSION['user_role'] === 1): ?>
<li class="sidebar-item">
    <a class="sidebar-link waves-effect waves-dark sidebar-link" href="users_list.php" aria-expanded="false">
        <i class="fa fa-user" aria-hidden="true"></i>
        <span class="hide-menu">Manage Admins / Staff</span>
    </a>
</li>
<?php endif; ?>
       
        <li class="sidebar-item">
            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="change-password.php" aria-expanded="false">
                <i class="fa fa-key" aria-hidden="true"></i>
                <span class="hide-menu">Change password</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a class="sidebar-link waves-effect waves-dark sidebar-link" href="logout.php" aria-expanded="false">
                <i class="fa fa-sign-out-alt" aria-hidden="true"></i>
                <span class="hide-menu">Logout</span>
            </a>
        </li>
    </ul>
</nav>