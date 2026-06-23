<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);

function sidebarLink(string $href, string $label, string $currentPage): string
{
    $active = $currentPage === basename($href) ? 'active' : '';
    return sprintf(
        '<a href="%s" class="nav-link text-white %s">%s</a>',
        htmlspecialchars($href, ENT_QUOTES, 'UTF-8'),
        $active,
        htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
    );
}
?>
<nav class="nav flex-column bg-dark text-white vh-100 p-3" style="min-width: 220px;">
    <div class="mb-4 text-center">
        <h5 class="text-white mb-1">Attendance</h5>
        <small class="text-muted">Staff Management</small>
    </div>
    <?php echo sidebarLink('dashboard.php', 'Dashboard', $currentPage); ?>
    <?php echo sidebarLink('staff_list.php', 'Staff List', $currentPage); ?>
    <?php echo sidebarLink('new_staff.php', 'New Staff', $currentPage); ?>
    <?php echo sidebarLink('attendance_log.php', 'Attendance', $currentPage); ?>
    <?php echo sidebarLink('reports.php', 'Report', $currentPage); ?>
    <?php echo sidebarLink('change-password.php', 'Change Password', $currentPage); ?>
    <?php echo sidebarLink('logout.php', 'Logout', $currentPage); ?>
</nav>
