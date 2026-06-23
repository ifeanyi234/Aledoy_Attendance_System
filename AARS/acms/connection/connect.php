<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
date_default_timezone_set('Africa/Lagos'); 

$servername = "localhost";
$databaseUsername = "aledoy_attendance_register_system";
$databasePassword = "attendance_"; // using local XAMPP default
$database = "aledoy_attendance_register_system";

$db = mysqli_connect($servername, $databaseUsername, $databasePassword, $database) or die('Database Connection Failed: ' . mysqli_connect_error());

// set charset
mysqli_set_charset($db, 'utf8mb4');

