<?php
// config.php

// ====== DATABASE CONFIG ======
$host = "localhost";
$dbname = "health_tracker";
$username = "root"; // sesuaikan dengan user DB kamu
$password = "";     // sesuaikan dengan password DB kamu

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ====== GEMINI API CONFIG ======
define("GEMINI_API_KEY", "AIzaSyDq2H0NQRR6RR6AsVngdk7I97fBN7TwUcE");

// Endpoint Gemini (Google Generative AI)
define("GEMINI_API_URL", "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . GEMINI_API_KEY);