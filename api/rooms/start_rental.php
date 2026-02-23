<?php
session_start();
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    http_response_code(405); 
    echo json_encode(['success'=>false,'error'=>'Method not allowed']); 
    exit; 
}

if (!isset($_SESSION['user_id']) || !in_array(intval($_SESSION['role_id']), [1, 3])) {
    http_response_code(403); 
    echo json_encode(['success'=>false,'error'=>'Forbidden']); 
    exit;
}

$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
$minutes = isset($_POST['minutes']) ? intval($_POST['minutes']) : 0;

if ($room_id <= 0 || $minutes <= 0) { 
    echo json_encode(['success'=>false,'error'=>'Invalid input']); 
    exit; 
}

$stmt = $mysqli->prepare("SELECT r.room_id, r.status, rt.price_per_hour, rt.price_per_30min, r.room_number FROM rooms r JOIN room_types rt ON r.room_type_id = rt.room_type_id WHERE r.room_id = ? LIMIT 1");
$stmt->bind_param('i', $room_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { 
    echo json_encode(['success'=>false,'error'=>'Room not found']); 
    exit; 
}
$row = $res->fetch_assoc();
$stmt->close();

if ($row['status'] !== 'AVAILABLE') { 
    echo json_encode(['success'=>false,'error'=>'Room not available']); 
    exit; 
}

$price30 = floatval($row['price_per_30min']);
if ($price30 <= 0) $price30 = floatval($row['price_per_hour']) / 2;
$roomCost = round($price30 * ($minutes / 30), 2);

// Generate QR token and OTP
$qr_token = bin2hex(random_bytes(32));
$otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

$mysqli->begin_transaction();
try {
    $now = date('Y-m-d H:i:s');

    // Create rental
    $stmt = $mysqli->prepare("INSERT INTO rentals (room_id, started_at, total_minutes, is_active) VALUES (?, ?, ?, 1)");
    $stmt->bind_param('isi', $room_id, $now, $minutes);
    $stmt->execute();
    $rental_id = $stmt->insert_id;
    $stmt->close();

    // Create bill
    $stmt = $mysqli->prepare("INSERT INTO bills (rental_id, total_room_cost, total_orders_cost, grand_total, is_paid, created_at) VALUES (?, ?, 0.00, ?, 0, ?)");
    $grand = $roomCost;
    $stmt->bind_param('idds', $rental_id, $roomCost, $grand, $now);
    $stmt->execute();
    $bill_id = $stmt->insert_id;
    $stmt->close();

    // Create rental access with QR and OTP
    $stmt = $mysqli->prepare("INSERT INTO rental_access (rental_id, room_id, qr_token, otp_code, expires_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iisss', $rental_id, $room_id, $qr_token, $otp_code, $expires_at);
    $stmt->execute();
    $stmt->close();

    // Update room status
    $stmt = $mysqli->prepare("UPDATE rooms SET status = 'OCCUPIED' WHERE room_id = ?");
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();

    // Generate QR code URL pointing to the actual qr_login endpoint
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $qr_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/api/rooms/qr_login.php?token=' . $qr_token;

    echo json_encode([
        'success'=>true,
        'rental_id'=>$rental_id,
        'bill_id'=>$bill_id,
        'room_id'=>$room_id,
        'room_number'=>$row['room_number'],
        'started_at'=>$now,
        'minutes'=>$minutes,
        'total_minutes'=>$minutes,
        'qr_token'=>$qr_token,
        'otp_code'=>$otp_code,
        'qr_url'=>$qr_url,
        'expires_at'=>$expires_at
    ]);
    exit;
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Server error: '.$e->getMessage()]);
    exit;
}