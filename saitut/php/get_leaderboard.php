<?php
error_reporting(0); // 
ini_set('display_errors', 0);

include "db.php";

$query = "
    SELECT username, points 
    FROM users 
    ORDER BY points DESC 
    LIMIT 5
";

$result = $conn->query($query);

$leaders = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaders[] = $row;
    }
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($leaders, JSON_UNESCAPED_UNICODE);
exit;
