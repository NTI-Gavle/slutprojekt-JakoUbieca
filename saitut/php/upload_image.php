<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload error code: ' . $file['error']]);
        exit;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.']);
        exit;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
        echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
        exit;
    }
    
    // Determine upload folder based on type
    $type = isset($_POST['type']) ? $_POST['type'] : 'general';
    $uploadDir = '../uploads/' . preg_replace('/[^a-zA-Z0-9]/', '', $type) . '/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Return relative path to be stored in DB
        $dbPath = 'uploads/' . preg_replace('/[^a-zA-Z0-9]/', '', $type) . '/' . $filename;
        echo json_encode(['success' => true, 'url' => $dbPath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No image uploaded']);
}
?>
