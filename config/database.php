<?php
$conn=new mysqli('localhost','root','','qr_menu');
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully";
// ?>