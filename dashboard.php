<?php
// FILE: dashboard.php — Only accessible after successful 2FA

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
session_start();

define('SESSION_TIMEOUT', 1800); // 30 minutes

// Guard: must be fully authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: login.php'); exit;
}

// Session timeout check
if (time() - $_SESSION['last_active'] > SESSION_TIMEOUT) {
    session_unset(); session_destroy();
    header('Location: login.php?reason=timeout'); exit;
}
$_SESSION['last_active'] = time();

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang='en'><head>
  <meta charset='UTF-8'>
  <title>Dashboard — IS351 2FA Lab</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f0f4f8; margin: 0; }
    .header { background: #1F4E79; color: #fff; padding: 16px 32px;
              display: flex; justify-content: space-between; align-items: center; }
    .header h1 { margin: 0; font-size: 20px; }
    .badge { background: #27ae60; color: #fff; padding: 4px 12px;
             border-radius: 20px; font-size: 13px; }
    .content { max-width: 700px; margin: 40px auto; padding: 0 20px; }
    .card { background: #fff; border-radius: 10px; padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08); margin-bottom: 20px; }
    .card h2 { color: #2E75B6; margin-top: 0; }
    .logout { display: inline-block; padding: 10px 24px; background: #e74c3c;
              color: #fff; text-decoration: none; border-radius: 6px; }
  </style>
</head><body>
  <div class='header'>
    <h1>IS351 2FA Lab — Dashboard</h1>
    <span class='badge'>2FA Verified</span>
  </div>
  <div class='content'>
    <div class='card'>
      <h2>Welcome, <?= $username ?>!</h2>
      <p>You have successfully authenticated using <strong>Two-Factor Authentication</strong>.</p>
      <p>Both factors were verified:</p>
      <ul>
        <li>Factor 1: Username + Password</li>
        <li>Factor 2: One-Time Code via Email</li>
      </ul>
      <p>Session will auto-expire after 30 minutes of inactivity.</p>
    </div>
    <a href='logout.php' class='logout'>Logout</a>
  </div>
</body></html>
