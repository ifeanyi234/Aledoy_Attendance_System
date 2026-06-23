<?php
 
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
 
// Unset specific session key and destroy the session
if (isset($_SESSION['acms_valid_user'])) {
	unset($_SESSION['acms_valid_user']);
}
// clear remaining session data
$_SESSION = [];
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}
session_destroy();

// Redirect to login page
header("Location: index.php");
 
 ?>

