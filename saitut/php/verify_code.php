<?php
session_start();
include "db.php";
include "logger.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    
    if (isset($_SESSION['2fa_code']) && $code == $_SESSION['2fa_code']) {
        $id = $_SESSION['temp_user_id'];
        $username = $_SESSION['temp_username'];                            // save token in db
        $newToken = bin2hex(random_bytes(32));
        $insertDevice = $conn->prepare("INSERT INTO user_devices (user_id, device_token) VALUES (?, ?)");
        $insertDevice->bind_param("is", $id, $newToken);
        $insertDevice->execute();

        setcookie('device_token', $newToken, time() + (86400 * 365), "/");   // one year cookie

        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;

        unset($_SESSION['2fa_code']);
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_username']);
        addSystemLog($conn, $id, "Logged in from a new verified device"); 
        header("Location: ../hub.php");
        exit;
    } else {
        echo "<script>alert('Invalid verification code!'); window.location.href='../verify_2fa.php';</script>";
    }
}
?>
