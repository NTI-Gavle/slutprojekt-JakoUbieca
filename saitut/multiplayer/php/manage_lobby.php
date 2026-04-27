<?php
session_start();
include "../../php/db.php";
header("Content-Type: application/json");

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';


if ($action === 'get_players') {
    $session_id = intval($_GET['session_id']);
    $stmt = $conn->prepare("
        SELECT lp.user_id, u.username, lp.status 
        FROM lobby_players lp 
        JOIN users u ON lp.user_id = u.id 
        WHERE lp.session_id = ?
    ");
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $players = [];
    while($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
    echo json_encode($players);
}


if ($action === 'join_lobby') {
    $code = $_GET['code'];
    
    $stmt = $conn->prepare("SELECT id FROM game_sessions WHERE lobby_code = ? AND status = 'waiting'");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Invalid code or the game has already started"]);
        exit;
    }
    
    $session = $res->fetch_assoc();
    $session_id = $session['id'];
    
    $stmt = $conn->prepare("INSERT INTO lobby_players (session_id, user_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $session_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "session_id" => $session_id]);
    } else {
        echo json_encode(["success" => false, "message" => "You have already sent a request"]);
    }
}

if ($action === 'approve_player') {
    $session_id = intval($_GET['session_id']);
    $player_id = intval($_GET['player_id']);
    $check = $conn->prepare("SELECT id FROM game_sessions WHERE id = ? AND host_id = ?");
    $check->bind_param("ii", $session_id, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $update = $conn->prepare("UPDATE lobby_players SET status = 'approved' WHERE session_id = ? AND user_id = ?");
        $update->bind_param("ii", $session_id, $player_id);
        $update->execute();
        echo json_encode(["success" => true]);
    }
}

if ($action === 'check_my_status') {
    $session_id = intval($_GET['session_id']);
    $stmt = $conn->prepare("
        SELECT lp.status as player_status, gs.status as game_status, gs.quiz_id 
        FROM lobby_players lp
        JOIN game_sessions gs ON lp.session_id = gs.id
        WHERE lp.session_id = ? AND lp.user_id = ?
    ");
    $stmt->bind_param("ii", $session_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        "approved" => ($res['player_status'] === 'approved'),
        "game_started" => ($res['game_status'] === 'playing'),
        "quiz_id" => $res['quiz_id']
    ]);
    exit;
}

if ($action === 'start_game') {                          //game start
    $session_id = intval($_GET['session_id']);
    $quiz_id = intval($_GET['quiz_id']);
    $check = $conn->prepare("SELECT id FROM game_sessions WHERE id = ? AND host_id = ?");
    $check->bind_param("ii", $session_id, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $update = $conn->prepare("UPDATE game_sessions SET status = 'playing', quiz_id = ? WHERE id = ?");
        $update->bind_param("ii", $quiz_id, $session_id);
        
        if ($update->execute()) {
            echo json_encode(["success" => true]);
        }
    }
    exit;
}
?>