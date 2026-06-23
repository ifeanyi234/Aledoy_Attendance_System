<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../attendsystem.css">
    <link rel="icon" href="../Images/icon.png" type="image/x-icon">
    <title>Aledoy Attendance</title>
</head>
<body>
    <div class="form-container">
        <div class="logo-badge">
            <img src="../Images/images-removebg-preview.png" alt="Company Logo" class="company-logo">
        </div>
        
        <h2>Admin Login</h2>
        
        <form action="proc-login.php" method="POST">
          <?php if (!empty($_SESSION['username_err'])) { ?>
              <div class="alert alert-danger">Username Required</div>
            <?php unset($_SESSION['username_err']); }?>
            <?php if (!empty($_SESSION['password_err'])) { ?>
              <div class="alert alert-danger">Password required</div>
            <?php unset($_SESSION['password_err']); }?>
            <?php if (!empty($_SESSION['passwordlength_err'])) { ?>
              <div class="alert alert-danger">Password must be more than 8</div>
            <?php unset($_SESSION['passwordlength_err']); }?>
            <?php if (!empty($_SESSION['login_error'])) { ?>
              <div class='alert alert-danger'>Login failed... invalid credentials</div>
            <?php unset($_SESSION['login_error']); } ?>
            <div class="input-group">
                <input type="text" id="username" name="username" placeholder="Username" required value='<?php echo isset($_SESSION['login_username']) ? htmlspecialchars($_SESSION['login_username']) : ""; unset($_SESSION['login_username']); ?>'>
            </div>
            
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>

            <input type="submit" class="submit-btn" value="Login">
            
        </form>
    </div>
</body>
</html>