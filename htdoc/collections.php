<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db_connection.php';

if (isset($_GET['debug']) && $_GET['debug'] === 'schema') {
    // Which DB are we actually connected to?
    $db = $conn->query("SELECT DATABASE() AS db")->fetch_assoc()['db'];
    echo "<pre>Connected DB: {$db}\n\nCOLUMNS in product_sales:\n";

    if ($res = $conn->query("SHOW COLUMNS FROM product_sales")) {
        while ($r = $res->fetch_assoc()) {
            echo "{$r['Field']}  {$r['Type']}\n";
        }
    } else {
        echo "SHOW COLUMNS failed: " . $conn->error . "\n";
    }

    echo "\n\nCREATE TABLE product_sales:\n";
    if ($res = $conn->query("SHOW CREATE TABLE product_sales")) {
        $row = $res->fetch_assoc();
        echo $row['Create Table'] ?? '(no create table text)';
    } else {
        echo "SHOW CREATE failed: " . $conn->error . "\n";
    }
    echo "</pre>";
    exit;
}

// -- HELPER FUNCTION to preserve filters when sorting --
function build_query_string($new_params)
{
    $current_params = $_GET;
    $merged_params = array_merge($current_params, $new_params);
    return http_build_query($merged_params);
}

// Filters & Sorting
$category = isset($_GET['category']) ? trim($_GET['category']) : null;
$subcategory = isset($_GET['subcategory']) ? trim($_GET['subcategory']) : null;
$product_to_view = isset($_GET['view_product']) ? (int) $_GET['view_product'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$sort_options = [
    'newest' => 'Newest Arrivals',
    'price_asc' => 'Price: Low to High',
    'price_desc' => 'Price: High to Low',
    'name_asc' => 'Name: A-Z',
    'name_desc' => 'Name: Z-A',
];
$sort_key = isset($_GET['sort']) && array_key_exists($_GET['sort'], $sort_options) ? $_GET['sort'] : 'newest';

$sql = "SELECT 
    MIN(p.id) AS id,
    p.name,
    MIN(p.description) AS description,
    MIN(p.price) AS price,
    MIN(p.image_url) AS image_url,
    MIN(p.stock_quantity) AS stock_quantity,
    MIN(p.category) AS category,
    MIN(p.subcategory) AS subcategory,
    MAX(ps.discount_percentage) AS discount_percentage,
    MAX(ps.end_date) AS end_date
FROM products p
LEFT JOIN if0_39696871_rnyinks.product_seasons ps
    ON p.id = ps.product_id 
   AND NOW() BETWEEN ps.start_date AND ps.end_date
WHERE p.is_active = 1";




$params = [];
$types = "";

if ($category) {
    $sql .= " AND p.category = ?";
    $params[] = $category;
    $types .= "s";
}
if ($subcategory) {
    $sql .= " AND p.subcategory = ?";
    $params[] = $subcategory;
    $types .= "s";
}
if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

$sql .= " GROUP BY p.name";

switch ($sort_key) {
    case 'price_asc':
        $orderBy = "MIN(p.price) ASC, p.name ASC";
        break;
    case 'price_desc':
        $orderBy = "MIN(p.price) DESC, p.name ASC";
        break;
    case 'name_asc':
        $orderBy = "p.name ASC";
        break;
    case 'name_desc':
        $orderBy = "p.name DESC";
        break;
    case 'newest':
    default:
        $orderBy = "MIN(p.id) DESC";
        break;
}
$sql .= " ORDER BY " . $orderBy;

// DEBUG: print the final query and stop execution
//echo "<pre>FINAL QUERY:\n$sql\n</pre>";
//exit;
//echo "<pre>$sql</pre>";


$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Other data fetching (cart count, wishlist, etc.) remains the same...
$cartCount = 0;
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $cartCount = $row['cart_count'] ?: 0;
    $stmt->close();
}
$user_wishlist = [];
if (isset($_SESSION['user_id'])) {
    $wishlist_stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $wishlist_stmt->bind_param("i", $_SESSION['user_id']);
    $wishlist_stmt->execute();
    $wishlist_result = $wishlist_stmt->get_result();
    while ($row = $wishlist_result->fetch_assoc()) {
        $user_wishlist[] = $row['product_id'];
    }
    $wishlist_stmt->close();
}
$catResult = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$categories = [];
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row['category'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fragrance Fusion Collections</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="collections.css">
    <style>
        /* --- General Page & Helpers --- */
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1060;
        }

        /* --- Filter & Sort Bar --- */
        .filter-btn {
            min-width: 250px;
            text-align: left;
            display: inline-flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-group-left {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* --- Product Card Styling --- */
        .card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            text-align: center;
            /* Center-aligns all text content */
        }

        /* --- Image & Badge Styling --- */
        .card-img-container {
            position: relative;
        }

        .card-img-top {
            aspect-ratio: 1 / 1;
            object-fit: cover;
            /* Fills the container, fixing the "small image" issue */
            width: 100%;
        }

        .sale-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            /* Moved to top-left for better visibility */
            background-color: #dc3545;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 10;
        }

        .wishlist-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #eee;
        }

        /* --- Card Content Styling --- */
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-text {
            color: #6c757d;
            font-size: 0.9rem;
            min-height: 4.5em;
            /* Prevents layout jumps with different description lengths */
        }

        /* --- Price Styling (with Strikethrough) --- */
        .price-wrapper {
            margin-bottom: 0.75rem;
        }

        .original-price {
            text-decoration: line-through;
            color: #6c757d;
            margin-right: 8px;
        }

        .sale-price {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.1rem;
        }

        /* --- Styles for Modal --- */
        .savings-info {
            color: #198754;
            font-weight: bold;
        }

        .countdown-timer {
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="toast-container"></div>

    <?php include 'header.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Our Collections</h2>

        <!-- Search and Filter Bar -->
        <div class="mb-4">
            <!-- Search Form - Placed above the filter bar -->
            <form action="collections.php" method="GET" class="d-flex mx-auto mb-3" style="max-width: 500px;">
                <!-- Hidden inputs to preserve filters -->
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_key); ?>">
                <?php if ($category): ?><input type="hidden" name="category"
                        value="<?php echo htmlspecialchars($category); ?>"><?php endif; ?>
                <?php if ($subcategory): ?><input type="hidden" name="subcategory"
                        value="<?php echo htmlspecialchars($subcategory); ?>"><?php endif; ?>

                <input type="text" name="search" class="form-control me-2" placeholder="Search products..."
                    value="<?php echo htmlspecialchars($search ?? ''); ?>">
                <button type="submit" class="btn btn-success"><i class="fas fa-search"></i></button>
            </form>

            <!-- Filter and Sort Controls -->
            <div class="filter-bar">
                <!-- Group for Category/Subcategory Dropdowns (LEFT) -->
                <div class="filter-group-left">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle filter-btn" type="button"
                            data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($category ?: "All Categories"); ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item"
                                    href="?<?php echo build_query_string(['category' => '', 'subcategory' => '']); ?>">All</a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a class="dropdown-item"
                                        href="?<?php echo build_query_string(['category' => $cat, 'subcategory' => '']); ?>">
                                        <?php echo htmlspecialchars($cat); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <?php if ($category): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle filter-btn" type="button"
                                data-bs-toggle="dropdown">
                                <?php echo htmlspecialchars($subcategory ?: "All Subcategories"); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item"
                                        href="?<?php echo build_query_string(['subcategory' => '']); ?>">All</a></li>
                                <?php
                                $subStmt = $conn->prepare("SELECT DISTINCT subcategory FROM products WHERE category = ? ORDER BY subcategory");
                                $subStmt->bind_param("s", $category);
                                $subStmt->execute();
                                $subRes = $subStmt->get_result();
                                while ($sub = $subRes->fetch_assoc()): ?>
                                    <li><a class="dropdown-item"
                                            href="?<?php echo build_query_string(['subcategory' => $sub['subcategory']]); ?>"><?php echo htmlspecialchars($sub['subcategory']); ?></a>
                                    </li>
                                <?php endwhile;
                                $subStmt->close(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Group for Sorting Dropdown (RIGHT) -->
                <div class="sort-group-right">
                    <div class="dropdown">
                        <button class="btn btn-outline-dark dropdown-toggle filter-btn" type="button"
                            data-bs-toggle="dropdown">
                            Sort by: <?php echo $sort_options[$sort_key]; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach ($sort_options as $key => $value): ?>
                                <li>
                                    <a class="dropdown-item" href="?<?php echo build_query_string(['sort' => $key]); ?>">
                                        <?php echo $value; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>


        <!-- Products -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if ($products): ?>
                <?php foreach ($products as $product): ?>
                    <?php
                    // PHP discount logic (no changes)
                    $is_on_sale = !empty($product['discount_percentage']);
                    $sale_price = $product['price'];
                    if ($is_on_sale) {
                        $sale_price = $product['price'] * (1 - $product['discount_percentage'] / 100);
                        $savings = $product['price'] - $sale_price;
                        $time_left = '';

                        if (!empty($product['end_date'])) {
                            $end_date = new DateTime($product['end_date']);
                            $now = new DateTime();
                            $interval = $now->diff($end_date);

                            if ($interval->invert == 0) {
                                if ($interval->d > 0) {
                                    $time_left = $interval->format('%a days, %h hours left');
                                } elseif ($interval->h > 0) {
                                    $time_left = $interval->format('%h hours, %i minutes left');
                                } else {
                                    $time_left = $interval->format('%i minutes left');
                                }
                            }
                        }
                    }
                    ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-img-container">
                                <?php if ($is_on_sale): ?>
                                    <span class="sale-badge">-<?php echo (int) $product['discount_percentage']; ?>% OFF</span>
                                <?php endif; ?>
                                <?php
                                $is_wishlisted = in_array($product['id'], $user_wishlist);
                                $heart_class = $is_wishlisted ? 'fas' : 'far';
                                $heart_color = $is_wishlisted ? 'style="color: red;"' : '';
                                ?>
                                <button type="button" class="btn btn-light wishlist-btn"
                                    data-product-id="<?php echo $product['id']; ?>">
                                    <i class="<?php echo $heart_class; ?> fa-heart" <?php echo $heart_color; ?>></i>
                                </button>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <!-- ========================================================== -->
                            <!-- == CORRECTED CARD BODY STRUCTURE                      == -->
                            <!-- ========================================================== -->
                            <!-- The key is adding d-flex and flex-column here -->
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <div class="price-wrapper mb-2">
                                    <?php if ($is_on_sale): ?>
                                        <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                                        <span class="sale-price">$<?php echo number_format($sale_price, 2); ?></span>
                                    <?php else: ?>
                                        <span>$<?php echo number_format($product['price'], 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text small"><?php echo htmlspecialchars($product['description']); ?></p>
                                <p><small class="text-muted"><?php echo htmlspecialchars($product['category']); ?> →
                                        <?php echo htmlspecialchars($product['subcategory']); ?></small></p>

                                <!-- The key is adding mt-auto (margin-top: auto) here -->
                                <div class="card-buttons d-flex mt-auto pt-2">
                                    <button class="btn btn-dark btn-sm flex-grow-1 me-1 cart-btn"
                                        data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm flex-grow-1 ms-1" data-bs-toggle="modal"
                                        data-bs-target="#modal-<?php echo $product['id']; ?>">
                                        View
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================== -->
                    <!-- == MODAL MOVED BACK INSIDE THE FOREACH LOOP           == -->
                    <!-- ========================================================== -->
                    <div class="modal fade" id="modal-<?php echo $product['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="img-fluid mb-3"
                                        alt="<?php echo htmlspecialchars($product['name']); ?> Details">
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?>
                                    </p>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category']); ?></p>

                                    <!-- Modal price display -->
                                    <?php if ($is_on_sale): ?>
                                        <p><strong>Price:</strong>
                                            <span class="original-price">$<?php echo number_format($product['price'], 2); ?></span>
                                            <span class="sale-price fs-5">$<?php echo number_format($sale_price, 2); ?></span>
                                        </p>
                                        <p class="savings-info">You Save: $<?php echo number_format($savings, 2); ?></p>
                                        <?php if (!empty($time_left)): ?>
                                            <p class="countdown-timer"><i class="far fa-clock"></i> Sale ends in:
                                                <?php echo $time_left; ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                                    <?php endif; ?>

                                    <p><strong>Availability:</strong>
                                        <?php if ($product['stock_quantity'] > 10): ?>
                                            <span class="badge bg-success">In Stock</span>
                                        <?php elseif ($product['stock_quantity'] > 0): ?>
                                            <span class="badge bg-warning text-dark">Only <?php echo $product['stock_quantity']; ?>
                                                left</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm"
                                        data-bs-dismiss="modal">Close</button>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <button type="button" class="cart-btn btn btn-primary btn-sm"
                                            data-product-id="<?php echo $product['id']; ?>" data-bs-dismiss="modal">
                                            <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary btn-sm" disabled>Out of Stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">No products found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>


    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const isLoggedIn = <?php echo json_encode(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']); ?>;
        const productData = <?php echo json_encode($products); ?>;

        // ==========================================================
        // == THIS IS THE ONLY FUNCTION THAT HAS BEEN CHANGED     ==
        // ==========================================================
        function updateCartBadge(count) {
            const badge = document.getElementById('header-cart-badge');
            if (badge) {
                if (count > 0) { badge.textContent = count; badge.style.display = ''; }
                else { badge.style.display = 'none'; }
            }
        }

        function showNotification(title, message, type = 'success') {
            const toastEl = document.createElement('div');
            toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.innerHTML = `<div class="d-flex"><div class="toast-body"><strong>${title}</strong><br>${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
            document.querySelector('.toast-container').appendChild(toastEl);
            new bootstrap.Toast(toastEl, { delay: 3000 }).show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        function addToCart(product, button) {
            // If not logged in, show red toast and redirect to signup
            if (!isLoggedIn) {
                showNotification(
                    'Login Required',
                    'You must sign up or log in before adding items to the cart.',
                    'danger'
                );
                setTimeout(() => {
                    window.location.href = 'signup.php';
                }, 2000); // 2 seconds delay to let user see the toast
                return;
            }

            const originalContent = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
            button.disabled = true;

            if (product.stock_quantity <= 0) {
                showNotification('Out of Stock', `${product.name} is currently out of stock.`, 'danger');
                button.innerHTML = originalContent;
                button.disabled = false;
                return;
            }

            const cartProduct = {
                action: 'add',
                product_id: parseInt(product.id)
            };

            fetch('cart_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cartProduct)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Added to Cart', `${product.name} added successfully.`);
                        if (typeof data.cartCount !== 'undefined') {
                            updateCartBadge(data.cartCount);
                        }
                    } else {
                        showNotification('Error', data.message, 'danger');
                    }
                })
                .catch(err => showNotification('Error', 'Network error. Please try again.', 'danger'))
                .finally(() => {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                });
        }
        function addToWishlist(productId, button) {
            if (!isLoggedIn) {
                window.location.href = 'login.php';
                return;
            }

            const icon = button.querySelector('i');
            button.disabled = true;

            const formData = new FormData();
            formData.append('product_id', productId);

            fetch('add_to_wishlist.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.action === 'added') {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            icon.style.color = 'red';
                            showNotification('Success!', 'Added to your wishlist.', 'success');
                        } else if (data.action === 'removed') {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            icon.style.color = '';
                            showNotification('Success!', 'Removed from your wishlist.', 'info');
                        }
                        if (typeof updateWishlistModal === 'function') {
                            updateWishlistModal();
                        }
                    } else {
                        showNotification('Error', data.message, 'danger');
                    }
                })
                .catch(error => {
                    showNotification('Error', 'A network error occurred.', 'danger');
                })
                .finally(() => {
                    button.disabled = false;
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.cart-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const productId = this.getAttribute('data-product-id');
                    const product = productData.find(p => p.id == productId);
                    if (product) addToCart(product, this);
                });
            });

            // This line now just sets the initial state on page load.
            // The updateCartBadge function will handle dynamic updates.
            updateCartBadge(<?php echo $cartCount; ?>);

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            document.querySelectorAll('.wishlist-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const productId = this.getAttribute('data-product-id');
                    addToWishlist(productId, this);
                });
            });
            const productToViewId = <?php echo json_encode($product_to_view); ?>;
            if (productToViewId) {
                const modalToOpen = document.getElementById(`modal-${productToViewId}`);
                if (modalToOpen) {
                    const bootstrapModal = new bootstrap.Modal(modalToOpen);
                    bootstrapModal.show();
                }
            }
        });
    </script>
</body>

</html>