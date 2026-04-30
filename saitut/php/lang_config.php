<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang'])) {
    $allowed_langs = ['en', 'sv'];
    if (in_array($_GET['lang'], $allowed_langs)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
}

$current_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

$lang_file = __DIR__ . '/../lang/' . $current_lang . '.php';
if (file_exists($lang_file)) {
    include_once $lang_file;
} else {
    include_once __DIR__ . '/../lang/en.php';
}
?>
