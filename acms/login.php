<?php
require_once __DIR__ . '/includes/app.php';

startAppSession();
if (isAuthenticated()) {
    redirectTo('dashboard.php');
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css_login/bootstrap.min.css">
    <link rel="stylesheet" href="css/login_style.css">
    <title>login</title>
  </head>
  <body>
    <div class="container">
      <div class="content-body">
        <div class="content-header">
          <p class="big-font">Login</p>
        </div>
        <div class="content-inner">
        <div class="form">
            <form action='proc-login.php' method='post'>
            <?php if (!empty($_SESSION['username_err'])) { ?>
              <div class="alert alert-danger">Username Required</div>
            <?php unset($_SESSION['username_err']); }
            if (!empty($_SESSION['password_err'])) { ?>
              <div class="alert alert-danger">Password required</div>
            <?php unset($_SESSION['password_err']); }
            if (!empty($_SESSION['passwordlength_err'])) { ?>
              <div class="alert alert-danger">Password must be more than 8</div>
            <?php unset($_SESSION['passwordlength_err']); }
            if (!empty($_SESSION['login_error'])) { ?>
              <div class='alert alert-danger'>Login failed... invalid credentials</div>
            <?php unset($_SESSION['login_error']); } ?>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value='<?php echo isset($_SESSION['login_username']) ? htmlspecialchars($_SESSION['login_username']) : ""; unset($_SESSION['login_username']); ?>'>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" value=''>

            <input type="submit" value="Login">
          </form>
        </div>

        </div>
      </div>
    </div>
  </body>
</html>
