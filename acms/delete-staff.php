<?php

require_once __DIR__ . '/connection/connect.php';

$id = $_GET['id'];

$query = "delete from staff where id = '$id'";
$result = mysqli_query($db, $query);

if ($result) {
    $success = "Staff record has been deleted";
    include('staff-list.php');
    exit;
} else {
    $error = "Cannot run query";
    include('staff-list.php');
    exit;
}