<?php
// FILE: logout.php
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
session_start();
session_unset();
session_destroy();
setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/',
          'httponly' => true, 'samesite' => 'Strict']);
header('Location: login.php');
exit;
?>
