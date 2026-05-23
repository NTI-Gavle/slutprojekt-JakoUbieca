 <?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$data = [];

$stmt = $pdo->prepare("SELECT id, username, email, display_pic, xp, level, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$data['user_profile'] = $stmt->fetch(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("SELECT u.username, u.email FROM friends f JOIN users u ON f.friend_id = u.id WHERE f.user_id = ?");
$stmt->execute([$user_id]);
$data['friends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT receiver_id, message, created_at FROM messages WHERE sender_id = ?");
$stmt->execute([$user_id]);
$data['messages_sent'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT title, body, created_at FROM forum_threads WHERE author_id = ?");
$stmt->execute([$user_id]);
$data['forum_threads'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT body, created_at FROM forum_posts WHERE author_id = ?");
$stmt->execute([$user_id]);
$data['forum_posts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="my_data_' . date('Y-m-d') . '.json"');
echo json_encode($data, JSON_PRETTY_PRINT);
exit;
