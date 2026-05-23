<?php
// Prevent direct access
if (!isset($conn)) exit;

$schema_queries = [
    // Forum Tables
    "CREATE TABLE IF NOT EXISTS forum_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        icon VARCHAR(50),
        sort_order INT DEFAULT 0,
        created_by INT NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS forum_threads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        body TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        views INT DEFAULT 0,
        is_pinned TINYINT(1) DEFAULT 0,
        is_locked TINYINT(1) DEFAULT 0
    )",
    "CREATE TABLE IF NOT EXISTS forum_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        thread_id INT NOT NULL,
        user_id INT NOT NULL,
        body TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        edited_at DATETIME NULL,
        is_correct TINYINT(1) DEFAULT 0
    )",
    "CREATE TABLE IF NOT EXISTS forum_category_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        icon VARCHAR(50) NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        reviewed_by INT NULL,
        admin_note TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        reviewed_at DATETIME NULL
    )",
    "CREATE TABLE IF NOT EXISTS forum_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type VARCHAR(50) NOT NULL,
        from_user_id INT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        link VARCHAR(255) NULL
    )",
    "CREATE TABLE IF NOT EXISTS forum_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        reason TEXT NOT NULL,
        status ENUM('pending', 'resolved') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS forum_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        vote_type ENUM('up', 'down') NOT NULL,
        UNIQUE KEY unique_vote (post_id, user_id)
    )",
    // New: Forum Subscriptions
    "CREATE TABLE IF NOT EXISTS forum_subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_sub (category_id, user_id)
    )",
    // Chat tables
    "CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        image_url VARCHAR(255) NULL,
        is_edited TINYINT(1) DEFAULT 0,
        sent_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($schema_queries as $sql) {
    $conn->query($sql);
}

// Helper function for safe ALTER
function safeAlter($conn, $query) {
    try {
        $conn->query($query);
    } catch (Exception $e) {
        // Ignore errors like Duplicate column name
    }
}

// Alter users for profile bio, cover, and status
safeAlter($conn, "ALTER TABLE users ADD COLUMN bio VARCHAR(1000) NULL");
safeAlter($conn, "ALTER TABLE users ADD COLUMN cover_url VARCHAR(255) NULL");
safeAlter($conn, "ALTER TABLE users ADD COLUMN status ENUM('online', 'away', 'dnd', 'offline') DEFAULT 'offline'");

// Alter chat_messages
safeAlter($conn, "ALTER TABLE chat_messages ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
safeAlter($conn, "ALTER TABLE chat_messages ADD COLUMN room_id INT NULL AFTER receiver_id");
safeAlter($conn, "ALTER TABLE chat_messages ADD COLUMN is_edited TINYINT(1) DEFAULT 0 AFTER message");
safeAlter($conn, "ALTER TABLE chat_messages ADD COLUMN image_url VARCHAR(255) NULL AFTER message");
safeAlter($conn, "ALTER TABLE chat_messages ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER image_url");
safeAlter($conn, "ALTER TABLE chat_messages ADD COLUMN is_delivered TINYINT(1) DEFAULT 0 AFTER is_read");
safeAlter($conn, "ALTER TABLE chat_messages ADD COLUMN link_preview_json TEXT NULL AFTER is_delivered");

// Advanced Chat Tables
$conn->query("CREATE TABLE IF NOT EXISTS chat_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    cover_url VARCHAR(255) NULL,
    is_private TINYINT(1) DEFAULT 0,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS chat_room_members (
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_member (room_id, user_id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS chat_typing_status (
    user_id INT NOT NULL,
    target_id INT NOT NULL,
    target_type ENUM('user', 'room') NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_typing (user_id, target_id, target_type)
)");

$conn->query("CREATE TABLE IF NOT EXISTS blocked_users (
    user_id INT NOT NULL,
    blocked_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_block (user_id, blocked_id)
)");

// Alter forum_notifications to add link
safeAlter($conn, "ALTER TABLE forum_notifications ADD COLUMN link VARCHAR(255) NULL AFTER created_at");

// Fix forum_votes to use vote INT instead of vote_type ENUM
safeAlter($conn, "ALTER TABLE forum_votes DROP COLUMN vote_type");
safeAlter($conn, "ALTER TABLE forum_votes ADD COLUMN vote INT NOT NULL DEFAULT 1 AFTER user_id");

// Add image_url to forum tables
safeAlter($conn, "ALTER TABLE forum_threads ADD COLUMN image_url VARCHAR(255) NULL AFTER body");
safeAlter($conn, "ALTER TABLE forum_posts ADD COLUMN image_url VARCHAR(255) NULL AFTER body");

?>
