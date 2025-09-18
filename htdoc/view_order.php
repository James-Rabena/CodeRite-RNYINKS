<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (
    !isset($_SESSION['user_logged_in']) ||
    !in_array($_SESSION['user_role'], ['superadmin', 'order_admin'])
) {
    header('Location: admindashboard.php');
    exit();
}
require_once 'db_connection.php';

// safe escape helper
function h($val)
{
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}

$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$source = isset($_GET['source']) ? $_GET['source'] : 'orders'; // default orders
if ($order_id <= 0) {
    echo "Invalid order ID.";
    exit;
}

// decide table + which column to use for status
switch ($source) {
    case 'ongoing':
        $table = 'ongoing_orders';
        $statusSelect = 'o.status AS status';
        break;
    case 'fulfilled':
        $table = 'fulfilled_orders';
        $statusSelect = "'Fulfilled' AS status"; // table has no status column
        break;
    default: // orders
        $table = 'orders';
        $statusSelect = 'o.delivery_status AS status'; // use delivery_status instead
        break;
}

// fetch order info
$sql = "
    SELECT 
        o.id,
        o.user_id,
        o.shipping_cost,
        $statusSelect,
        o.created_at,
        o.address,
        u.firstname,
        u.lastname,
        u.email,
        u.phone
    FROM {$table} o
    JOIN users u ON u.id = o.user_id
    WHERE o.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) {
    echo "Order not found in $table.";
    exit;
}

// fetch items for this order
$stmt = $conn->prepare("
    SELECT product_name, quantity, price, discount_applied
    FROM order_items
    WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$items_total = 0;
foreach ($items as $it) {
    $items_total += $it['quantity'] * ($it['price'] - $it['discount_applied']);
}
$total_amount = $items_total + ($order['shipping_cost'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo h($order_id); ?> Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">
    <h2>Order #<?php echo h($order_id); ?> Details (<?php echo h(ucfirst($source)); ?>)</h2>
    <p><strong>Customer:</strong>
        <?php echo h($order['firstname'] . ' ' . $order['lastname']); ?>
        (<?php echo h($order['email']); ?>, <?php echo h($order['phone']); ?>)</p>
    <p><strong>Address:</strong> <?php echo h($order['address']); ?></p>
    <p><strong>Status:</strong> <?php echo h($order['status']); ?></p>
    <p><strong>Placed At:</strong> <?php echo h($order['created_at']); ?></p>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Product</th>
                <th class="text-end">Quantity</th>
                <th class="text-end">Price</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it):
                $subtotal = $it['quantity'] * ($it['price'] - $it['discount_applied']); ?>
                <tr>
                    <td><?php echo h($it['product_name']); ?></td>
                    <td class="text-end"><?php echo (int) $it['quantity']; ?></td>
                    <td class="text-end">$<?php echo number_format($it['price'], 2); ?></td>
                    <td class="text-end">$<?php echo number_format($it['discount_applied'], 2); ?></td>
                    <td class="text-end">$<?php echo number_format($subtotal, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Items Total:</th>
                <th class="text-end">$<?php echo number_format($items_total, 2); ?></th>
            </tr>
            <tr>
                <th colspan="4" class="text-end">Shipping:</th>
                <th class="text-end">$<?php echo number_format($order['shipping_cost'] ?? 0, 2); ?></th>
            </tr>
            <tr class="fw-bold">
                <th colspan="4" class="text-end">Total Amount:</th>
                <th class="text-end">$<?php echo number_format($total_amount, 2); ?></th>
            </tr>
        </tfoot>
    </table>

    <a href="<?php
    echo $source === 'orders' ? 'new_order.php' :
        ($source === 'ongoing' ? 'ongoing_order.php' : 'fulfilled_order.php');
    ?>" class="btn btn-secondary">Back</a>
</body>

</html>
<?php $conn->close(); ?>