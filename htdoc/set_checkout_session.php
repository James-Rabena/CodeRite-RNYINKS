<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['selected'])) {
    $_SESSION['checkout_items'] = $data['selected'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
