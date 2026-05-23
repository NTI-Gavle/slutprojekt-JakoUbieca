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

$action = $data['action'] ?? '';
$target_id = intval($data['target_id'] ?? 0);
$adm_stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$adm_stmt->bind_param("i", $user_id);
$adm_stmt->execute();
$adm_stmt->bind_result($is_admin);
$adm_stmt->fetch();
$adm_stmt->close();
$admin_only = ['pin', 'unpin', 'lock', 'unlock', 'delete_thread', 'edit_post', 'move_thread'];

if (in_array($action, $admin_only) && $is_admin != 1) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

switch ($action) {
    case 'pin':
        $conn->query("UPDATE forum_threads SET is_pinned = 1 WHERE id = $target_id");
        echo json_encode(['success' => true, 'message' => 'Thread pinned']);
        break;

    case 'unpin':
        $conn->query("UPDATE forum_threads SET is_pinned = 0 WHERE id = $target_id");
        echo json_encode(['success' => true, 'message' => 'Thread unpinned']);
        break;

    case 'lock':
        $lock_stmt = $conn->prepare("SELECT is_locked FROM forum_threads WHERE id = ?");
        $lock_stmt->bind_param("i", $target_id);
        $lock_stmt->execute();
        $lock_stmt->bind_result($is_locked);
        $lock_stmt->fetch();
        $lock_stmt->close();
        $new_lock = $is_locked ? 0 : 1;
        $conn->query("UPDATE forum_threads SET is_locked = $new_lock WHERE id = $target_id");
        echo json_encode(['success' => true, 'message' => $new_lock ? 'Thread locked' : 'Thread unlocked']);
        break;

    case 'delete_thread':
        $conn->query("DELETE FROM forum_threads WHERE id = $target_id");
        echo json_encode(['success' => true, 'message' => 'Thread deleted']);
        break;

    case 'delete_post':
        $p_stmt = $conn->prepare("SELECT user_id FROM forum_posts WHERE id = ?");
        $p_stmt->bind_param("i", $target_id);
        $p_stmt->execute();
        $p_stmt->bind_result($post_owner);
        $p_stmt->fetch();
        $p_stmt->close();

        if ($is_admin == 1 || $post_owner == $user_id) {
            $conn->query("DELETE FROM forum_posts WHERE id = $target_id");
            echo json_encode(['success' => true, 'message' => 'Post deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'You cannot delete this post']);
        }
        break;

    case 'edit_post':
        $new_body = trim($data['new_body'] ?? '');
        if ($new_body === '') {
            echo json_encode(['success' => false, 'message' => 'Post body cannot be empty']);
            break;
        }
        $e_stmt = $conn->prepare("UPDATE forum_posts SET body = ?, edited_at = NOW() WHERE id = ?");
        $e_stmt->bind_param("si", $new_body, $target_id);
        $e_stmt->execute();
        $e_stmt->close();
        echo json_encode(['success' => true, 'message' => 'Post edited']);
        break;

    case 'mark_correct':
        $mc_stmt = $conn->prepare("SELECT ft.user_id FROM forum_posts fp JOIN forum_threads ft ON fp.thread_id = ft.id WHERE fp.id = ?");
        $mc_stmt->bind_param("i", $target_id);
        $mc_stmt->execute();
        $mc_stmt->bind_result($thread_owner);
        $mc_stmt->fetch();
        $mc_stmt->close();

        if ($is_admin == 1 || $thread_owner == $user_id) {
            $conn->query("UPDATE forum_posts SET is_correct = 1 WHERE id = $target_id");
            echo json_encode(['success' => true, 'message' => 'Marked as correct answer']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Only thread author or admin can do this']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
?>
