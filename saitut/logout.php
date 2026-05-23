<?php
session_start();
include "php/db.php";
include "php/logger.php";

session_destroy();
header("Location: index.php");
exit;
?>
