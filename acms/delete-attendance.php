<?php

require_once __DIR__ . '/connection/connect.php';

$id = $_GET['id'];

$query = "delete from attendance where id = '$id'";
$result = mysqli_query($db, $query);

if ($result) {
    $success = "Attendance record has been deleted";
    include('attendance_log.php');
    exit;
} else {
    $error = "Cannot run query";
    include('attendance_log.php');
    exit;
}