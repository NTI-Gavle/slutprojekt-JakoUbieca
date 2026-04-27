<?php
session_start();
include "db.php";

$username = $_POST['username'];
$password = $_POST['password'];


$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $hashedPassword);
    $stmt->fetch();

   
    if (password_verify($password, $hashedPassword)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;

        header("Location: ../dashboard.php");
        exit;
    }
}

echo "Wrong username or password.";
?>
