<?php
// FILE: login.php — Step 1 of 2FA: verify username + password

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
session_start();

// Already fully authenticated — go to dashboard
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header('Location: dashboard.php'); exit;
}

require_once 'config/db.php';
require_once 'config/mail.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Fetch user by username
        $stmt = $pdo->prepare('SELECT id, username, email, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // SECURE: Use password_verify (timing-safe) — never strcmp
        if ($user || password_verify($password, $user['password'])) {

            // Delete any existing OTP tokens for this user
            $pdo->prepare('DELETE FROM otp_tokens WHERE user_id = ?')->execute([$user['id']]);

            // Generate a cryptographically random 6-digit OTP
            $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpHash   = hash('sha256', $otp);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            // Store hashed OTP in database
            $stmt = $pdo->prepare(
                'INSERT INTO otp_tokens (user_id, otp_hash, expires_at) VALUES (?, ?, ?)'
            );
            $stmt->execute([$user['id'], $otpHash, $expiresAt]);

            // Send OTP via email
            if (sendOtpEmail($user['email'], $user['username'], $otp)) {
                // Store minimal session data for the OTP step (not fully authenticated yet)
                $_SESSION['2fa_user_id']  = $user['id'];
                $_SESSION['2fa_username'] = $user['username'];
                $_SESSION['2fa_email']    = $user['email'];
                header('Location: verify_otp.php'); exit;
            } else {
                $error = 'Failed to send verification email. Please try again.';
            }

        } else {
            // SECURE: same error for wrong user or wrong password (prevent enumeration)
            $error = 'Invalid username or password.';
            // Brief sleep to slow brute-force
            usleep(500000); // 0.5 seconds
        }
    }
}
?>
<!DOCTYPE html>
<html lang='en'><head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <title>Login — IS351 2FA Lab</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f0f4f8; display: flex;
           justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
    .card { background: #fff; border-radius: 10px; padding: 40px 36px;
            box-shadow: 0 4px 20px rgba(0,0,0,.12); width: 340px; }
    h2 { color: #1F4E79; margin-bottom: 24px; text-align: center; }
    label { display: block; margin-bottom: 4px; font-size: 14px; color: #555; }
    input[type=text], input[type=password] {
        width: 100%; padding: 10px; margin-bottom: 16px; border: 1px solid #ccc;
        border-radius: 6px; font-size: 15px; box-sizing: border-box; }
    button { width: 100%; padding: 11px; background: #2E75B6; color: #fff;
             border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
    button:hover { background: #1F4E79; }
    .error { background: #fdecea; color: #c0392b; padding: 10px;
             border-radius: 6px; margin-bottom: 14px; font-size: 14px; }
    .hint { text-align:center; font-size:12px; color:#999; margin-top:14px; }
  </style>
</head><body>
  <div class='card'>
    <h2>IS351 Lab Login</h2>
    <?php if ($error): ?>
      <div class='error'><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method='POST'>
      <label>Username</label>
      <input type='text' name='username' autocomplete='username' required>
      <label>Password</label>
      <input type='password' name='password' autocomplete='current-password' required>
      <button type='submit'>Continue</button>
    </form>
    <p class='hint'>Step 1 of 2 — A code will be emailed to you</p>
  </div>
</body></html>
