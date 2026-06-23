<?php

include('connection/connect.php');

$id = $_GET['token'];
$table = $_GET['tab'];
$return = $_GET['return'];

if($table == 'jobs'){
    $table = 'job_post';
}

$query = "delete from $table where token = '$id'";
$result = mysqli_query($db, $query);

if ($result) {
    $success = "Item has been deleted";
    include($return.'.php');
    exit;
} else {
    $error = "Cannot run query";
    include($return.'.php');
    exit;
}
