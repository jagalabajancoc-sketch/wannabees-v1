<?php
session_start();
require_once __DIR__ . '/../../db.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if ($token === '') {
    http_response_code(400);
    echo '<!DOCTYPE html><html><head><title>Invalid QR Code</title></head><body style="font-family:sans-serif;text-align:center;padding:60px;">
        <h2 style="color:#e74c3c;">&#10060; Invalid QR Code</h2>
        <p>No token provided. Please scan the QR code again.</p>
    </body></html>';
    exit;
}

// Validate token against rental_access and check rental is still active
$stmt = $mysqli->prepare("
    SELECT ra.rental_id, ra.room_id, ra.expires_at, rm.room_number
    FROM rental_access ra
    JOIN rooms rm ON ra.room_id = rm.room_id
    JOIN rentals r ON ra.rental_id = r.rental_id
    WHERE ra.qr_token = ? AND r.ended_at IS NULL
    LIMIT 1
");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
$access = $res->fetch_assoc();
$stmt->close();

if (!$access) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>QR Code Expired</title></head><body style="font-family:sans-serif;text-align:center;padding:60px;">
        <h2 style="color:#e74c3c;">&#10060; QR Code Invalid or Expired</h2>
        <p>This QR code is no longer valid. The rental may have ended, or the code has expired.</p>
        <p style="margin-top:20px;"><a href="../../index.php" style="color:#3498db;">Return to Home</a></p>
    </body></html>';
    exit;
}

// Check expiry
if (strtotime($access['expires_at']) < time()) {
    http_response_code(410);
    echo '<!DOCTYPE html><html><head><title>QR Code Expired</title></head><body style="font-family:sans-serif;text-align:center;padding:60px;">
        <h2 style="color:#e74c3c;">&#10060; QR Code Expired</h2>
        <p>This QR code has expired. Please ask staff to generate a new one.</p>
    </body></html>';
    exit;
}

// Set customer session variables
$_SESSION['customer_rental_id'] = intval($access['rental_id']);
$_SESSION['customer_room_id'] = intval($access['room_id']);
$_SESSION['customer_room_number'] = intval($access['room_number']);
$_SESSION['user_id'] = 'guest_' . $access['rental_id'];
$_SESSION['role_id'] = 4;

header('Location: ../../customer/dashboard.php');
exit;
