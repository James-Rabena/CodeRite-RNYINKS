<?php
session_start();
if (
    !isset($_SESSION['user_logged_in']) ||
    !in_array($_SESSION['user_role'], ['superadmin', 'product_admin'])
) {
    header('Location: admindashboard.php');
    exit();
}
include 'db_connection.php';

// fetch products
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY id");
while ($row = $result->fetch_assoc())
    $products[] = $row;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container py-4">
    <h2>Manage Products</h2>

    <!-- Add Product Form -->
    <h4>Add New Product</h4>
    <form id="addProductForm" class="row g-2 mb-4">
        <div class="col"><input type="text" placeholder="Name" class="form-control" name="name" required></div>
        <div class="col"><input type="text" placeholder="Description" class="form-control" name="description"></div>
        <div class="col"><input type="number" step="0.01" placeholder="Price" class="form-control" name="price"
                required></div>

        <!-- CATEGORY DROPDOWN -->
        <div class="col">
            <select class="form-select" name="category" id="categorySelect" required>
                <option value="">Select Category</option>
                <option value="Fountain Pens">Fountain Pens</option>
                <option value="Ink">Ink</option>
                <option value="Paper">Paper</option>
            </select>
        </div>

        <!-- SUBCATEGORY DROPDOWN -->
        <div class="col">
            <select class="form-select" name="subcategory" id="subcategorySelect" required>
                <option value="">Select Subcategory</option>
            </select>
        </div>

        <div class="col"><input type="number" placeholder="Stock" class="form-control" name="stock" required></div>
        <div class="col-auto"><button type="submit" class="btn btn-primary">Add Product</button></div>
    </form>

    <!-- Products Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Stock</th>
                <th>Active</th>
                <th>Save</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $prod): ?>
                <tr data-id="<?= $prod['id'] ?>">
                    <td><?= $prod['id'] ?></td>
                    <td><input type="text" value="<?= htmlspecialchars($prod['name']) ?>" class="form-control p-name"></td>
                    <td><input type="text" value="<?= htmlspecialchars($prod['description']) ?>"
                            class="form-control p-desc"></td>
                    <td><input type="number" step="0.01" value="<?= $prod['price'] ?>" class="form-control p-price"></td>

                    <!-- CATEGORY DROPDOWN -->
                    <td>
                        <select class="form-select p-cat">
                            <option value="Fountain Pens" <?= $prod['category'] == "Fountain Pens" ? "selected" : "" ?>>
                                Fountain Pens</option>
                            <option value="Ink" <?= $prod['category'] == "Ink" ? "selected" : "" ?>>Ink</option>
                            <option value="Paper" <?= $prod['category'] == "Paper" ? "selected" : "" ?>>Paper</option>
                        </select>
                    </td>

                    <!-- SUBCATEGORY DROPDOWN -->
                    <td>
                        <select class="form-select p-subcat"></select>
                    </td>

                    <td><input type="number" value="<?= $prod['stock_quantity'] ?>" class="form-control p-stock"></td>
                    <td><input type="checkbox" class="form-check-input p-active" <?= $prod['is_active'] ? 'checked' : '' ?>>
                    </td>
                    <td><button class="btn btn-success btn-sm" onclick="saveProduct(<?= $prod['id'] ?>)">Save</button></td>
                    <td><button class="btn btn-danger btn-sm"
                            onclick="deleteProduct(<?= $prod['id'] ?>, this)">Delete</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="admindashboard.php" class="btn btn-secondary">Back to Dashboard</a>

    <script>
        const subcategories = {
            "Fountain Pens": ["Piston_Filler", "Vacuum_Filler", "Cartridge_Converter"],
            "Ink": ["Shimmering", "Sheening", "Shading"],
            "Paper": ["Dotted", "Grid", "Lined"]
        };

        // -- ADD PRODUCT FORM: Populate subcategories when category changes --
        document.getElementById('categorySelect').addEventListener('change', function () {
            const subcatSelect = document.getElementById('subcategorySelect');
            subcatSelect.innerHTML = '<option value="">Select Subcategory</option>';
            const cat = this.value;
            if (subcategories[cat]) {
                subcategories[cat].forEach(sub => {
                    let opt = document.createElement('option');
                    opt.value = sub;
                    opt.textContent = sub;
                    subcatSelect.appendChild(opt);
                });
            }
        });

        // -- EDIT ROWS: Populate subcategory dropdowns based on selected category --
        document.querySelectorAll('tr[data-id]').forEach(row => {
            const catSelect = row.querySelector('.p-cat');
            const subcatSelect = row.querySelector('.p-subcat');
            const currentSubcat = <?= json_encode(array_column($products, 'subcategory', 'id')) ?>[row.dataset.id];

            function populateRowSubcats() {
                const cat = catSelect.value;
                subcatSelect.innerHTML = '';
                if (subcategories[cat]) {
                    subcategories[cat].forEach(sub => {
                        let opt = document.createElement('option');
                        opt.value = sub;
                        opt.textContent = sub;
                        if (sub === currentSubcat) opt.selected = true;
                        subcatSelect.appendChild(opt);
                    });
                }
            }
            populateRowSubcats();
            catSelect.addEventListener('change', populateRowSubcats);
        });

        function saveProduct(id) {
            const row = document.querySelector(`tr[data-id='${id}']`);
            const data = {
                id: id,
                name: row.querySelector('.p-name').value,
                description: row.querySelector('.p-desc').value,
                price: row.querySelector('.p-price').value,
                category: row.querySelector('.p-cat').value,
                subcategory: row.querySelector('.p-subcat').value,
                stock: row.querySelector('.p-stock').value,
                active: row.querySelector('.p-active').checked ? 1 : 0
            };
            fetch('update_product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(r => r.json())
                .then(res => alert(res.success ? 'Updated!' : 'Error: ' + res.error));
        }

        function deleteProduct(id, btn) {
            if (confirm('Delete this product?')) {
                fetch('delete_product.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) btn.closest('tr').remove();
                        else alert(res.error);
                    });
            }
        }

        // Add new product
        document.getElementById('addProductForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('add_product.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        alert('Product added!');
                        location.reload();
                    } else alert(res.error);
                });
        });
    </script>
</body>

</html>
<?php $conn->close(); ?>