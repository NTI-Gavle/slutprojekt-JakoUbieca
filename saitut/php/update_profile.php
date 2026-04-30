<?php
session_start();
include "db.php";

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

    
    if (isset($_POST['profile_url']) && !empty($_POST['profile_url'])) {
        $new_url = $_POST['profile_url'];
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $new_url, $user_id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['new_pic'] = $new_url;
        }
        $stmt->close();
    }

    
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $uploadDir = '../uploads/';
        
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['profile_pic']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

      
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        if (in_array(strtolower($fileType), $allowTypes)) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFilePath)) {
                $dbPath = 'uploads/' . $fileName; 
                $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->bind_param("si", $dbPath, $user_id);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['new_pic'] = $dbPath;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Error uploading the file.';
            }
        } else {
            $response['message'] = 'Only JPG, JPEG, PNG & GIF files are allowed.';
        }
    }

    
    echo json_encode($response);
    exit;
}
?>