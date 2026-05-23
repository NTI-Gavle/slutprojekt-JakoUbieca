<?php
include "db.php";

$result = $conn->query("SELECT id, title FROM quizzes");

$quizzes = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
}

header("Content-Type: application/json");
include_once "sanitize.php";
sanitize_array($quizzes);
echo json_encode($quizzes);
