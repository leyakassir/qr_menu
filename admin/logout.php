<?php
// Logs the administrator out by destroying the session.

session_start();

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;