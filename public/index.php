<?php
require_once "../functions/auth.php";
if (!isAuthenticated()) {
    header("Location: login.php");
    exit;
}
?>