<?php
// config.php

// ====== DATABASE CONFIG ======
$host = "localhost";
$user = "root";
$pass = "";
$db   = "health_tracker";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ====== GEMINI API CONFIG ======
define("GEMINI_API_KEY", "AIzaSyA8wM7pjZzEi5VeuwzLFSsIeTQ4rechYzo");

// Endpoint Gemini (Google Generative AI)
define("GEMINI_API_URL", "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY);
