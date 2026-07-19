<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db.php';

    $subject_id = $_POST['subject_id'];
    $statuses = $_POST['status']; 
    $comments = $_POST['comment']; 
    $date = date('Y-m-d');

    foreach ($statuses as $student_id => $status) {
        $student_comment = $comments[$student_id];

        $sql = "INSERT INTO attendance_records (student_id, subject_id, status, comment, date) 
                VALUES ('$student_id', '$subject_id', '$status', '$student_comment', '$date')";
        $conn->query($sql);
    }

    echo "<h2>Attendance records processed successfully!</h2>";
    echo "<a href='dashboard.php'>Return to Dashboard</a>";
}
?>
