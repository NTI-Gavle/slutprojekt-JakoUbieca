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

$post_id = intval($data['post_id'] ?? 0);
$vote = intval($data['vote'] ?? 0);

if ($post_id === 0 || !in_array($vote, [1, -1])) {
    echo json_encode(['success' => false, 'message' => 'Invalid vote']);
    exit;
}

$check = $conn->prepare("SELECT id, vote FROM forum_votes WHERE user_id = ? AND post_id = ?");
$check->bind_param("ii", $user_id, $post_id);
$check->execute();
$result = $check->get_result();
$existing = $result->fetch_assoc();
$check->close();

$current_vote = 0;                                                         // vpte change or remove new post vote

if ($existing) {
    if ($existing['vote'] == $vote) {
        $del = $conn->prepare("DELETE FROM forum_votes WHERE id = ?");
        $del->bind_param("i", $existing['id']);
        $del->execute();
        $del->close();
        $current_vote = 0;
    } else {
        $upd = $conn->prepare("UPDATE forum_votes SET vote = ? WHERE id = ?");
        $upd->bind_param("ii", $vote, $existing['id']);
        $upd->execute();
        $upd->close();
        $current_vote = $vote;
    }
} else {
    $ins = $conn->prepare("INSERT INTO forum_votes (user_id, post_id, vote) VALUES (?, ?, ?)");
    $ins->bind_param("iii", $user_id, $post_id, $vote);
    $ins->execute();
    $ins->close();
    $current_vote = $vote;
}

$score_stmt = $conn->prepare("SELECT COALESCE(SUM(vote), 0) AS total FROM forum_votes WHERE post_id = ?");
$score_stmt->bind_param("i", $post_id);
$score_stmt->execute();
$score_stmt->bind_result($new_score);
$score_stmt->fetch();
$score_stmt->close();

echo json_encode(['success' => true, 'new_score' => $new_score, 'current_vote' => $current_vote]);
?>
