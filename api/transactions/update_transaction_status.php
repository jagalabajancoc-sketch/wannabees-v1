<?php
session_start();
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Only accessible by Owner (1), Staff (2), Cashier (3)
if (!isset($_SESSION['user_id']) || !in_array(intval($_SESSION['role_id']), [1, 2, 3])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$transaction_id = isset($_POST['transaction_id']) ? intval($_POST['transaction_id']) : 0;
$action = isset($_POST['action']) ? strtolower(trim($_POST['action'])) : '';
$staff_id = intval($_SESSION['user_id']);

if ($transaction_id <= 0 || !in_array($action, ['approve', 'reject', 'collected'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Fetch current transaction
$stmt = $mysqli->prepare("SELECT transaction_id, payment_method, status FROM customer_transactions WHERE transaction_id = ? LIMIT 1");
$stmt->bind_param('i', $transaction_id);
$stmt->execute();
$tx = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tx) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Transaction not found']);
    exit;
}

$current_status = $tx['status'];
$payment_method = $tx['payment_method'];
$new_status = null;
$now = date('Y-m-d H:i:s');

// Validate transition
if ($action === 'approve') {
    if ($payment_method !== 'GCASH' || $current_status !== 'PENDING_VERIFICATION') {
        echo json_encode(['success' => false, 'error' => 'Invalid status transition for approve']);
        exit;
    }
    $new_status = 'APPROVED';
} elseif ($action === 'reject') {
    if ($payment_method !== 'GCASH' || $current_status !== 'PENDING_VERIFICATION') {
        echo json_encode(['success' => false, 'error' => 'Invalid status transition for reject']);
        exit;
    }
    $new_status = 'REJECTED';
} elseif ($action === 'collected') {
    if ($payment_method !== 'CASH' || $current_status !== 'PENDING_STAFF_COLLECTION') {
        echo json_encode(['success' => false, 'error' => 'Invalid status transition for collected']);
        exit;
    }
    $new_status = 'COLLECTED';
}

$stmt = $mysqli->prepare("UPDATE customer_transactions SET status = ?, staff_id = ?, verified_at = ? WHERE transaction_id = ?");
$stmt->bind_param('sisi', $new_status, $staff_id, $now, $transaction_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected === 0) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update transaction']);
    exit;
}

echo json_encode([
    'success' => true,
    'transaction_id' => $transaction_id,
    'new_status' => $new_status,
    'action' => $action
]);
exit;
