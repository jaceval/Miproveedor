<?php
include 'backend/config/db.php';
include 'backend/models/Product.php';
$db = new Database();
$conn = $db->getConnection();
if (!$conn) { echo "DB ERROR\n"; exit; }
$prod = new Product($conn);
$stmt = $prod->searchWithDistance('ladrillo', 4.6097, -74.0817, 50);
if ($stmt) {
    $res = $stmt->fetchAll();
    echo "Results: " . count($res) . "\n";
    print_r($res);
} else {
    print_r($conn->errorInfo());
}
