<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_logged_in'])) {
    $_SESSION['user_logged_in'] = false;
}

require_once __DIR__ . '/db_connection.php';

$cartCount = 0;
if ($_SESSION['user_logged_in']) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT SUM(quantity) as cart_count FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $cartCount = $row['cart_count'] ?: 0;
    }
    $stmt->close();
}

if (isset($_GET['login'])) {
    $_SESSION['user_logged_in'] = true;
    header("Location: index.php");
    exit();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RNYINKS</title>
    <meta name="description" content="Fragrance Fusion - Unique, memorable scents that tell stories">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            overflow-x: hidden;
        }

        .hero-section {
            margin-top: -11px;
        }

        .carousel-item {
            position: relative;
        }

        .carousel-item::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            background: linear-gradient(rgba(0, 0, 0, 0) 70%, rgba(0, 0, 0, 0.7) 100%);
        }

        .carousel-caption {
            position: absolute;
            z-index: 2;
        }

        .hero-section .carousel-caption h1 {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 10px 15px;
            border-radius: 15px;
            display: inline-block;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.9);
        }

        .hero-section .carousel-caption .hero-subtitle {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 10px 15px;
            border-radius: 15px;
            display: inline-block;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.9);
        }

        .hero-buttons .hero-btn {
            padding: 10px 25px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 30px;
            transition: all 0.3s ease-in-out;
            border: 2px solid;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .hero-buttons .primary-btn.btn-outline-light {
            border-color: #f8f9fa;
            color: #000;
            background-color: tranparent;
        }

        .hero-buttons .primary-btn.btn-outline-light:hover {
            background-color: #f8f9fa;
            color: #000;
            border-color: #000;
        }

        .hero-buttons .secondary-btn.btn-light {
            background-color: #f8f9fa;
            color: #000;
            border-color: #f8f9fa;
        }

        .hero-buttons .secondary-btn.btn-light:hover {
            background-color: #f8f9fa;
            color: #000;
            border-color: #000;
        }

        .fade-in-element {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .fade-in-element.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .perfume-category-row.fade-in-element:nth-of-type(2) {
            transition-delay: 100ms;
        }

        .perfume-category-row.fade-in-element:nth-of-type(3) {
            transition-delay: 200ms;
        }

        .perfume-category-row.fade-in-element:nth-of-type(4) {
            transition-delay: 300ms;
        }

        .perfume-category-row.fade-in-element:nth-of-type(5) {
            transition-delay: 400ms;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.8rem;
            }

            .hero-subtitle {
                font-size: 0.9rem;
                max-width: 90%;
            }

            .hero-buttons {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
                margin-top: 15px;
            }

            .hero-buttons .hero-btn {
                width: 80%;
                max-width: 300px;
                margin: 8px 0 !important;
            }

            .perfume-category-row,
            .perfume-category-row:nth-child(even) {
                flex-direction: column;
            }

            .category-info {
                text-align: center;
            }

            .hero-section .carousel-item {
                height: 65vh;
            }

            .hero-section .carousel-item img {
                height: 100%;
                object-fit: cover;
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <section class="hero-section p-0">
        <div id="carouselExampleIndicators" class="carousel slide carousel-fade w-100" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"
                    aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                    aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                    aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="https://external-content.duckduckgo.com/iu/?u=http%3A%2F%2Fi.huffpost.com%2Fgen%2F2046406%2Fimages%2Fo-WRITING-PEN-AND-PAPER-facebook.jpg&f=1&nofb=1&ipt=91d7e6b0502110eb17b7df355977c69de9673d11351975023a804d99c5c0b6ce"
                        class="d-block w-100" alt="Fragrance 1">
                    <div class="carousel-caption d-flex flex-column justify-content-center align-items-center"
                        style="top:0; bottom:0;">
                        <h1 class="hero-title">Make your Journaling have Meaning</h1>
                        <p class="hero-subtitle">Immerse yourself in a world of handcrafted pens that tell stories,
                            evoke emotions, and create lasting memories through history.</p>
                        <div class="hero-buttons">
                            <a href="collections.php" class="hero-btn primary-btn btn btn-outline-light me-2">Explore
                                Collections</a>
                            <a href="AboutUs.php" class="hero-btn secondary-btn btn btn-light">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fwallpapercave.com%2Fwp%2Fwp7467323.jpg&f=1&nofb=1&ipt=dd6005a72dd1a351aab4f7bfa575a032f0b3f784e7c5823a18e3da7703dbb3b4"
                        class="d-block w-100" alt="Fragrance 2">
                    <div class="carousel-caption d-flex flex-column justify-content-center align-items-center"
                        style="top:0; bottom:0;">
                        <h1 class="hero-title">Luxury Items in your Fingertips</h1>
                        <p class="hero-subtitle">Discover our exquisite collection of luxury pens that blend timeless
                            craftsmanship with modern elegance.</p>
                        <div class="hero-buttons">
                            <a href="collections.php" class="hero-btn primary-btn btn btn-outline-light me-2">Explore
                                Collections</a>
                            <a href="AboutUs.php" class="hero-btn secondary-btn btn btn-light">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="https://external-content.duckduckgo.com/iu/?u=http%3A%2F%2Fbrewminate.com%2Fwp-content%2Fuploads%2F2016%2F11%2FJournal03.jpg&f=1&nofb=1&ipt=e342babeb74c478f896d88b8a15acb81ff93da3153b7d96b5f60ae62ea8f148c"
                        class="d-block w-100" alt="Fragrance 3">
                    <div class="carousel-caption d-flex flex-column justify-content-center align-items-center"
                        style="top:0; bottom:0;">
                        <h1 class="hero-title">Made from Well-known Ink Brands</h1>
                        <p class="hero-subtitle">Elevate your writing experience with our premium pens, designed to
                            deliver smooth, consistent ink flow for effortless creativity.</p>
                        <div class="hero-buttons">
                            <a href="collections.php" class="hero-btn primary-btn btn btn-outline-light me-2">Explore
                                Collections</a>
                            <a href="AboutUs.php" class="hero-btn secondary-btn btn-light">Learn More</a>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
    </section>

    <div class="container my-5">
        <section class="dynamic-section mb-5 fade-in-element">
            <h2 class="section-title">Weekly Promotions</h2>
            <div class="row" id="promotions-row">
                <?php if (!empty($promotional_products)): ?>
                    <?php foreach ($promotional_products as $product): ?>
                        <?php
                        // Calculate the sale price
                        $original_price = $product['price'];
                        $discount_decimal = $product['discount_percentage'] / 100;
                        $sale_price = $original_price * (1 - $discount_decimal);
                        ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="product-card">
                                <div class="product-image-container">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                                    <span class="sale-badge"><?php echo htmlspecialchars($product['discount_percentage']); ?>%
                                        OFF</span>
                                </div>
                                <div class="product-info">
                                    <h5 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="product-price">
                                        <span class="original-price">$<?php echo number_format($original_price, 2); ?></span>
                                        <span class="sale-price">$<?php echo number_format($sale_price, 2); ?></span>
                                    </p>
                                    <a href="#" class="btn btn-dark w-100">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col">
                        <p class="text-center text-muted">No special promotions are running at the moment. Check back soon!
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="dynamic-section mb-5 fade-in-element">
            <h2 class="section-title">New Arrivals</h2>
            <div class="row">
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="images/kwz_honey.jpg" alt="KWZ Honey" class="product-image">
                            <span class="new-badge">NEW</span>
                        </div>
                        <div class="product-info">
                            <h5 class="product-name">KWZ Honey</h5>
                            <p class="product-price">$15.00</p>
                            <a href="#" class="btn btn-dark w-100">View Product</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="images/rhodia_grid.jpg" alt="Rhodia A4 Grid Pad" class="product-image">
                            <span class="new-badge">NEW</span>
                        </div>
                        <div class="product-info">
                            <h5 class="product-name">Rhodia A4 Grid Pad</h5>
                            <p class="product-price">$18.00</p>
                            <a href="#" class="btn btn-dark w-100">View Product</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="images/pelikan_4001.jpg" alt="Pelikan 4001 Brilliant Black" class="product-image">
                            <span class="new-badge">NEW</span>
                        </div>
                        <div class="product-info">
                            <h5 class="product-name">Pelikan 4001 Brilliant Black</h5>
                            <p class="product-price">$12.50</p>
                            <a href="#" class="btn btn-dark w-100">View Product</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="images/montblanc_notebook.jpg" alt="Montblanc Notebook" class="product-image">
                            <span class="new-badge">NEW</span>
                        </div>
                        <div class="product-info">
                            <h5 class="product-name">Montblanc Notebook</h5>
                            <p class="product-price">$45.00</p>
                            <a href="#" class="btn btn-dark w-100">View Product</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="images/diamine_aurora_borealis.jpg" alt="Diamine Aurora Borealis"
                                class="product-image">
                            <span class="new-badge">NEW</span>
                        </div>
                        <div class="product-info">
                            <h5 class="product-name">Diamine Aurora Borealis</h5>
                            <p class="product-price">$14.00</p>
                            <a href="#" class="btn btn-dark w-100">View Product</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="dynamic-section fade-in-element">
            <h2 class="section-title">Popular Products</h2>
            <div class="row">
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <img src="images/pilot_custom74.jpg" alt="Pilot Custom 74" class="product-image">
                        <div class="product-info">
                            <h5 class="product-name">Pilot Custom 74</h5>
                            <p class="product-price">$160.00</p>
                            <a href="#" class="btn btn-dark w-100">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <img src="images/lamy_safari.jpg" alt="Lamy Safari" class="product-image">
                        <div class="product-info">
                            <h5 class="product-name">Lamy Safari</h5>
                            <p class="product-price">$35.00</p>
                            <a href="#" class="btn btn-dark w-100">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <img src="images/twsbi_eco.jpg" alt="TWSBI Eco" class="product-image">
                        <div class="product-info">
                            <h5 class="product-name">TWSBI Eco</h5>
                            <p class="product-price">$40.00</p>
                            <a href="product.php?id=5" class="btn btn-dark w-100">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <img src="images/platinum_preppy.jpg" alt="Platinum Preppy" class="product-image">
                        <div class="product-info">
                            <h5 class="product-name">Platinum Preppy</h5>
                            <p class="product-price">$5.00</p>
                            <a href="product.php?id=6" class="btn btn-dark w-100">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'newsletter.php'; ?>
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="home.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1
            });

            const elementsToFadeIn = document.querySelectorAll('.fade-in-element');
            elementsToFadeIn.forEach(el => observer.observe(el));
        });
        function updatePromotions() {
            const promotionsRow = document.getElementById('promotions-row');
            if (!promotionsRow) return; // Only run if the element exists

            fetch('get_promotions.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let newHtml = '';
                        if (data.products.length > 0) {
                            data.products.forEach(product => {
                                const originalPrice = parseFloat(product.price);
                                const discountDecimal = parseFloat(product.discount_percentage) / 100;
                                const salePrice = originalPrice * (1 - discountDecimal);

                                newHtml += `
                                <div class="col-md-4 col-sm-6 mb-4">
                                    <div class="product-card">
                                        <div class="product-image-container">
                                            <img src="${product.image_url}" alt="${product.name}" class="product-image">
                                            <span class="sale-badge">${product.discount_percentage}% OFF</span>
                                        </div>
                                        <div class="product-info">
                                            <h5 class="product-name">${product.name}</h5>
                                            <p class="product-price">
                                                <span class="original-price">$${originalPrice.toFixed(2)}</span>
                                                <span class="sale-price">$${salePrice.toFixed(2)}</span>
                                            </p>
                                            <a href="#" class="btn btn-dark w-100">Add to Cart</a>
                                        </div>
                                    </div>
                                </div>`;
                            });
                        } else {
                            newHtml = '<div class="col"><p class="text-center text-muted">No special promotions are running at the moment. Check back soon!</p></div>';
                        }
                        promotionsRow.innerHTML = newHtml;
                    }
                });
        }

        // Check for new promotions every 60 seconds (60000 milliseconds)
        setInterval(updatePromotions, 60000);
    </script>
</body>

</html>