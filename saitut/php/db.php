<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "quiz_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error connecting to database!");
}
?>
