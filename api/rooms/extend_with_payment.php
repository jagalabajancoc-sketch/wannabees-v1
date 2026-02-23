<?php
session_start();
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Must be a logged-in customer (role 4) via QR login
if (!isset($_SESSION['customer_rental_id']) || intval($_SESSION['role_id']) !== 4) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$rental_id = intval($_SESSION['customer_rental_id']);
$minutes = isset($_POST['minutes']) ? intval($_POST['minutes']) : 0;
$payment_method = isset($_POST['payment_method']) ? strtoupper(trim($_POST['payment_method'])) : '';
$gcash_account_name = isset($_POST['gcash_account_name']) ? trim($_POST['gcash_account_name']) : null;
$gcash_reference_number = isset($_POST['gcash_reference_number']) ? trim($_POST['gcash_reference_number']) : null;

if ($minutes <= 0 || !in_array($payment_method, ['GCASH', 'CASH'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

if ($payment_method === 'GCASH') {
    if (empty($gcash_account_name) || empty($gcash_reference_number)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'GCash account name and reference number are required']);
        exit;
    }
}

// Fetch rental and room info
$stmt = $mysqli->prepare("SELECT r.rental_id, r.room_id, r.total_minutes FROM rentals r WHERE r.rental_id = ? AND r.ended_at IS NULL LIMIT 1 FOR UPDATE");
$stmt->bind_param('i', $rental_id);
$stmt->execute();
$rental = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$rental) {
    echo json_encode(['success' => false, 'error' => 'Rental not found or already ended']);
    exit;
}
$room_id = intval($rental['room_id']);

// Get room pricing
$stmt = $mysqli->prepare("SELECT price_per_30min, price_per_hour FROM room_types rt JOIN rooms r ON rt.room_type_id = r.room_type_id WHERE r.room_id = ? LIMIT 1");
$stmt->bind_param('i', $room_id);
$stmt->execute();
$rt = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$rt) {
    echo json_encode(['success' => false, 'error' => 'Room type not found']);
    exit;
}
$price30 = floatval($rt['price_per_30min']);
if ($price30 <= 0) $price30 = floatval($rt['price_per_hour']) / 2;
$cost = round($price30 * ($minutes / 30), 2);

$mysqli->begin_transaction();
try {
    $now = date('Y-m-d H:i:s');

    // Create extension record
    $stmt = $mysqli->prepare("INSERT INTO rental_extensions (rental_id, minutes_added, cost, extended_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iids', $rental_id, $minutes, $cost, $now);
    $stmt->execute();
    $extension_id = $stmt->insert_id;
    $stmt->close();

    // Update total_minutes
    $stmt = $mysqli->prepare("UPDATE rentals SET total_minutes = total_minutes + ? WHERE rental_id = ?");
    $stmt->bind_param('ii', $minutes, $rental_id);
    $stmt->execute();
    $stmt->close();

    // Update bill totals
    $stmt = $mysqli->prepare("SELECT bill_id, total_room_cost, total_orders_cost FROM bills WHERE rental_id = ? ORDER BY created_at DESC LIMIT 1 FOR UPDATE");
    $stmt->bind_param('i', $rental_id);
    $stmt->execute();
    $bill = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$bill) throw new Exception('Bill not found');

    $bill_id = intval($bill['bill_id']);
    $new_room = round(floatval($bill['total_room_cost']) + $cost, 2);
    $new_grand = round($new_room + floatval($bill['total_orders_cost']), 2);
    $stmt = $mysqli->prepare("UPDATE bills SET total_room_cost = ?, grand_total = ? WHERE bill_id = ?");
    $stmt->bind_param('ddi', $new_room, $new_grand, $bill_id);
    $stmt->execute();
    $stmt->close();

    // Determine transaction status
    $tx_status = ($payment_method === 'GCASH') ? 'PENDING_VERIFICATION' : 'PENDING_STAFF_COLLECTION';
    $gcash_name = ($payment_method === 'GCASH') ? $gcash_account_name : null;
    $gcash_ref = ($payment_method === 'GCASH') ? $gcash_reference_number : null;

    // Create customer_transaction record
    $stmt = $mysqli->prepare("INSERT INTO customer_transactions (rental_id, room_id, transaction_type, reference_id, amount, payment_method, gcash_account_name, gcash_reference_number, status, created_at) VALUES (?, ?, 'EXTEND_TIME', ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iiidsssss', $rental_id, $room_id, $extension_id, $cost, $payment_method, $gcash_name, $gcash_ref, $tx_status, $now);
    $stmt->execute();
    $transaction_id = $stmt->insert_id;
    $stmt->close();

    $mysqli->commit();

    $message = ($payment_method === 'GCASH')
        ? 'Payment submitted for verification. Staff will come to verify.'
        : 'Cash collection requested. Staff will come to your room.';

    echo json_encode([
        'success' => true,
        'extension_id' => $extension_id,
        'transaction_id' => $transaction_id,
        'cost' => $cost,
        'minutes_added' => $minutes,
        'payment_method' => $payment_method,
        'status' => $tx_status,
        'message' => $message
    ]);
    exit;
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
    exit;
}
