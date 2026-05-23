<?php
include 'php/db.php';

$sql = "INSERT INTO chat_messages (sender_id, receiver_id, room_id, message, image_url, link_preview_json) VALUES (1, 0, 1, 'test', NULL, NULL)";
if ($conn->query($sql)) {
    echo "SUCCESS";
} else {
    echo "ERROR: " . $conn->error;
}
?>
