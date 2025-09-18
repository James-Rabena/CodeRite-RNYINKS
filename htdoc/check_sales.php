<?php
session_start();
require_once __DIR__ . '/db_connection.php';
header('Content-Type: application/json');

// This script should only run for logged-in users
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    // Silently exit if not logged in. No error needed.
    exit;
}

$user_id = $_SESSION['user_id'];

// This is the exact same logic we removed from header.php
// Its job is to find wishlist items on sale that don't already have a notification.
$sql_find_sales = "
    SELECT p.id AS product_id, p.name, ps.discount_percentage, ps.id AS season_id
    FROM wishlist w
    INNER JOIN product_seasons ps ON w.product_id = ps.product_id
    INNER JOIN products p ON w.product_id = p.id
    LEFT JOIN notifications n ON w.user_id = n.user_id AND ps.id = n.season_id
    WHERE w.user_id = ? AND NOW() BETWEEN ps.start_date AND ps.end_date AND n.id IS NULL
";
$stmt_find_sales = $conn->prepare($sql_find_sales);
$stmt_find_sales->bind_param("i", $user_id);
$stmt_find_sales->execute();
$sales_to_notify = $stmt_find_sales->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_find_sales->close();

if (!empty($sales_to_notify)) {
    $stmt_insert_notif = $conn->prepare("INSERT INTO notifications (user_id, product_id, season_id, message, link) VALUES (?, ?, ?, ?, ?)");
    foreach ($sales_to_notify as $sale) {
        $message = htmlspecialchars($sale['name']) . " is now " . $sale['discount_percentage'] . "% off!";
        $link = "collections.php?view_product=" . $sale['product_id'];
        $stmt_insert_notif->bind_param("iiiss", $user_id, $sale['product_id'], $sale['season_id'], $message, $link);
        $stmt_insert_notif->execute();
    }
    $stmt_insert_notif->close();
    
    // Respond to the JavaScript to let it know new notifications were created
    echo json_encode(['success' => true, 'new_notifications' => count($sales_to_notify)]);
} else {
    // Respond that nothing new was found
    echo json_encode(['success' => true, 'new_notifications' => 0]);
}

$conn->close();