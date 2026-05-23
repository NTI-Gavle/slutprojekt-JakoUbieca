<?php
session_start();
include "db.php";
include "logger.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Error processing the request'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
  
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->close();
        $response['success'] = true;
    }

    if (isset($_POST['profile_url'])) {
        $new_url = $conn->real_escape_string($_POST['profile_url']);
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $new_url, $user_id);
        $stmt->execute();
        $stmt->close();
        $response['success'] = true;
    }

    if (isset($_POST['cover_url'])) {
        $cover_url = $conn->real_escape_string($_POST['cover_url']);
        $stmt = $conn->prepare("UPDATE users SET cover_url = ? WHERE id = ?");
        $stmt->bind_param("si", $cover_url, $user_id);
        $stmt->execute();
        $stmt->close();
        $response['success'] = true;
    }

    if (isset($_POST['bio'])) {
        // Enforce 100 words max on backend
        $bio = trim($_POST['bio']);
        $words = preg_split('/\s+/', $bio, -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > 100) {
            $bio = implode(" ", array_slice($words, 0, 100));
        }
        $bio_safe = $conn->real_escape_string($bio);
        
        $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
        $stmt->bind_param("si", $bio_safe, $user_id);
        $stmt->execute();
        $stmt->close();
        $response['success'] = true;
    }

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = time() . '_p_' . basename($_FILES['profile_pic']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
        if (in_array(strtolower($fileType), $allowTypes)) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFilePath)) {
                $dbPath = 'uploads/' . $fileName; 
                $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->bind_param("si", $dbPath, $user_id);
                $stmt->execute();
                $stmt->close();
                $response['success'] = true;
            }
        }
    }

    if (isset($_FILES['cover_pic']) && $_FILES['cover_pic']['error'] === 0) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = time() . '_c_' . basename($_FILES['cover_pic']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
        if (in_array(strtolower($fileType), $allowTypes)) {
            if (move_uploaded_file($_FILES['cover_pic']['tmp_name'], $targetFilePath)) {
                $dbPath = 'uploads/' . $fileName; 
                $stmt = $conn->prepare("UPDATE users SET cover_url = ? WHERE id = ?");
                $stmt->bind_param("si", $dbPath, $user_id);
                $stmt->execute();
                $stmt->close();
                $response['success'] = true;
            }
        }
    }

    if ($response['success']) {
        addSystemLog($conn, $user_id, "Updated their profile");
    }
    
    echo json_encode($response);
    exit;
}
?>