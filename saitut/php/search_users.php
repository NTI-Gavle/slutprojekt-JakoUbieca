<?php
session_start();
include "db.php";

$q = $_GET['q'] ?? '';
$my_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT id, username FROM users WHERE username LIKE ? AND id != ? LIMIT 10");
$search = "%$q%";
$stmt->bind_param("si", $search, $my_id);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);