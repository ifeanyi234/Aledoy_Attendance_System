<?php
    include ('connection/connect.php');
    
    if(isset($_POST['sub'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($username == '' || $email == '' || $password == '' || $confirm_password == '') {
        $error = 'All information are required';
        include ('register.php');
        exit;
    }
  

    // if email doesn't include @
    $email = filter_var($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
        include ('register.php');
        exit;
    }
    
    //validate password strength
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialchar = preg_match('@[^A-Za-z0-9]@', $password);
    // $specialchar = preg_match('@[^\w]@', $password);

    
    //password length
    if(strlen($password) < 8) {
        $error = 'Password should be at least 8 characters in length and should include at least one uppercase letter, one lowercase, one number, and one special character.';
        include ('register.php');
        exit;
    }

    // if password doesn't include uppercase
    if(!$uppercase) {
        $error = 'Password should include a uppercase';
        include ('register.php');
        exit;
    }

    // if password doesn't include lowercase
    if(!$lowercase) {
        $error = 'Password should include a lowercase';
        include ('register.php');
        exit;
    }

    // if password doesn't include number
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

    //password confirmation
    if($password != $confirm_password) {
        $error = 'Password do not match';
        include ('register.php');
        exit;
    }
     

    $query = "insert into users (username, password) values 
    ('$username', '$password' )";

    $result = mysqli_query($db, $query);
    if($result){
        $success = 'Registration successful, login to continue';
        include ('register.php');
        exit;
    }
    else {
        $error = 'This email has already been registered';
        include ('register.php');
        exit;
    }
}


?>