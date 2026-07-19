<?php
session_start();
include 'db.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // VULNERABILITY (SQL Injection - Preserved for Lab Testing)
    $sql = "SELECT * FROM teachers WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $teacher = $result->fetch_assoc();
        $_SESSION['teacher_id'] = $teacher['id'];
        $_SESSION['teacher_name'] = $teacher['name'];
        setcookie("PHPSESSID_LAB", "session_token_xyz_authenticated_user_secret", time() + 3600, "/");
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Faculty Credentials!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>BUBT - Attendance Management System</title>
    <!-- Links to your untouched style.css matrix -->
    <link rel="stylesheet" href="style.css">
    <style>
        /* Dedicated layout wrappers for the redesigned login page */
        .login-page-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f3f4f6 0%, #cbd5e1 100%);
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .login-card-box {
            max-width: 420px;
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border-top: 5px solid #10b981;
        }

        .bubt-branding-banner {
            background: linear-gradient(135deg, #0B3C5D 0%, #1e40af 100%);
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
            border-bottom: 4px solid #10b981;
        }

        .bubt-branding-banner h2 {
            margin: 0;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #ffffff;
        }

        .bubt-branding-banner p {
            margin: 6px 0 0 0;
            font-size: 11px;
            color: #93c5fd;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .login-padded-content {
            padding: 35px 30px;
        }

        .login-padded-content h3 {
            margin: 0 0 25px 0;
            color: #0B3C5D;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }

        .input-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .custom-error-alert {
            background: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fca5a5;
            font-size: 13px;
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>
<body class="login-page-wrapper">
    <div class="login-card-box">
        <!-- BUBT Varsity Banner Module -->
        <div class="bubt-branding-banner">
            <h2>BUBT</h2>
            <p>Attendance Management System</p>
        </div>
        
        <div class="login-padded-content">
            <h3>Faculty Portal Portal Secure Sign In</h3>
            
            <?php if(!empty($error)) echo "<div class='custom-error-alert'>$error</div>"; ?>
            
            <form method="POST" action="login.php">
                <label class="input-label">Faculty Username</label>
                <!-- Re-uses input-field properties from style.css cleanly -->
                <input type="text" name="username" class="input-field" placeholder="e.g. ripon" style="width:100%; margin-bottom:20px; box-sizing: border-box;" required autocomplete="off">
                
                <label class="input-label">Account Password</label>
                <input type="password" name="password" class="input-field" placeholder="••••••••" style="width:100%; margin-bottom:25px; box-sizing: border-box;" required>
                
                <!-- Re-uses btn-primary configuration from style.css cleanly -->
                <input type="submit" value="Sign In Securely" class="btn-primary" style="width:100%; padding:14px; font-weight:700;">
            </form>
        </div>
    </div>
</body>
</html>
