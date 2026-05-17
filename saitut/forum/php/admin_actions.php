<?php
session_start();
include "../../php/db.php";

header('Content-Type: application/json');                                           // overall comment, admin decisions, approve or reject catg, resolve repps, manage catg

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$adm_stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$adm_stmt->bind_param("i", $user_id);
$adm_stmt->execute();
$adm_stmt->bind_result($is_admin);
$adm_stmt->fetch();
$adm_stmt->close();

if ($is_admin != 1) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$id = intval($data['id'] ?? 0);

switch ($action) {
    case 'approve_category':
        $req_stmt = $conn->prepare("SELECT name, description, icon, user_id FROM forum_category_requests WHERE id = ? AND status = 'pending'");
        $req_stmt->bind_param("i", $id);
        $req_stmt->execute();
        $req = $req_stmt->get_result()->fetch_assoc();
        $req_stmt->close();

        if (!$req) {
            echo json_encode(['success' => false, 'message' => 'Request not found or already reviewed']);
            break;
        }

        $max_order = $conn->query("SELECT MAX(sort_order) AS m FROM forum_categories")->fetch_assoc()['m'];
        $new_order = ($max_order ?? 0) + 1;
        $cat_stmt = $conn->prepare("INSERT INTO forum_categories (name, description, icon, sort_order, created_by) VALUES (?, ?, ?, ?, ?)");
        $cat_stmt->bind_param("sssii", $req['name'], $req['description'], $req['icon'], $new_order, $req['user_id']);
        $cat_stmt->execute();
        $cat_stmt->close();

        $admin_note = trim($data['admin_note'] ?? '');
        $upd = $conn->prepare("UPDATE forum_category_requests SET status = 'approved', reviewed_by = ?, admin_note = ?, reviewed_at = NOW() WHERE id = ?");
        $upd->bind_param("isi", $user_id, $admin_note, $id);
        $upd->execute();
        $upd->close();

        $msg = "Your category request '" . $req['name'] . "' has been approved!";
        $n_stmt = $conn->prepare("INSERT INTO forum_notifications (user_id, type, from_user_id, message) VALUES (?, 'category_approved', ?, ?)");
        $n_stmt->bind_param("iis", $req['user_id'], $user_id, $msg);
        $n_stmt->execute();
        $n_stmt->close();

        echo json_encode(['success' => true, 'message' => 'Category approved and created!']);
        break;

    case 'reject_category':
        $admin_note = trim($data['admin_note'] ?? '');

        $req_stmt = $conn->prepare("SELECT user_id, name FROM forum_category_requests WHERE id = ? AND status = 'pending'");
        $req_stmt->bind_param("i", $id);
        $req_stmt->execute();
        $req = $req_stmt->get_result()->fetch_assoc();
        $req_stmt->close();

        if (!$req) {
            echo json_encode(['success' => false, 'message' => 'Request not found']);
            break;
        }

        $upd = $conn->prepare("UPDATE forum_category_requests SET status = 'rejected', reviewed_by = ?, admin_note = ?, reviewed_at = NOW() WHERE id = ?");
        $upd->bind_param("isi", $user_id, $admin_note, $id);
        $upd->execute();
        $upd->close();

        $msg = "Your category request '" . $req['name'] . "' was rejected.";
        if ($admin_note) $msg .= " Note: " . $admin_note;
        $n_stmt = $conn->prepare("INSERT INTO forum_notifications (user_id, type, from_user_id, message) VALUES (?, 'category_rejected', ?, ?)");
        $n_stmt->bind_param("iis", $req['user_id'], $user_id, $msg);
        $n_stmt->execute();
        $n_stmt->close();

        echo json_encode(['success' => true, 'message' => 'Category request rejected']);
        break;

    case 'create_category':
        $name = trim($data['name'] ?? '');
        $desc = trim($data['description'] ?? '');
        $icon = trim($data['icon'] ?? '📁');
        $sort = intval($data['sort_order'] ?? 0);

        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            break;
        }

        $stmt = $conn->prepare("INSERT INTO forum_categories (name, description, icon, sort_order, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $name, $desc, $icon, $sort, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Category created']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create']);
        }
        $stmt->close();
        break;

    case 'edit_category':
        $name = trim($data['name'] ?? '');
        $icon = trim($data['icon'] ?? '📁');
        $sort = intval($data['sort_order'] ?? 0);

        $stmt = $conn->prepare("UPDATE forum_categories SET name = ?, icon = ?, sort_order = ? WHERE id = ?");
        $stmt->bind_param("ssii", $name, $icon, $sort, $id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'message' => 'Category updated']);
        break;

    case 'delete_category':
        $conn->query("DELETE FROM forum_categories WHERE id = $id");
        echo json_encode(['success' => true, 'message' => 'Category deleted']);
        break;

    case 'resolve_report':
        $conn->query("UPDATE forum_reports SET status = 'resolved' WHERE id = $id");
        echo json_encode(['success' => true, 'message' => 'Report resolved']);
        break;

    case 'delete_reported_post':
        $post_id = intval($data['post_id'] ?? 0);
        if ($post_id > 0) {
            $conn->query("DELETE FROM forum_posts WHERE id = $post_id");
        }
        $conn->query("UPDATE forum_reports SET status = 'resolved' WHERE id = $id");
        echo json_encode(['success' => true, 'message' => 'Post deleted and report resolved']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
?>
