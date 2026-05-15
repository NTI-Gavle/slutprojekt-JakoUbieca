<?php
session_start();
include "db.php";
include "logger.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, password, email FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $hashedPassword, $user_email);
    $stmt->fetch();

    if (password_verify($password, $hashedPassword)) {       
        $deviceToken = isset($_COOKIE['device_token']) ? $_COOKIE['device_token'] : '';          // check device token
        $deviceKnown = false;
        
        if ($deviceToken !== '') {
            $checkDevice = $conn->prepare("SELECT id FROM user_devices WHERE user_id = ? AND device_token = ?");
            $checkDevice->bind_param("is", $id, $deviceToken);
            $checkDevice->execute();
            $checkDevice->store_result();
            if ($checkDevice->num_rows > 0) {
                $deviceKnown = true;
            }
        }

        if ($deviceKnown || empty($user_email)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            addSystemLog($conn, $id, "Logged in");
            header("Location: ../dashboard.php");
            exit;
        } else {
            $code = rand(100000, 999999);
            $_SESSION['2fa_code'] = $code;
            $_SESSION['temp_user_id'] = $id;
            $_SESSION['temp_username'] = $username;   // send ver code
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'changepassquiz1@gmail.com'; 
                $mail->Password   = 'bwtx mxbe wota jgoe';    
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('changepassquiz1@gmail.com', 'Quiz Security');
                $mail->addAddress($user_email);

                $mail->isHTML(true);
                $mail->Subject = 'Code for access from a new device';
                $mail->Body    = "
                    <h3>Hi, {$username},</h3>
                    <p>We detected an attempt to log in to your account from a new device or browser.</p>
                    <p>To continue, please enter this verification code:</p>
                    <h2 style='background: #ffcc00; color: #000; padding: 10px; display: inline-block; border-radius: 5px; letter-spacing: 5px;'>{$code}</h2>
                    <p>If it wasn't you, please change your password immediately!</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                die("There was a problem sending the email: " . $mail->ErrorInfo);               // email error 
            }
            
            header("Location: ../verify_2fa.php");
            exit;
        }
    }
}

echo "Wrong username or password.";
?>
