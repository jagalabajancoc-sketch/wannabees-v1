<?php
// process_payment.php - process bill payment, end rental, mark room CLEANING, record transactions
require_once __DIR__ . '/../../db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit; }
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'Forbidden']); exit; }

$bill_id = isset($_POST['bill_id']) ? intval($_POST['bill_id']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.0;
$payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'CASH';
$cashier_id = intval($_SESSION['user_id']);

if ($bill_id <= 0 || $amount <= 0) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Invalid request: bill_id and amount are required']); exit; }

$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare("SELECT b.rental_id, b.grand_total, b.is_paid, rm.room_number FROM bills b LEFT JOIN rentals r ON b.rental_id = r.rental_id LEFT JOIN rooms rm ON r.room_id = rm.room_id WHERE b.bill_id = ? LIMIT 1 FOR UPDATE");
    $stmt->bind_param('i', $bill_id);
    $stmt->execute();
    $bill = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$bill) throw new Exception('Bill not found');
    if ($bill['is_paid']) throw new Exception('Bill already paid');

    $grand_total = floatval($bill['grand_total']);
    if ($amount < $grand_total - 0.005) { // 0.005 tolerance to handle floating-point rounding (half-cent)
        throw new Exception('Underpayment: amount paid (₱' . number_format($amount, 2) . ') is less than grand total (₱' . number_format($grand_total, 2) . ')');
    }
    $change_amount = round($amount - $grand_total, 2);
    $rental_id = intval($bill['rental_id']);
    $room_number = $bill['room_number'];

    $now = date('Y-m-d H:i:s');
    $stmt = $mysqli->prepare("INSERT INTO payments (bill_id, amount_paid, payment_method, paid_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('idss', $bill_id, $amount, $payment_method, $now);
    $stmt->execute();
    $payment_id = $stmt->insert_id;
    $stmt->close();

    $date = date('Y-m-d');
    $stmt = $mysqli->prepare("INSERT INTO transactions (bill_id, transaction_date, total_amount) VALUES (?, ?, ?)");
    $stmt->bind_param('isd', $bill_id, $date, $amount);
    $stmt->execute();
    $transaction_id = $stmt->insert_id;
    $stmt->close();

    $stmt = $mysqli->prepare("UPDATE bills SET is_paid = 1 WHERE bill_id = ?");
    $stmt->bind_param('i', $bill_id);
    $stmt->execute();
    $stmt->close();

    $ended_at = date('Y-m-d H:i:s');
    $stmt = $mysqli->prepare("UPDATE rentals SET ended_at = ?, is_active = 0 WHERE rental_id = ?");
    $stmt->bind_param('si', $ended_at, $rental_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT room_id FROM rentals WHERE rental_id = ? LIMIT 1");
    $stmt->bind_param('i', $rental_id);
    $stmt->execute();
    $rrow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($rrow && isset($rrow['room_id'])) {
        $room_id = intval($rrow['room_id']);
        $stmt = $mysqli->prepare("UPDATE rooms SET status = 'CLEANING' WHERE room_id = ?");
        $stmt->bind_param('i', $room_id);
        $stmt->execute();
        $stmt->close();
    }

    $mysqli->commit();
    echo json_encode([
        'success' => true,
        'bill_id' => $bill_id,
        'payment_id' => $payment_id,
        'transaction_id' => $transaction_id,
        'amount_paid' => $amount,
        'grand_total' => $grand_total,
        'change_amount' => $change_amount,
        'room_number' => $room_number
    ]);
    exit;
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    exit;
}