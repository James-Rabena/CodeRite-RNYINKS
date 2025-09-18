<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header("Location: login.php");
    exit();
}

// safe escape helper
function h($val) {
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}

$user_id = $_SESSION['user_id'];
$message = '';
$upload_dir = 'uploads/profile_pictures/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// fetch user
$stmt_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// fetch all billing
$stmt_billing = $conn->prepare("SELECT * FROM user_billing WHERE user_id = ? ORDER BY created_at DESC");
$stmt_billing->bind_param("i", $user_id);
$stmt_billing->execute();
$billings = $stmt_billing->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_billing->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // delete billing row
    if (isset($_POST['delete_billing_id'])) {
        $bid = (int) $_POST['delete_billing_id'];
        $stmt = $conn->prepare("DELETE FROM user_billing WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $bid, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = "Billing method deleted.";

    // add billing
    } elseif (isset($_POST['update_billing'])) {
        if ($_POST['update_billing'] === 'confirm_gcash') {
            $phone_number = $user['phone'];
            if (!empty($phone_number)) {
                $sql = "INSERT INTO user_billing (user_id, provider, customer_id, card_brand, card_last_four)
                        VALUES (?, 'GCash', ?, 'Mobile', NULL)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $user_id, $phone_number);
                $stmt->execute();
                $stmt->close();
                $message = "GCash added.";
            } else {
                $message = "Please add a phone number to your profile first.";
            }
        } elseif ($_POST['update_billing'] === 'confirm_card') {
            $card_holder = trim($_POST['card_holder_name']);
            $card_number = preg_replace('/\s+/', '', $_POST['card_number']);
            $last_four = substr($card_number, -4);
            $sql = "INSERT INTO user_billing (user_id, provider, customer_id, card_brand, card_last_four)
                    VALUES (?, 'Credit Card', ?, 'Card', ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $user_id, $card_holder, $last_four);
            $stmt->execute();
            $stmt->close();
            $message = "Credit card saved.";
        } elseif ($_POST['update_billing'] === 'confirm_bank') {
            $account_holder = trim($_POST['account_holder_name']);
            $bank_name = trim($_POST['bank_name']);
            $account_number = preg_replace('/\s+/', '', $_POST['account_number']);
            $last_four = substr($account_number, -4);
            $sql = "INSERT INTO user_billing (user_id, provider, customer_id, card_brand, card_last_four)
                    VALUES (?, 'Bank Transfer', ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $user_id, $account_holder, $bank_name, $last_four);
            $stmt->execute();
            $stmt->close();
            $message = "Bank details saved.";
        }

    // update profile info
    } else {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $file = $_FILES['profile_picture'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($file['type'], $allowed_types)) {
                if ($user && !empty($user['profile_picture_path']) && file_exists($user['profile_picture_path'])) {
                    unlink($user['profile_picture_path']);
                }
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = $user_id . '.' . $extension;
                $target_path = $upload_dir . $new_filename;
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $stmt_pic = $conn->prepare("UPDATE users SET profile_picture_path = ? WHERE id = ?");
                    $stmt_pic->bind_param("si", $target_path, $user_id);
                    $stmt_pic->execute();
                    $stmt_pic->close();
                    $_SESSION['profile_picture'] = $target_path;
                    $message = "Profile picture updated. ";
                } else {
                    $message = "Error moving uploaded file.";
                }
            } else {
                $message = "Invalid file type.";
            }
        }

        $firstname = trim($_POST['firstname']);
        $lastname = trim($_POST['lastname']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $stmt_main = $conn->prepare("UPDATE users SET firstname=?, lastname=?, phone=?, address=? WHERE id=?");
        $stmt_main->bind_param("ssssi", $firstname, $lastname, $phone, $address, $user_id);
        $stmt_main->execute();
        $stmt_main->close();
        $_SESSION['user_name'] = $firstname . ' ' . $lastname;
        $message .= "Profile updated.";
    }

    // refresh user & billing
    $stmt_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();

    $stmt_billing = $conn->prepare("SELECT * FROM user_billing WHERE user_id = ? ORDER BY created_at DESC");
    $stmt_billing->bind_param("i", $user_id);
    $stmt_billing->execute();
    $billings = $stmt_billing->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_billing->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-5 mb-5">
    <h2>My Profile</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= h($message) ?></div>
    <?php endif; ?>
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <?php
                    $profilePicPath = (!empty($user['profile_picture_path']) && file_exists($user['profile_picture_path']))
                        ? $user['profile_picture_path'] : 'assets/default-avatar.png';
                    if ($profilePicPath !== 'assets/default-avatar.png')
                        $profilePicPath .= '?t=' . time();
                    ?>
                    <img src="<?= h($profilePicPath); ?>" class="img-thumbnail rounded-circle"
                         style="width:150px;height:150px;object-fit:cover;">
                    <h5 class="my-3"><?= h($user['firstname'] . ' ' . $user['lastname']); ?></h5>
                    <p class="text-muted mb-1"><?= h($user['email']); ?></p>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Billing Details</h5>
                    <hr>
                    <?php if (!empty($billings)): ?>
                        <?php foreach ($billings as $b): ?>
                            <form action="profile.php" method="post" class="border p-2 rounded mb-2">
                                <strong>Provider:</strong> <?= h($b['provider']); ?><br>
                                <?php if (!empty($b['card_brand']) && !empty($b['card_last_four'])): ?>
                                    <strong>Details:</strong> <?= h($b['card_brand']); ?>
                                    ****<?= h($b['card_last_four']); ?>
                                <?php else: ?>
                                    <strong>Account:</strong> <?= h($b['customer_id']); ?>
                                <?php endif; ?>
                                <br><small class="text-muted"><?= h($b['created_at']); ?></small><br>
                                <input type="hidden" name="delete_billing_id" value="<?= (int) $b['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger mt-2">Delete</button>
                            </form>
                        <?php endforeach; ?>
                        <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#addPaymentModal">Add Another Payment Method</button>
                    <?php else: ?>
                        <p>No payment method on file.</p>
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#addPaymentModal">Add Payment Method</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <form action="profile.php" method="post" enctype="multipart/form-data">
                        <h5 class="card-title">Profile & Shipping Details</h5>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Change Profile Picture</label>
                            <input type="file" class="form-control" name="profile_picture" accept="image/*">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="firstname" name="firstname"
                                       value="<?= h($user['firstname']) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastname" name="lastname"
                                       value="<?= h($user['lastname']) ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= h($user['phone']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Shipping Address</label>
                            <textarea class="form-control" id="address" name="address"
                                      rows="4"><?= h($user['address']) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save All Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentModalLabel">Add a Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="profile.php" method="post">
                <div class="modal-body">
                    <p>Select your payment method:</p>
                    <div class="list-group">
                        <label class="list-group-item list-group-item-action">
                            <input type="radio" name="payment_method" value="gcash" class="form-check-input me-2"
                                   required> GCash
                        </label>
                        <label class="list-group-item list-group-item-action">
                            <input type="radio" name="payment_method" value="card" class="form-check-input me-2">
                            Credit/Debit Card
                        </label>
                        <label class="list-group-item list-group-item-action">
                            <input type="radio" name="payment_method" value="bank" class="form-check-input me-2">
                            Bank Information
                        </label>
                    </div>

                    <div id="gcash-details" class="mt-4 border p-3 rounded" style="display:none;">
                        <h5>Confirm GCash Number</h5>
                        <p>Is your registered phone number <strong><?= h($user['phone']); ?></strong>
                            also your GCash number?</p>
                        <button type="submit" name="update_billing" value="confirm_gcash"
                                class="btn btn-primary">Yes, this is my GCash number</button>
                    </div>

                    <div id="card-details" class="mt-4 border p-3 rounded" style="display:none;">
                        <h5>Enter Card Details</h5>
                        <div class="mb-3">
                            <label for="card_holder_name" class="form-label">Cardholder Name</label>
                            <input type="text" class="form-control" name="card_holder_name"
                                   placeholder="John M. Doe">
                        </div>
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card Number</label>
                            <input type="text" class="form-control" name="card_number"
                                   placeholder="•••• •••• •••• ••••">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="card_expiry" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" name="card_expiry" placeholder="MM/YY">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="card_cvv" class="form-label">CVV</label>
                                <input type="text" class="form-control" name="card_cvv" placeholder="•••">
                            </div>
                        </div>
                        <button type="submit" name="update_billing" value="confirm_card"
                                class="btn btn-primary">Save Card</button>
                    </div>

                    <div id="bank-details" class="mt-4 border p-3 rounded" style="display:none;">
                        <h5>Enter Bank Details</h5>
                        <div class="mb-3"><label class="form-label">Account Holder Name</label><input type="text"
                                                                                                     class="form-control" name="account_holder_name"></div>
                        <div class="mb-3"><label class="form-label">Bank Name</label><input type="text"
                                                                                            class="form-control" name="bank_name"></div>
                        <div class="mb-3"><label class="form-label">Account Number</label><input type="text"
                                                                                                 class="form-control" name="account_number"></div>
                        <button type="submit" name="update_billing" value="confirm_bank"
                                class="btn btn-primary">Save Bank Info</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<script>
    document.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.getElementById('gcash-details').style.display = 'none';
            document.getElementById('card-details').style.display = 'none';
            document.getElementById('bank-details').style.display = 'none';
            if (this.value === 'gcash') document.getElementById('gcash-details').style.display = 'block';
            if (this.value === 'card') document.getElementById('card-details').style.display = 'block';
            if (this.value === 'bank') document.getElementById('bank-details').style.display = 'block';
        });
    });
</script>
</body>
</html>
<?php $conn->close(); ?>
