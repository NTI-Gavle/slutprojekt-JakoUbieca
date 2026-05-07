<?php
function addSystemLog($conn, $user_id, $action) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $username = "Unknown";
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $username = $row['username'];
        }
        $stmt->close();
    } else {
        $username = "Unknown";
    }

    try {
        $ins = $conn->prepare("INSERT INTO system_logs (username, action) VALUES (?, ?)");
        if ($ins) {
            $ins->bind_param("ss", $username, $action);
            $ins->execute();
            $ins->close();
        }
    } catch (Exception $e) 
    {
    }
}
