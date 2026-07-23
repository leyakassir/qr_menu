<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$name = getenv('DB_NAME');
$port = getenv('DB_PORT');

$conn = new mysqli($host, $user, $pass, $name, $port);
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "Connected successfully to Aiven database!";
}
?>