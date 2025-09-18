<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

// Fetch current promotions from the database
$promotional_products = [];
$sql = "SELECT p.name, p.price, p.image_url, ps.discount_percentage
        FROM product_seasons ps
        JOIN products p ON ps.product_id = p.id
        WHERE NOW() BETWEEN ps.start_date AND ps.end_date
        ORDER BY ps.discount_percentage DESC
        LIMIT 3";

$promo_result = $conn->query($sql);
if ($promo_result) {
    $promotional_products = $promo_result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();

echo json_encode(['success' => true, 'products' => $promotional_products]);