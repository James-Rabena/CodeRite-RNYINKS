<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db_connection.php';

$data = json_decode(file_get_contents('php://input'), true);
$idToken = $data['token'] ?? '';

if (!$idToken) {
    echo json_encode(['success' => false, 'error' => 'Missing token']);
    exit;
}

// 🔹 Verify Firebase ID token
$verifyUrl = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/getAccountInfo?key=AIzaSyDSATkg3AQiM5GNq1a5zDByCRWdqQrUVZk';
$ch = curl_init($verifyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['idToken' => $idToken]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['users'][0])) {
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

$userData = $response['users'][0];
$email = $userData['email'];
$nameParts = explode(' ', $userData['displayName'] ?? '');
$firstname = $nameParts[0] ?? '';
$lastname = $nameParts[1] ?? '';

// 🔹 Check local DB
$stmt = $conn->prepare("SELECT id, firstname, lastname, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$allowedRoles = ['superadmin', 'admin', 'support', 'product_admin']; // roles to skip verification
$skipVerification = false;

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (in_array($user['role'], $allowedRoles, true)) {
        $skipVerification = true;
    }
} else {
    // Auto-create as customer
    $role = "customer";
    $stmtInsert = $conn->prepare("INSERT INTO users (firstname, lastname, email, role) VALUES (?,?,?,?)");
    $stmtInsert->bind_param("ssss", $firstname, $lastname, $email, $role);
    $stmtInsert->execute();
    $user = [
        'id' => $stmtInsert->insert_id,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'role' => $role
    ];
    $stmtInsert->close();
}

// 🔹 Enforce email verification for everyone except allowed roles
if (!$skipVerification && empty($userData['emailVerified'])) {
    echo json_encode(['success' => false, 'error' => 'Email not verified. Please verify your email first.']);
    exit;
}

// 🔹 Start session
$_SESSION['user_logged_in'] = true;
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
$_SESSION['user_role'] = $user['role'];

echo json_encode(['success' => true]);
