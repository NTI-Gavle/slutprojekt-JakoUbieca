<?php
session_start();
include "../../php/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$search = '%' . $query . '%';                                   // advc search 
$sql = "SELECT DISTINCT ft.id AS thread_id, ft.title, u.username, fc.name AS category_name
    FROM forum_threads ft
    JOIN users u ON ft.user_id = u.id
    JOIN forum_categories fc ON ft.category_id = fc.id
    LEFT JOIN forum_posts fp ON fp.thread_id = ft.id
    WHERE ft.title LIKE ? OR ft.body LIKE ? OR fp.body LIKE ?
    ORDER BY ft.created_at DESC
    LIMIT 20";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $search, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

$results = [];
while ($row = $result->fetch_assoc()) {
    $results[] = $row;
}
$stmt->close();

include_once "../../php/sanitize.php";
sanitize_array($results);

echo json_encode($results);
?>
