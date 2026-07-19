<?php
// ল্যাবের জন্য এরর রিপোর্টিং চালু থাকবে কিন্তু ফ্যাটাল ক্র্যাশ হ্যান্ডেল করা হবে
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';

$subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : 1; 

// ডিফল্ট ভেরিয়েবল ইনিশিয়ালাইজেশন
$dept_id = "0"; 
$subject_name = "Unknown Module";
$db_error = "";
$sub_res = false;

// FIX: try-catch ব্লক ব্যবহার করা হলো যাতে mysqli_sql_exception পেজটিকে সম্পূর্ণ ক্র্যাশ (White Screen) না করায়
try {
    // VULNERABILITY (SQL Injection): সরাসরি সিঙ্গেল কোটের ভেতরে ইউআরএল ইনপুট পাস
    $sub_query = "SELECT * FROM subjects WHERE id = '$subject_id'";
    $sub_res = $conn->query($sub_query);

    if ($sub_res && $sub_res->num_rows > 0) {
        $subject = $sub_res->fetch_assoc();
        $dept_id = $subject['dept_id'];
        $subject_name = $subject['subject_name'];
    }
} catch (mysqli_sql_exception $e) {
    // ক্র্যাশ হওয়া এরর মেসেজটি এখানে ক্যাপচার করা হলো
    $db_error = $e->getMessage();
    $dept_id = "0";
    $subject_name = "SQL Syntax Error Detected";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>BUBT ERP - Take Attendance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- অফিশিয়াল টপ নেভিগেশন বার - এটি সর্বদা সুন্দরভাবে স্ক্রিনে থাকবে (৪০% ইন্টারফেস সেভ থাকবে) -->
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
        <?php
        // যদি কুয়েরি ফেইল হয় (যেমন সিঙ্গেল কোট ইনজেক্ট করলে), তবে কার্ড ও টেবিল লেআউট গায়েব হয়ে যাবে (৬০% চেঞ্জ)
        if (!$sub_res || $dept_id === "0") {
            echo "<div class='card' style='border-top: 5px solid #ef4444; padding:40px; text-align:center;'>";
            echo "<h2 style='color:#ef4444; margin-bottom:10px;'>⚠️ SQL Syntax Exception Raised</h2>";
            echo "<p style='color:#4b5563; font-size:15px; margin-bottom:20px;'>The application statement has failed parsing rules due to an unescaped string injection vector.</p>";
            echo "<span style='font-size:13px; font-weight:bold; color:#ef4444; background:#fef2f2; padding:12px; border:1px solid #fca5a5; border-radius:6px; display:inline-block; font-family:monospace; text-align:left; max-width:100%; overflow-x:auto;'>";
            // স্ক্রিনশটের সেই আসল ডাটাবেজ এরর মেসেজটি এখানে সুন্দর ডিজাইনের বক্সে শো করবে
            echo "Database Response Trigger: " . ($db_error ? $db_error : $conn->error);
            echo "</span>";
            echo "</div>";
        } else {
            // কুয়েরি ঠিক থাকলে বা কমেন্ট দিয়ে ব্যালেন্স করলে এই টেবিলটি ১০০% লোড হবে
            $student_query = "SELECT * FROM students WHERE dept_id = '$dept_id'";
            $students = $conn->query($student_query);
        ?>
        <div class="card">
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
        </div>
        <?php } ?>
    </div>
</body>
</html>
