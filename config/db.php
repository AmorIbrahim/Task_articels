<?php
// اتصال قاعدة البيانات
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "articels_dash";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
session_start();
?>
