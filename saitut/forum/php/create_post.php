<?php
session_start();
include "../../php/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$thread_id = intval($data['thread_id'] ?? 0);
$body = trim($data['body'] ?? '');

if ($thread_id === 0 || $body === '') {
    echo json_encode(['success' => false, 'message' => 'Thread ID and body are required']);
    exit;
}

$t_stmt = $conn->prepare("SELECT user_id, is_locked, title FROM forum_threads WHERE id = ?");
$t_stmt->bind_param("i", $thread_id);
$t_stmt->execute();
$t_res = $t_stmt->get_result();
$thread = $t_res->fetch_assoc();
$t_stmt->close();

if (!$thread) {
    echo json_encode(['success' => false, 'message' => 'Thread not found']);
    exit;
}

if ($thread['is_locked']) {
    echo json_encode(['success' => false, 'message' => 'This thread is locked']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO forum_posts (thread_id, user_id, body) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $thread_id, $user_id, $body);

if ($stmt->execute()) {                                                                                // notf to author 
    if ($thread['user_id'] != $user_id) {
        $msg = "Someone replied to your thread: " . $thread['title'];
        $n_stmt = $conn->prepare("INSERT INTO forum_notifications (user_id, type, thread_id, from_user_id, message) VALUES (?, 'reply', ?, ?, ?)");
        $n_stmt->bind_param("iiis", $thread['user_id'], $thread_id, $user_id, $msg);
        $n_stmt->execute();
        $n_stmt->close();
    }

    preg_match_all('/@(\w+)/', $body, $matches);                                  //tags
    if (!empty($matches[1])) {
        $mentioned = array_unique($matches[1]);
        foreach ($mentioned as $uname) {
            $u_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $u_stmt->bind_param("s", $uname);
            $u_stmt->execute();
            $u_stmt->bind_result($mentioned_id);
            if ($u_stmt->fetch() && $mentioned_id != $user_id) {
                $u_stmt->close();
                $m_msg = "You were mentioned in: " . $thread['title'];
                $mn_stmt = $conn->prepare("INSERT INTO forum_notifications (user_id, type, thread_id, from_user_id, message) VALUES (?, 'mention', ?, ?, ?)");
                $mn_stmt->bind_param("iiis", $mentioned_id, $thread_id, $user_id, $m_msg);
                $mn_stmt->execute();
                $mn_stmt->close();
            } else {
                $u_stmt->close();
            }
        }
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create post']);
}
$stmt->close();
?>
