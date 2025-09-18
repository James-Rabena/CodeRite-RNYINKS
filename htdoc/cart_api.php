<?php
session_start();
require_once __DIR__ . '/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $data['action'];

try {
    switch ($action) {
        case 'add':
            $product_id = (int) $data['product_id'];
            if ($product_id <= 0) {
                throw new Exception('Invalid product ID.');
            }

            // Fetch product details AND any active discount
            $sql = "SELECT p.name, p.price, p.image_url, ps.discount_percentage
                    FROM products p
                    LEFT JOIN product_seasons ps ON p.id = ps.product_id AND NOW() BETWEEN ps.start_date AND ps.end_date
                    WHERE p.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();

            if (!$product)
                throw new Exception('Product not found.');

            $final_price = $product['price'];
            if (!empty($product['discount_percentage'])) {
                $final_price = $product['price'] * (1 - $product['discount_percentage'] / 100);
            }

            // Insert or update cart item
            $insert_sql = "INSERT INTO cart_items (user_id, product_id, product_name, price, image_url, quantity, active) 
                           VALUES (?, ?, ?, ?, ?, 1, 1) 
                           ON DUPLICATE KEY UPDATE quantity = quantity + 1, price = VALUES(price)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("iissd", $user_id, $product_id, $product['name'], $final_price, $product['image_url']);
            $stmt->execute();

            $count_stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart_items WHERE user_id = ?");
            $count_stmt->bind_param("i", $user_id);
            $count_stmt->execute();
            $new_cart_count = $count_stmt->get_result()->fetch_assoc()['cart_count'] ?? 0;

            echo json_encode(['success' => true, 'message' => 'Item added to cart.', 'cartCount' => $new_cart_count]);
            break;

        case 'update':
            // Update quantity using cart item ID
            $cart_id = (int) $data['cart_id'];
            $quantity = (int) $data['quantity'];
            if ($cart_id > 0 && $quantity > 0) {
                $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
                $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
                $stmt->execute();
                echo json_encode(['success' => true, 'message' => 'Cart updated.']);
            } else {
                throw new Exception('Invalid cart ID or quantity.');
            }
            break;

        case 'remove':
            // Remove item using cart item ID
            $cart_id = (int) $data['cart_id'];
            if ($cart_id > 0) {
                $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $cart_id, $user_id);
                $stmt->execute();
                echo json_encode(['success' => true, 'message' => 'Item removed.']);
            } else {
                throw new Exception('Invalid cart ID.');
            }
            break;

        case 'clear':
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Cart cleared.']);
            break;

        default:
            throw new Exception('Unknown action.');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>