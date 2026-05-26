<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "myshop";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Помилка підключення: " . $conn->connect_error);
}

session_start();
?>