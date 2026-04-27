<?php
session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "There is no active session."]);
    exit;
}

$my_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];

    
    if ($action === 'get_requests') {
        $stmt = $conn->prepare("
            SELECT f.id AS request_id, u.username 
            FROM friendships f 
            JOIN users u ON f.user_id = u.id 
            WHERE f.friend_id = ? AND f.status = 'pending'
        ");
        $stmt->bind_param("i", $my_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        echo json_encode($requests);
        exit;
    }

  
    if ($action === 'get_friends') {
        $stmt = $conn->prepare("
            SELECT f.id as friendship_id, u.id as user_id, u.username, u.profile_pic 
            FROM friendships f 
            JOIN users u ON (f.user_id = u.id OR f.friend_id = u.id) 
            WHERE (f.user_id = ? OR f.friend_id = ?) 
            AND f.status = 'accepted' 
            AND u.id != ?
        ");
        $stmt->bind_param("iii", $my_id, $my_id, $my_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $friends = [];
        while ($row = $result->fetch_assoc()) {
            $friends[] = $row;
        }
        echo json_encode($friends);
        exit;
    }
}


$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';


if ($action === 'send_request' || $action === 'add') {
    $friend_id = intval($data['target_id'] ?? $data['friend_id'] ?? 0);
    
    if ($friend_id === 0 || $friend_id === $my_id) {
        echo json_encode(["success" => false, "message" => "invalid user."]);
        exit;
    }

    $check = $conn->prepare("SELECT id FROM friendships WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $check->bind_param("iiii", $my_id, $friend_id, $friend_id, $my_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "There is already a request or you are friends."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $my_id, $friend_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Invitation sent."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error in database."]);
    }
} 


elseif ($action === 'respond_request' || $action === 'respond') {
    $request_id = intval($data['request_id'] ?? 0);
    $status = $data['status'] ?? ''; 

    if ($status === 'accepted' || $status === 'accept') {
        $stmt = $conn->prepare("UPDATE friendships SET status = 'accepted' WHERE id = ? AND friend_id = ?");
        $stmt->bind_param("ii", $request_id, $my_id);
    } else {
     
        $stmt = $conn->prepare("DELETE FROM friendships WHERE id = ? AND friend_id = ?");
        $stmt->bind_param("ii", $request_id, $my_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error updating database."]);
    }
}


elseif ($action === 'unfriend') {
    $friendship_id = intval($data['friendship_id'] ?? 0);
    if ($friendship_id === 0) exit;

    $stmt = $conn->prepare("DELETE FROM friendships WHERE id = ? AND (user_id = ? OR friend_id = ?)");
    $stmt->bind_param("iii", $friendship_id, $my_id, $my_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Friendship ended."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error removing friendship."]);
    }
}