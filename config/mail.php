<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

/*
 * Gmail SMTP CONFIG
 * Replace these values with your own Gmail + App Password
 */

define('SMTP_USER', getenv('SMTP_USER') ?: 'm.yalayalatabua.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');


/**
 * Send OTP Email
 */
function sendOtpEmail($toEmail, $toName, $otp) {

    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Sender & recipient
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Verification Code';

        $mail->Body = "
            <h2>2FA Login Code</h2>
            <p>Hello <b>$toName</b>,</p>
            <p>Your OTP code is:</p>
            <h1 style='letter-spacing:5px;'>$otp</h1>
            <p>This code expires in 10 minutes.</p>
        ";

        $mail->AltBody = "Your OTP code is: $otp";

        $mail->send();
        return true;

    } catch (Exception $e) {
    die("MAIL ERROR: " . $mail->ErrorInfo);
}
}