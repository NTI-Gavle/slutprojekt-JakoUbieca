<?php
include "db.php";

$username = $_POST['username'];
$email = $_POST['email']; 
$password = $_POST['password'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);


$check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$check->bind_param("ss", $username, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "The username or email already exists!";
    exit;
}


$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if ($stmt->execute()) {
    header("Location: ../login.php");
    exit;
} else {
    echo "Error during registration";
}
?>