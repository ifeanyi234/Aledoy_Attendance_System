<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/connection/connect.php';

if(isset($_POST['sub'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = isset($_POST['role']) ? $_POST['role'] : '';
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Ensure all data fields including role are evaluated
    if($username == '' || $email == '' || $password == '' || $confirm_password == '' || $role === '') {
        $error = 'All information are required';
        include ('register.php');
        exit;
    }

    // NEW CHECK: Verify there are no spaces anywhere inside the username string
    if (preg_match('/\s/', $username)) {
        $error = 'Username cannot contain spaces';
        include ('register.php');
        exit;
    }

    // Email format sanitizer
    $email = filter_var($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
        include ('register.php');
        exit;
    }
    
    // Validate structural password strength rules
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialchar = preg_match('@[^A-Za-z0-9]@', $password);

    if(strlen($password) < 8) {
        $error = 'Password should be at least 8 characters in length and should include at least one uppercase letter, one lowercase, one number, and one special character.';
        include ('register.php');
        exit;
    }

    if(!$uppercase) {
        $error = 'Password should include an uppercase letter';
        include ('register.php');
        exit;
    }

    if(!$lowercase) {
        $error = 'Password should include a lowercase letter';
        include ('register.php');
        exit;
    }

    if(!$number) {
        $error = 'Password should include a number';
        include ('register.php');
        exit;
    }

    if(!$specialchar)  {
        $error = 'Password should include a special character';
        include ('register.php');
        exit;
    }

    if($password != $confirm_password) {
        $error = 'Passwords do not match';
        include ('register.php');
        exit;
    }
     
    // Escape string values for SQL execution safety
    $safe_username = mysqli_real_escape_string($db, $username);
    $safe_email = mysqli_real_escape_string($db, $email);
    $safe_role = (int)$role;
    $safe_password = mysqli_real_escape_string($db, $password);

    // Insert user into database including their assigned role rank
    $query = "INSERT INTO users (username, email, password, role) VALUES 
    ('$safe_username', '$safe_email', '$safe_password', $safe_role)";

    $result = mysqli_query($db, $query);
    if($result){
        $success = 'Registration successful! The account has been provisioned.';
        // Clear variables on success so form inputs return to blank for the next entry
        $username = $email = $role = '';
        include ('register.php');
        exit;
    }
    else {
        $error = 'Database rejection: This username or email address is already taken.';
        include ('register.php');
        exit;
    }
}
?>