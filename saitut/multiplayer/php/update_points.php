<?php
session_start();
include "../../php/db.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['session_id'])) {
    exit(json_encode(["success" => false]));
}

$user_id = $_SESSION['user_id'];
$session_id = intval($_POST['session_id']);
$points = intval($_POST['points']);
$stmt = $conn->prepare("UPDATE lobby_players SET points = ? WHERE session_id = ? AND user_id = ?");
$stmt->bind_param("iii", $points, $session_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
?>