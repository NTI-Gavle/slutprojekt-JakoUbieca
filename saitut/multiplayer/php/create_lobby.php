<?php
session_start();
include "../../php/db.php"; 

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "You are not logged in!"]);
    exit;
}

$host_id = $_SESSION['user_id'];
$lobby_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6)); 

$stmt = $conn->prepare("INSERT INTO game_sessions (host_id, lobby_code, status) VALUES (?, ?, 'waiting')");
$stmt->bind_param("is", $host_id, $lobby_code);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "lobby_code" => $lobby_code,
        "session_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Error in database"]);
}
?>