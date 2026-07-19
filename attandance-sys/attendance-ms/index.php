<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php'; // ডাটাবেজ কানেকশন কল করা হলো
$search = isset($_GET['search']) ? $_GET['search'] : '';

echo "<h1>Teacher Attendance Portal</h1>";
echo "<h3>Welcome, <a href='profile.php?id=" . $_SESSION['teacher_id'] . "'>" . $_SESSION['teacher_name'] . "</a></h3>";
echo "<a href='logout.php'>Logout</a><br><br>";

// Search Form
echo "<form method='GET' action='index.php'>
        Search Records: <input type='text' name='search' value='$search' placeholder='Student name...'>
        <input type='submit' value='Search'>
      </form><br>";

if (!empty($search)) {
    echo "<p>Showing search results for: <strong>" . $search . "</strong></p>"; // Reflected XSS
}

// Active Subject List
$sql = "SELECT s.id AS sub_id, s.subject_name, d.dept_name FROM subjects s JOIN departments d ON s.dept_id = d.id";
$result = $conn->query($sql);

echo "<h3>Start New Attendance:</h3>";
echo "<table border='1' cellpadding='5'><tr><th>Department</th><th>Subject</th><th>Action</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>".$row['dept_name']."</td>
            <td>".$row['subject_name']."</td>
            <td><a href='sheet.php?subject_id=".$row['sub_id']."'>Open Attendance Sheet</a></td>
          </tr>";
}
echo "</table><br><hr>";

// View Logs
echo "<h3>Past Attendance Logs:</h3>";
$log_sql = "SELECT r.id, s.student_name, sub.subject_name, r.status, r.comment 
            FROM attendance_records r 
            JOIN students s ON r.student_id = s.id 
            JOIN subjects sub ON r.subject_id = sub.id";

if (!empty($search)) { $log_sql .= " WHERE s.student_name LIKE '%$search%'"; }

$log_result = $conn->query($log_sql);
echo "<table border='1' cellpadding='5'><tr><th>Student</th><th>Subject</th><th>Status</th><th>Comment</th></tr>";
while($log = $log_result->fetch_assoc()) {
    echo "<tr>
            <td>".$log['student_name']."</td>
            <td>".$log['subject_name']."</td>
            <td>".$log['status']."</td>
            <td>".$log['comment']."</td> <!-- Stored XSS -->
          </tr>";
}
echo "</table>";
?>
