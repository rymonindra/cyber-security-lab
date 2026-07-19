<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$subject_filter = isset($_GET['subject_filter']) ? $_GET['subject_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>BUBT ERP - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .logs-wrapper { max-height: 350px; overflow-y: auto; border: 1px solid var(--border); border-radius: 8px; }
        .logs-wrapper th { position: sticky; top: 0; background-color: #f8fafc; z-index: 5; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-brand">
            <h1>BUBT Central Network</h1>
            <p>Faculty Attendance Management System</p>
        </div>
        <div class="user-meta">
            <!-- VULNERABILITY (IDOR): ID prints directly in the GET parameter -->
            <a href="profile.php?id=<?php echo $_SESSION['teacher_id']; ?>" class="profile-link">👤 <?php echo $_SESSION['teacher_name']; ?></a>
            <a href="logout.php" class="btn-danger">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h3 style="margin-bottom:15px; font-size:16px; color:var(--text-muted);">Search Filters</h3>
            <form method="GET" action="dashboard.php" class="filter-form-wrapper">
                <input type="text" name="search" value="<?php echo $search; ?>" class="input-field" placeholder="Search student name..." style="flex:2;">
                
                <select name="subject_filter" class="input-field" style="flex:1;">
                    <option value="">All Modules</option>
                    <?php
                    $sub_list_res = $conn->query("SELECT id, subject_name FROM subjects");
                    while($sub_row = $sub_list_res->fetch_assoc()) {
                        $selected = ($subject_filter == $sub_row['id']) ? 'selected' : '';
                        echo "<option value='".$sub_row['id']."' $selected>".$sub_row['subject_name']."</option>";
                    }
                    ?>
                </select>

                <input type="text" name="date_filter" value="<?php echo $date_filter; ?>" class="input-field" placeholder="YYYY-MM-DD" style="flex:1;">
                <input type="submit" value="Apply Filters" class="btn-primary">
            </form>
        </div>

        <?php if (!empty($search)) { ?>
            <!-- VULNERABILITY (Reflected XSS): Echoing raw input string straight to screen -->
            <p>Showing search results for student: <strong><?php echo $search; ?></strong></p>
        <?php } ?>

        <div class="card">
            <h2>Active Registered Courses</h2>
            <table>
                <tr><th>Department</th><th>Subject Module</th><th>Action Log</th></tr>
                <?php
                $result = $conn->query("SELECT s.id AS sub_id, s.subject_name, d.dept_name FROM subjects s JOIN departments d ON s.dept_id = d.id");
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td><strong>".$row['dept_name']."</strong></td>
                            <td>".$row['subject_name']."</td>
                            <td><a href='sheet.php?subject_id=".$row['sub_id']."' class='btn-success'>Take Attendance</a></td>
                          </tr>";
                }
                ?>
            </table>
        </div>

        <div class="card">
            <h2>Archived History Log</h2>
            <div class="logs-wrapper">
                <table>
                    <tr><th>Student Entity</th><th>Course Domain</th><th>Status</th><th>Faculty Note</th><th>Date Block</th></tr>
                    <?php
                    $log_sql = "SELECT r.id, s.student_name, sub.subject_name, r.status, r.comment, r.date 
                                FROM attendance_records r 
                                JOIN students s ON r.student_id = s.id 
                                JOIN subjects sub ON r.subject_id = sub.id";
                    $conditions = [];
                    if (!empty($search)) $conditions[] = "s.student_name LIKE '%$search%'";
                    if (!empty($subject_filter)) $conditions[] = "r.subject_id = $subject_filter";
                    
                    if (!empty($date_filter)) {
                        // VULNERABILITY (SQL Injection): Raw input appended straight into active SQL execution
                        $log_sql .= " AND r.date = '$date_filter'";
                    }
                    
                    if (count($conditions) > 0) $log_sql .= " WHERE " . implode(' AND ', $conditions);
                    $log_sql .= " ORDER BY r.date DESC, r.id DESC";

                    $log_result = $conn->query($log_sql);
                    if ($log_result && $log_result->num_rows > 0) {
                        while($log = $log_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $log['student_name'] . "</td>";
                            echo "<td>" . $log['subject_name'] . "</td>";
                            echo "<td>" . $log['status'] . "</td>";
                            // VULNERABILITY (Stored XSS): Must print without ANY filter blocks or css modifications
                            echo "<td>" . $log['comment'] . "</td>";
                            echo "<td><code>" . $log['date'] . "</code></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;'>No historical records found.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
