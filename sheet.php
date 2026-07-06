cat << 'EOF' > /var/www/html/labproject/attandance-sys/attendance-ms/sheet.php
<?php
// Enable verbose database error output for SQLMap automated mapping
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';
// Session check bypassed to allow cookie-less scanning via your specific tool setup
// if (!isset($_SESSION['teacher_id'])) { header("Location: login.php"); exit(); }

$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : 1; 

// VULNERABILITY (SQL Injection): Raw single-quoted string evaluation
// Allows your sir's manual verification (3') to break 60% of the page
$sub_query = "SELECT * FROM subjects WHERE id = '$subject_id'";
$sub_res = $conn->query($sub_query);

$dept_id = "0"; 
$subject_name = "Unknown Module";

if ($sub_res && $sub_res->num_rows > 0) {
    $subject = $sub_res->fetch_assoc();
    $dept_id = $subject['dept_id'];
    $subject_name = $subject['subject_name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>BUBT ERP - Take Attendance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Top Nav remains intact (40% structure stays visible during syntax crash) -->
    <div class="navbar">
        <div class="navbar-brand">
            <h1>Course Roster: <?php echo $subject_name; ?></h1>
            <p>Verification Sheet | Current Server Date: <?php echo date('Y-m-d'); ?></p>
        </div>
        <div>
            <a href="dashboard.php" class="btn-danger" style="text-decoration:none; padding:10px 20px; border-radius:6px; font-weight:600; font-size:13px; display:inline-block;">⬅ Back to Dashboard</a>
        </div>
    </div>

    <div class="container" style="max-width:950px;">
        <div class="card">
            <?php
            $students = false;
            if ($dept_id !== "0") {
                $student_query = "SELECT * FROM students WHERE dept_id = '$dept_id'";
                $students = $conn->query($student_query);
            }

            // VULNERABILITY EXECUTION: If query syntax breaks, the inner table drops completely
            if (!$sub_res || !$students || $students->num_rows == 0) {
                echo "<div style='padding:40px; text-align:center; color:var(--text-muted); font-weight:600;'>";
                echo "⚠️ Query Execution Error or Empty Dataset Mapped.<br><br>";
                echo "<span style='font-size:14px; font-weight:bold; color:#ef4444; background:#fef2f2; padding:10px; border:1px solid #fca5a5; border-radius:6px; display:inline-block;'>";
                echo "MySQL Error Trace: " . $conn->error;
                echo "</span>";
                echo "</div>";
            } else {
            ?>
            <form method="POST" action="submit_attendance.php">
                <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                
                <table>
                    <tr>
                        <th style="width:80px;">UID</th>
                        <th>Full Name</th>
                        <th style="width:200px;">Status Matrix</th>
                        <th>Evaluation Note</th>
                    </tr>
                    <?php 
                    while($student = $students->fetch_assoc()) { 
                        $sid = $student['id'];
                    ?>
                        <tr>
                            <td><code>#00<?php echo $sid; ?></code></td>
                            <td><strong><?php echo $student['student_name']; ?></strong></td>
                            <td>
                                <div class="status-pill-group">
                                    <label><input type="radio" name="status[<?php echo $sid; ?>]" value="Present" checked> Present</label>
                                    <label><input type="radio" name="status[<?php echo $sid; ?>]" value="Absent"> Absent</label>
                                </div>
                            </td>
                            <td>
                                <input type="text" name="comment[<?php echo $sid; ?>]" class="input-field" placeholder="Add custom evaluation comment..." style="width:100%; margin:0; padding:8px 12px; box-sizing: border-box;">
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                
                <div style="margin-top:20px; text-align:right;">
                    <input type="submit" value="Save & Publish Attendance" class="btn-primary" style="width:auto; padding:12px 35px; font-weight:700;">
                </div>
            </form>
            <?php } ?>
        </div>
    </div>
</body>
</html>
EOF
