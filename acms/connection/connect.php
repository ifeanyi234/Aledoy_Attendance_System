<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
date_default_timezone_set('Africa/Lagos'); 

$servername = "localhost";
$databaseUsername = "aledusbx_attendance";
$databasePassword = "Aledoy@2026!"; // using local XAMPP default
$database = "aledusbx_attendance";

$db = mysqli_connect($servername, $databaseUsername, $databasePassword, $database) or die('Database Connection Failed: ' . mysqli_connect_error());

// set charset
mysqli_set_charset($db, 'utf8mb4');

