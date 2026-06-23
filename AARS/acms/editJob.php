<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('connection/connect.php');
require_once('fns.php');

$date = date('Y-m-d h:i:s');
$title = mysqli_real_escape_string($db, $_POST['title']);
$qualification = ($_POST['qualification']);
$experience = ($_POST['experience']);
$state = mysqli_real_escape_string($db, $_POST['state']);
$country = mysqli_real_escape_string($db, $_POST['country']);
$location = mysqli_real_escape_string($db, $_POST['location']);
$job_type = mysqli_real_escape_string($db, $_POST['job_type']);
$salary = mysqli_real_escape_string($db, $_POST['salary']);
$deadline = mysqli_real_escape_string($db, $_POST['deadline']);
$job_url = mysqli_real_escape_string($db, $_POST['job_url']);
$description = mysqli_real_escape_string($db, $_POST['description']);
$skills = ($_POST['skills']);
$responsibilities = ($_POST['responsibilities']);
$token =  mysqli_real_escape_string($db, $_POST['token']);

$requiredFiles = [
    'title' => $title,
    'state' => $state,
    'country' => $country,
    'location' => $location,
    'job_type' => $job_type,
    'salary' => $salary,
    'deadline' => $deadline,
    'job_url' => $job_url,
    'qualification' => $qualification,
    'experience' => $experience,
    'description' => $description,
    'skills' => $skills,
    'responsibilities' => $responsibilities
];

foreach ($requiredFiles as $key => $value) {
    if (empty($value)) {
        $error = ucfirst(str_replace('_', ' ', $key)) . " is required";
        include('edit-job.php');
        exit;
    }
}

$query = "UPDATE job_post SET 
job_title = '$title',
state = '$state',
country = '$country',
location = '$location',
job_type = '$job_type',
salary = '$salary',
deadline = '$deadline',
job_url = '$job_url',
qualification = '$qualification',
experience = '$experience',
description = '$description',
skills = '$skills',
responsibilities = '$responsibilities',
date_posted = '" . date('Y-m-d') . "',
token = '$token',
user = '" . $_SESSION['acms_valid_user'] . "'
WHERE token = '$token'";


$result = mysqli_query($db, $query);
if ($result) {
    $success = 'Job has been edited successfully';
    include('edit-job.php');
    exit;
} else {
    $error = 'An error occured';
    include('edit-job.php');
    exit;
}
