<?php
session_start();
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();

        $pdo->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?")->execute([$user_id, $user_id]);
        $pdo->prepare("DELETE FROM friends WHERE user_id = ? OR friend_id = ?")->execute([$user_id, $user_id]);
        $pdo->prepare("DELETE FROM friend_requests WHERE sender_id = ? OR receiver_id = ?")->execute([$user_id, $user_id]);
        $pdo->prepare("DELETE FROM forum_posts WHERE author_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM forum_threads WHERE author_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM forum_votes WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM forum_reports WHERE reporter_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM forum_notifications WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM user_achievements WHERE user_id = ?")->execute([$user_id]);
        
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

        $pdo->commit();

        session_destroy();
        echo "success";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "error";
    }
} else {
    echo "invalid_request";
}
