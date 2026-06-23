<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include ('connection/connect.php');

    if(!isset($_SESSION['acms_valid_user'])) {
        include('index.php');
        exit;

    }
        header("refresh:10;url= dashboard.php");



?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="robots" content="noindex,nofollow">
    <title>Login</title>
   
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/fav_icon.png">
    <!-- Custom CSS -->
    <link href="css/style.min.css" rel="stylesheet">

    <!-- login form -->
    <link href="css_login/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css_login/startmin.css" rel="stylesheet">

    </head>
    <body>

        <div class="container">
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <div class="login-panel panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">You're are logged in as <?php echo $username ;?>
                             </h3><br>
                            You will be redirected in the next 10 seconds. If the system doesn't automatically redirect you
                            <a href="dashboard.php">Click here<br>
                            
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- jQuery -->
        <script src="../js/jquery.min.js"></script>

        <!-- Bootstrap Core JavaScript -->
        <script src="../js/bootstrap.min.js"></script>

        <!-- Metis Menu Plugin JavaScript -->
        <script src="../js/metisMenu.min.js"></script>

        <!-- Custom Theme JavaScript -->
        <script src="../js/startmin.js"></script>

    </body>
</html>
