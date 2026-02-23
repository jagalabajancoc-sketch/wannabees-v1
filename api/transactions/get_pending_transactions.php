<?php
session_start();
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');

// Only accessible by Owner (1), Staff (2), Cashier (3)
if (!isset($_SESSION['user_id']) || !in_array(intval($_SESSION['role_id']), [1, 2, 3])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$sql = "
SELECT ct.transaction_id, ct.rental_id, ct.room_id, ct.transaction_type,
       ct.reference_id, ct.amount, ct.payment_method,
       ct.gcash_account_name, ct.gcash_reference_number,
       ct.status, ct.created_at,
       rm.room_number
FROM customer_transactions ct
JOIN rooms rm ON ct.room_id = rm.room_id
WHERE ct.status IN ('PENDING_VERIFICATION', 'PENDING_STAFF_COLLECTION')
ORDER BY rm.room_number ASC, ct.created_at ASC
";

$result = $mysqli->query($sql);
$transactions = [];
$byRoom = [];

while ($row = $result->fetch_assoc()) {
    $roomNum = $row['room_number'];
    if (!isset($byRoom[$roomNum])) {
        $byRoom[$roomNum] = [];
    }
    $byRoom[$roomNum][] = $row;
    $transactions[] = $row;
}

echo json_encode([
    'success' => true,
    'transactions' => $transactions,
    'by_room' => $byRoom,
    'total_pending' => count($transactions)
]);
exit;
