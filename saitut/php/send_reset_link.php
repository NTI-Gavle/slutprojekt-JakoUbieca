<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    

    $check = $conn->prepare("SELECT username FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();

        $reset_link = "http://localhost/saitut/reset_password.php?token=" . $token;

       
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

            $mail->setFrom('changepassquiz1@gmail.com', 'Quiz Site Support');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Recover Your Password';
            $mail->Body    = "
                <h3>Hello,</h3>
                <p>We received a request to reset your password.</p>
                <p>Click the button below to set a new password (valid for 1 hour):</p>
                <a href='$reset_link' style='background:#ffcc00; color:black; padding:10px 20px; text-decoration:none; border-radius:5px; font-weight:bold;'>Change Password</a>
                <p>If you did not request this change, please ignore this email.</p>
            ";

            $mail->send();
            echo "The reset link has been sent to your email!";
        } catch (Exception $e) {
            echo "Email could not be sent. Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "This email is not found in the system.";
    }
}
?>