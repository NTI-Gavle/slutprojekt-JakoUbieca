<?php
$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "quiz_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error connecting to database!");
}

include_once __DIR__ . "/schema_check.php";
?>
