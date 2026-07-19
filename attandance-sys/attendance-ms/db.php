<?php
// নতুন ক্রিয়েট হওয়া 'ripon' ইউজারের ক্রেডেনশিয়ালস দিয়ে কানেকশন সেটআপ
$host = "localhost";
$user = "ripon";
$password = "ripon123"; 
$database = "cyber_lab";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
