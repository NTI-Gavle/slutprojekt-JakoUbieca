<?php
session_start();
include "db.php";
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "unauthorized"]);
    exit;
}

$my_id = intval($_SESSION['user_id']);
$data = json_decode(file_get_contents("php://input"), true);

// Utility: Scrape Link Preview
function getLinkPreview($text) {
    preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $text, $match);
    if (!empty($match[0])) {
        $url = $match[0][0]; // get first URL
        
        // Use a simple context stream to avoid hanging
        $context = stream_context_create(['http' => ['timeout' => 2]]);
        $html = @file_get_contents($url, false, $context);
        
        if ($html) {
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $tags = $doc->getElementsByTagName('meta');
            
            $preview = ['url' => $url, 'title' => $url, 'description' => '', 'image' => ''];
            
            foreach ($tags as $tag) {
                $property = $tag->getAttribute('property');
                $name = $tag->getAttribute('name');
                $content = $tag->getAttribute('content');
                
                if ($property == 'og:title' || $name == 'title') $preview['title'] = $content;
                if ($property == 'og:description' || $name == 'description') $preview['description'] = $content;
                if ($property == 'og:image') $preview['image'] = $content;
            }
            return json_encode($preview);
        }
    }
    return NULL;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $data['action'] ?? '';

    if ($action === 'send') {
        $target_id = intval($data['target_id']);
        $type = $data['type'] ?? 'user';
        $msg = $conn->real_escape_string(trim($data['message']));
        $img_url = isset($data['image_url']) ? $conn->real_escape_string($data['image_url']) : NULL;
        
        $link_preview = getLinkPreview($msg);
        $link_preview_sql = $link_preview ? "'" . $conn->real_escape_string($link_preview) . "'" : "NULL";
        $img_url_sql = $img_url ? "'$img_url'" : "NULL";

        if ($type === 'room') {
            $sql = "INSERT INTO chat_messages (sender_id, receiver_id, room_id, message, image_url, link_preview_json) 
                    VALUES ($my_id, $my_id, $target_id, '$msg', $img_url_sql, $link_preview_sql)";
        } else {
            $sql = "INSERT INTO chat_messages (sender_id, receiver_id, message, image_url, link_preview_json, is_delivered) 
                    VALUES ($my_id, $target_id, '$msg', $img_url_sql, $link_preview_sql, 1)";
        }
        if ($conn->query($sql)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $conn->error, "sql" => $sql]);
        }
    } 
    else if ($action === 'delete') {
        $msg_id = intval($data['msg_id']);
        // 15 min rule: TIME_TO_SEC(TIMEDIFF(NOW(), sent_at)) < 900
        $sql = "DELETE FROM chat_messages WHERE id = $msg_id AND sender_id = $my_id AND TIME_TO_SEC(TIMEDIFF(NOW(), sent_at)) <= 900";
        $conn->query($sql);
        echo json_encode(["success" => $conn->affected_rows > 0]);
    }
    else if ($action === 'edit') {
        $msg_id = intval($data['msg_id']);
        $new_msg = $conn->real_escape_string(trim($data['message']));
        $sql = "UPDATE chat_messages SET message = '$new_msg', is_edited = 1 
                WHERE id = $msg_id AND sender_id = $my_id AND TIME_TO_SEC(TIMEDIFF(NOW(), sent_at)) <= 900";
        $conn->query($sql);
        echo json_encode(["success" => $conn->affected_rows > 0]);
    }
    else if ($action === 'typing') {
        $target_id = intval($data['target_id']);
        $type = $data['type'] ?? 'user';
        $sql = "INSERT INTO chat_typing_status (user_id, target_id, target_type) VALUES ($my_id, $target_id, '$type')
                ON DUPLICATE KEY UPDATE updated_at = NOW()";
        echo json_encode(["success" => $conn->query($sql)]);
    }
    else if ($action === 'update_status') {
        $status = $conn->real_escape_string($data['status']);
        $sql = "UPDATE users SET status = '$status' WHERE id = $my_id";
        echo json_encode(["success" => $conn->query($sql)]);
    }
    else if ($action === 'create_room') {
        $room_name = trim($conn->real_escape_string($data['name'] ?? ''));
        if (empty($room_name)) { echo json_encode(["error" => "Name required"]); exit; }
        
        $conn->query("INSERT INTO chat_rooms (name, created_by, is_private) VALUES ('$room_name', $my_id, 0)");
        $room_id = $conn->insert_id;
        $conn->query("INSERT INTO chat_room_members (room_id, user_id) VALUES ($room_id, $my_id)");
        
        echo json_encode(["success" => true, "id" => $room_id, "name" => $room_name]);
    }
    else if ($action === 'rename_room') {
        $room_id = intval($data['room_id']);
        $new_name = trim($conn->real_escape_string($data['name'] ?? ''));
        
        // Check if user is the creator
        $res = $conn->query("SELECT created_by FROM chat_rooms WHERE id = $room_id");
        $room = $res->fetch_assoc();
        if ($room && $room['created_by'] == $my_id && !empty($new_name)) {
            $conn->query("UPDATE chat_rooms SET name = '$new_name' WHERE id = $room_id");
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["error" => "Unauthorized or empty name"]);
        }
    }
    else if ($action === 'remove_member') {
        $room_id = intval($data['room_id']);
        $target_user_id = intval($data['target_user_id']);
        
        // Check if user is the creator
        $res = $conn->query("SELECT created_by FROM chat_rooms WHERE id = $room_id");
        $room = $res->fetch_assoc();
        if ($room && $room['created_by'] == $my_id && $target_user_id != $my_id) {
            $conn->query("DELETE FROM chat_room_members WHERE room_id = $room_id AND user_id = $target_user_id");
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["error" => "Unauthorized"]);
        }
    }
    else if ($action === 'add_member') {
        $room_id = intval($data['room_id']);
        $target_user_id = intval($data['target_user_id']);
        
        // Check if user is a member or creator of the room
        $res = $conn->query("SELECT * FROM chat_room_members WHERE room_id = $room_id AND user_id = $my_id");
        if ($res->num_rows > 0) {
            // Add user (ignore if already exists)
            $conn->query("INSERT IGNORE INTO chat_room_members (room_id, user_id) VALUES ($room_id, $target_user_id)");
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["error" => "Unauthorized"]);
        }
    }
    else if ($action === 'delete_room') {
        $room_id = intval($data['room_id']);
        // Check if user is the creator
        $res = $conn->query("SELECT created_by FROM chat_rooms WHERE id = $room_id");
        $room = $res->fetch_assoc();
        if ($room && $room['created_by'] == $my_id) {
            $conn->query("DELETE FROM chat_messages WHERE room_id = $room_id");
            $conn->query("DELETE FROM chat_room_members WHERE room_id = $room_id");
            $conn->query("DELETE FROM chat_rooms WHERE id = $room_id");
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["error" => "Unauthorized"]);
        }
    }
    exit;
} 

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'get';

    if ($action === 'get_rooms') {
        $sql = "SELECT DISTINCT r.* FROM chat_rooms r 
                LEFT JOIN chat_room_members m ON r.id = m.room_id 
                WHERE r.is_private = 0 OR m.user_id = $my_id";
        $res = $conn->query($sql);
        $rooms = [];
        if($res) { while($row = $res->fetch_assoc()) $rooms[] = $row; }
        echo json_encode($rooms);
        exit;
    }
    
    if ($action === 'get_room_details') {
        $room_id = intval($_GET['room_id']);
        // Verify user is in room or room is public
        $res = $conn->query("SELECT r.*, m.user_id as is_member FROM chat_rooms r LEFT JOIN chat_room_members m ON r.id = m.room_id AND m.user_id = $my_id WHERE r.id = $room_id");
        $room = $res->fetch_assoc();
        
        if ($room && ($room['is_private'] == 0 || $room['is_member'])) {
            $members_res = $conn->query("SELECT u.id, u.username FROM chat_room_members m JOIN users u ON m.user_id = u.id WHERE m.room_id = $room_id");
            $members = [];
            while($m = $members_res->fetch_assoc()) $members[] = $m;
            
            echo json_encode(["success" => true, "room" => $room, "members" => $members]);
        } else {
            echo json_encode(["error" => "Unauthorized"]);
        }
        exit;
    }

    if ($action === 'get') {
        $target_id = intval($_GET['target_id']);
        $type = $_GET['type'] ?? 'user';

        // Update read status if it's a direct message
        if ($type === 'user') {
            $conn->query("UPDATE chat_messages SET is_read = 1 WHERE receiver_id = $my_id AND sender_id = $target_id AND is_read = 0");
        }

        // Fetch messages
        if ($type === 'room') {
            $sql = "SELECT id, sender_id, room_id, message, image_url, link_preview_json, is_edited, sent_at 
                    FROM chat_messages WHERE room_id = $target_id ORDER BY sent_at ASC";
        } else {
            $sql = "SELECT id, sender_id, receiver_id, message, image_url, link_preview_json, is_read, is_delivered, is_edited, sent_at 
                    FROM chat_messages WHERE (sender_id = $my_id AND receiver_id = $target_id) 
                    OR (sender_id = $target_id AND receiver_id = $my_id) ORDER BY sent_at ASC";
        }
        
        $result = $conn->query($sql);
        $msgs = [];
        if($result) {
            while($row = $result->fetch_assoc()) {
                $msgs[] = $row;
            }
        }

        // Check if target is typing (updated in last 5 seconds)
        $is_typing = false;
        if ($type === 'user') {
            $res = $conn->query("SELECT 1 FROM chat_typing_status WHERE user_id = $target_id AND target_id = $my_id AND target_type = 'user' AND TIME_TO_SEC(TIMEDIFF(NOW(), updated_at)) < 5");
            $is_typing = ($res && $res->num_rows > 0);
        } else {
            $res = $conn->query("SELECT 1 FROM chat_typing_status WHERE target_id = $target_id AND target_type = 'room' AND user_id != $my_id AND TIME_TO_SEC(TIMEDIFF(NOW(), updated_at)) < 5 LIMIT 1");
            $is_typing = ($res && $res->num_rows > 0);
        }
        
        include_once "sanitize.php";
        sanitize_array($msgs);
        
        echo json_encode(["messages" => $msgs, "is_typing" => $is_typing]);
        exit;
    }
}
?>