<?php
include 'php/db.php';

$res = $conn->query("SELECT * FROM chat_messages");
if (!$res) {
    echo "Error: " . $conn->error;
} else {
    echo "Found " . $res->num_rows . " messages.";
    print_r($res->fetch_all(MYSQLI_ASSOC));
}
?>
