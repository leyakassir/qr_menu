<!-- //<?php
//$conn=new mysqli('localhost','root','','qr_menu');
//if($conn->connect_error){
    //die("Connection failed: " . $conn->connect_error);
//}
// echo "Connected successfully";
// 
//<?php
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'qr_menu';
$port = getenv('DB_PORT') ?: 3306;

$conn = mysqli_init();

if ($host !== 'localhost') {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
}

if (!mysqli_real_connect($conn, $host, $user, $password, $database, $port, NULL, ($host !== 'localhost') ? MYSQLI_CLIENT_SSL : 0)) {
    die("Connect Error (" . mysqli_connect_errno() . ") " . mysqli_connect_error());
}

$conn->set_charset("utf8mb4");
?>