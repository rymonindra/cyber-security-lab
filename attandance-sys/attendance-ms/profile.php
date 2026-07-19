<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// ফিক্সড লজিক: ইউআরএল-এ আইডি না থাকলে বা সিএসআরএফ অ্যাটাক হলে কারেন্ট লগইন সেশন আইডি ব্যবহার হবে
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // VULNERABILITY (IDOR) - ল্যাবের জন্য আইডিআর বহাল রাখা হলো
    $teacher_id = $_GET['id'];
} else {
    $teacher_id = $_SESSION['teacher_id'];
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    
    // VULNERABILITY (SQL Injection & CSRF): কোনো অ্যান্টি-সিএসআরএফ টোকেন নেই
    // লগইন থাকা ইউজারের আইডিতেই পাসওয়ার্ড আপডেট হবে
    $update_sql = "UPDATE teachers SET password = '$new_password' WHERE id = $teacher_id";
    if ($conn->query($update_sql)) {
        $msg = "<div style='color:#10b981; font-weight:600; margin-bottom:15px;'>Password updated successfully!</div>";
    }
}

$sql = "SELECT * FROM teachers WHERE id = $teacher_id";
$result = $conn->query($sql);
$profile = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Faculty Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-brand">
            <h1>Faculty Information Architecture</h1>
            <p>BUBT Central Directory</p>
        </div>
        <div>
            <a href="dashboard.php" class="btn-danger" style="background:#6b7280; text-decoration:none; padding:10px 20px; border-radius:6px; font-weight:600; font-size:13px;">Dashboard</a>
        </div>
    </div>

    <div class="container" style="max-width: 650px;">
        <div class="card" style="padding: 40px 20px;">
            <div style="width: 100px; height: 100px; background: #e5e7eb; border-radius: 50%; margin: 0 auto 20px auto; display: flex; align-items: center; justify-content: center; font-size: 40px; border: 3px solid var(--bubt-blue);">👤</div>
            <h2 style="text-align:center; margin-bottom:5px;"><?php echo $profile['name']; ?></h2>
            <p style="text-align:center; color: var(--bubt-green); font-weight: 600; margin: 0 0 30px 0; text-transform: uppercase; font-size: 13px; letter-spacing: 1px;"><?php echo $profile['designation']; ?></p>
            
            <table style="text-align: left; margin: 0 0 30px 0;">
                <tr><td style="color: var(--text-muted); font-weight: 600;">Academic Department:</td><td><?php echo $profile['department']; ?></td></tr>
                <tr><td style="color: var(--text-muted); font-weight: 600;">System Access ID:</td><td><code>BUBT-F-00<?php echo $profile['id']; ?></code></td></tr>
                <tr><td style="color: var(--text-muted); font-weight: 600;">Username:</td><td><code><?php echo $profile['username']; ?></code></td></tr>
            </table>

            <hr style="border:0; border-top:1px solid var(--border); margin:30px 0;">
            <h3 style="color:var(--bubt-blue); margin-bottom:15px;">Security Settings</h3>
            <?php echo $msg; ?>
            <form method="POST" action="">
                <label style="font-size:13px; font-weight:600; color:#4b5563; display:block; margin-bottom:6px;">New Account Password</label>
                <input type="password" name="new_password" class="input-field" placeholder="Enter new password" style="width:100%; box-sizing:border-box; margin-bottom:15px;" required>
                <input type="submit" name="update_password" value="Update Password" class="btn-primary" style="width:auto; padding:10px 25px;">
            </form>
        </div>
    </div>
</body>
</html>
