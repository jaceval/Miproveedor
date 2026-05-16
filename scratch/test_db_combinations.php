<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$hosts = ['127.0.0.1', 'localhost', '::1'];
$passwords = ['', 'root'];

foreach ($hosts as $host) {
    foreach ($passwords as $pass) {
        echo "Testing host: $host, password: '$pass' ... ";
        try {
            $conn = new PDO("mysql:host=$host;port=3306;dbname=miproveedor;charset=utf8mb4", "root", $pass);
            echo "SUCCESS!\n";
        } catch (PDOException $e) {
            echo "FAILED: " . $e->getMessage() . "\n";
        }
    }
}
?>
