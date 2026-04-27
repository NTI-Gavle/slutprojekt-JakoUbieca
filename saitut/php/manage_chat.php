<?php
session_start();
include "db.php";
header("Content-Type: application/json");

$my_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $friend_id = intval($data['friend_id']);
    $msg = $conn->real_escape_string($data['message']);

    $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES ($my_id, $friend_id, '$msg')";
    echo json_encode(["success" => $conn->query($sql)]);
} 

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $friend_id = intval($_GET['friend_id']);
    $sql = "SELECT * FROM chat_messages 
            WHERE (sender_id = $my_id AND receiver_id = $friend_id) 
            OR (sender_id = $friend_id AND receiver_id = $my_id) 
            ORDER BY sent_at ASC";
    
    $result = $conn->query($sql);
    $msgs = [];
    while($row = $result->fetch_assoc()) {
        $row['is_me'] = ($row['sender_id'] == $my_id);
        $msgs[] = $row;
    }
    echo json_encode($msgs);
}
?>