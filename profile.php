cat << 'EOF' > /var/www/html/labproject/mysocial/profile.php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';
$profile_id = isset($_GET['id']) ? $_GET['id'] : 1; 
$show_popup = false;

if (isset($_POST['update_bio']) && isset($_SESSION['user_id'])) {
    $new_bio = $_POST['bio']; $current_user = $_SESSION['user_id'];
    mysqli_query($conn, "UPDATE users SET bio = '$new_bio' WHERE id = $current_user");
    header("Location: profile.php?id=" . $current_user); exit;
}

// CSRF ও পাসওয়ার্ড চেঞ্জ হ্যান্ডলিং
if (isset($_POST['change_password']) && isset($_SESSION['user_id'])) {
    $new_password = $_POST['new_password']; $current_user = $_SESSION['user_id'];
    mysqli_query($conn, "UPDATE users SET password = '$new_password' WHERE id = $current_user");
    
    // রিকোয়েস্ট সফল হওয়ার পর কারেন্ট ভিকটিম ইউজারের আইডিতেই পেজটি রিডাইরেক্ট হবে
    $profile_id = $current_user;
    $show_popup = true; 
}

// SQLi Vulnerable Query with Exception Handling
$query = "SELECT * FROM users WHERE id = '$profile_id'";
$user = null;
$db_error = null;

try {
    $result = mysqli_query($conn, $query);
    if ($result) {
        $user = mysqli_fetch_assoc($result);
    }
} catch (mysqli_sql_exception $e) {
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - MySocial</title>
    <style>
        body { font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif; background: #f0f2f5; margin: 0; color: #1c1e21; }
        
        /* ন্যাভবার ডিজাইন */
        .navbar { background: #ffffff; padding: 0 40px; height: 60px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 12px rgba(0,0,0,0.05); position: sticky; top:0; z-index:100; }
        .navbar h2 { color: #1877f2; margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; }
        .nav-links { display: flex; align-items: center; }
        .nav-links a { margin-right: 25px; text-decoration: none; color: #65676b; font-weight: 600; font-size: 15px; transition: color 0.2s; }
        .nav-links a:hover { color: #1877f2; }
        .nav-links a.logout { color: #f02849; background: #fde8eb; padding: 6px 12px; border-radius: 6px; }
        .nav-links a.logout:hover { background: #fbcacf; }
        .search-form input { padding: 10px 18px; border: none; background: #f0f2f5; border-radius: 50px; outline: none; width: 220px; font-size: 14px; transition: all 0.2s; }
        .search-form input:focus { background: #e4e6eb; width: 260px; }

        .profile-container { max-width: 650px; margin: 40px auto; background: white; padding: 35px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 1px solid #e4e6eb; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #1877f2; text-decoration: none; font-weight: 600; font-size: 15px; }
        h2.username { font-size: 28px; margin: 0 0 15px 0; color: #050505; border-bottom: 2px solid #1877f2; display: inline-block; padding-bottom: 5px; }
        .bio-box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #1877f2; }
        .bio-box strong { color: #65676b; font-size: 14px; text-transform: uppercase; }
        .bio-box p { margin: 8px 0 0 0; font-size: 16px; line-height: 1.5; color: #050505; }
        .form-section { margin-top: 30px; padding-top: 25px; border-top: 1px solid #e4e6eb; }
        .form-section h3 { margin: 0 0 15px 0; color: #050505; font-size: 18px; }
        input, textarea { width: 100%; padding: 12px; margin: 8px 0 15px 0; border: 1px solid #e4e6eb; border-radius: 8px; box-sizing: border-box; font-size: 15px; background: #f8f9fa; outline: none; }
        input:focus, textarea:focus { border-color: #1877f2; background: #fff; }
        button { background: #1877f2; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px; transition: background 0.2s; }
        button:hover { background: #166fe5; }
        .post-item { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 12px; border: 1px solid #e4e6eb; font-size: 15px; }
        .error-debug { color: #f02849; padding: 12px; background: #fde8eb; border-radius: 8px; margin-top: 15px; font-weight: 500; font-family: monospace; font-size: 13px; border: 1px solid #fbcacf; }
        
        /* কাস্টম প্রিমিয়াম পপ-আপ নোটিফিকেশন */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .modal-box { background: white; padding: 30px; border-radius: 12px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-width: 400px; width: 90%; animation: slideDown 0.3s ease; }
        .modal-box h3 { color: #2ecc71; margin-top: 0; font-size: 22px; }
        .modal-box p { color: #555; font-size: 15px; line-height: 1.4; margin-bottom: 20px; }
        .modal-close-btn { background: #2ecc71; color: white; padding: 10px 25px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .modal-close-btn:hover { background: #27ae60; }
        @keyframes slideDown { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>MySocial</h2>
        <div class="nav-links">
            <a href="index.php">Home Feed</a>
            <a href="profile.php?id=<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; ?>">My Profile</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        <form action="search.php" method="GET" class="search-form">
            <input type="text" name="query" placeholder="Search across feed...">
        </form>
    </div>

    <?php if ($show_popup): ?>
        <div class="modal-overlay" id="customAlert">
            <div class="modal-box">
                <div style="font-size: 45px; margin-bottom: 10px;">🛡️</div>
                <h3>Success!</h3>
                <p>Password updated successfully!</p>
                <button class="modal-close-btn" onclick="document.getElementById('customAlert').style.display='none'">Awesome</button>
            </div>
        </div>
    <?php endif; ?>

    <div class="profile-container">
        <a href="index.php" class="back-link">⬅ Back to News Feed</a>
        
        <?php if ($user && !$db_error): ?>
            <br>
            <h2 class="username">@<?php echo $user['username']; ?></h2>
            <div class="bio-box">
                <strong>User Biography</strong>
                <p><?php echo $user['bio']; ?></p>
            </div>

            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id']): ?>
                <div class="form-section">
                    <h3>Update Bio Description</h3>
                    <form method="POST">
                        <textarea name="bio" placeholder="Write something compelling about yourself..." rows="2"></textarea>
                        <button type="submit" name="update_bio">Save Profile Bio</button>
                    </form>
                </div>

                <div class="form-section">
                    <h3>Account Security</h3>
                    <form method="POST">
                        <input type="password" name="new_password" placeholder="Enter robust new password" required>
                        <button type="submit" name="change_password">Change Account Password</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="form-section">
                <h3>Recent Shared Publications</h3>
                <?php
                $posts_res = mysqli_query($conn, "SELECT * FROM posts WHERE user_id = '" . $user['id'] . "' ORDER BY created_at DESC");
                while ($post = mysqli_fetch_assoc($posts_res)) {
                    echo "<div class='post-item'><strong>" . date('M d, Y', strtotime($post['created_at'])) . "</strong> — " . $post['content'] . "</div>";
                }
                ?>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 40px 0;">
                <div style="font-size: 50px;">🔍</div>
                <h3 style="color:#65676b; margin-top:10px;">User Profile Data Currently Empty or Disrupted</h3>
                <p style="color:#8a8d91; font-size:14px;">The request parameters broke the inner semantic database logic.</p>
            </div>
            
            <?php if ($db_error || mysqli_error($conn)): ?>
                <div class="error-debug">
                    <strong>⚠️ Database Error Log:</strong><br>
                    <?php echo $db_error ? $db_error : mysqli_error($conn); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
EOF
