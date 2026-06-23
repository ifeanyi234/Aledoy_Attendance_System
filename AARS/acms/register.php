<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

 ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/login_style.css">
    <title>login</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  </head>
  <body>
    <div class="container">
      <div class="content-body">
        <div class="content-header">
          <p class="big-font">Register</p>
        </div>
        <div class="content-inner">
        <div class="form">
            <form action='proc-register.php' method='post'>
            <?php if (!empty($_SESSION['username_err'])) { ?>
              <div class="alert alert-danger">Username Required</div>
            <?php unset($_SESSION['username_err']); }?>
            <?php if (!empty($_SESSION['password_err'])) { ?>
              <div class="alert alert-danger">Password required</div>
            <?php unset($_SESSION['password_err']); }?>
            <?php if (!empty($_SESSION['passwordlength_err'])) { ?>
              <div class="alert alert-danger">Password must be more than 8</div>
            <?php unset($_SESSION['passwordlength_err']); }?>
            <?php if (!empty($_SESSION['register_error'])) { ?>
              <div class='alert alert-danger'>Registration failed... username already exists</div>
            <?php unset($_SESSION['register_error']); } ?>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value='<?php echo isset($_SESSION['register_username']) ? htmlspecialchars($_SESSION['register_username']) : ""; unset($_SESSION['register_username']); ?>'>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" value=''>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" value=''

            <input type="submit" value="Register">
          </form>
        </div>

        </div>
      </div>
    </div>
  </body>
</html>

