<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'backend/config/db.php';
include_once 'backend/models/Product.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed.");
}

$product = new Product($db);
$q = 'cemento';
$lat = '4.6097';
$lng = '-74.0817';
$radius = '50';

try {
    $stmt = $product->searchWithDistance($q, $lat, $lng, $radius);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Results for 'cemento':\n";
    print_r($results);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
