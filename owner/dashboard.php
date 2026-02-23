<?php
session_start();
require_once __DIR__ . '/../db.php';
if (!isset($_SESSION['user_id']) || intval($_SESSION['role_id']) !== 1) {
    header('Location: ../index.php');
    exit;
}
$ownerName = htmlspecialchars($_SESSION['display_name'] ?: $_SESSION['username']);

// Handle room add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_room') {
        $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
        $room_number = trim($_POST['room_number']);
        $room_type_id = intval($_POST['room_type_id']);

        $dupCount = 0;
        if ($room_id > 0) {
            $chk = $mysqli->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = ? AND room_id != ?");
            $chk->bind_param('si', $room_number, $room_id);
        } else {
            $chk = $mysqli->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = ?");
            $chk->bind_param('s', $room_number);
        }
        $chk->execute();
        $chk->bind_result($dupCount);
        $chk->fetch();
        $chk->close();

        if ($dupCount > 0) {
            $error = 'Room number already exists. Choose a different number.';
            $preserve = [
                'room_id' => $room_id,
                'room_number' => $room_number,
                'room_type_id' => $room_type_id
            ];
        } else {
            if ($room_id > 0) {
                try {
                    $stmt = $mysqli->prepare("UPDATE rooms SET room_number = ?, room_type_id = ? WHERE room_id = ?");
                    $stmt->bind_param('sii', $room_number, $room_type_id, $room_id);
                    $stmt->execute();
                    $stmt->close();
                } catch (mysqli_sql_exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                    $preserve = [
                        'room_id' => $room_id,
                        'room_number' => $room_number,
                        'room_type_id' => $room_type_id
                    ];
                }
            } else {
                try {
                    $stmt = $mysqli->prepare("INSERT INTO rooms (room_number, room_type_id, status) VALUES (?, ?, 'AVAILABLE')");
                    $stmt->bind_param('si', $room_number, $room_type_id);
                    $stmt->execute();
                    $stmt->close();
                } catch (mysqli_sql_exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                    $preserve = [
                        'room_id' => $room_id,
                        'room_number' => $room_number,
                        'room_type_id' => $room_type_id
                    ];
                }
            }
            if (empty($error)) {
                header('Location: dashboard.php');
                exit;
            }
        }
    }
}

$roomTypes = [];
$rtResult = $mysqli->query("SELECT room_type_id, type_name, price_per_hour FROM room_types ORDER BY price_per_hour ASC");
if ($rtResult) {
    while ($rt = $rtResult->fetch_assoc()) $roomTypes[] = $rt;
    $rtResult->free();
}

$sql = "
SELECT
  r.room_id,
  r.room_number,
  r.status,
  rt.room_type_id,
  rt.type_name,
  rt.price_per_hour,
  rt.price_per_30min,
  rent.rental_id,
  rent.started_at,
  rent.total_minutes
FROM rooms r
JOIN room_types rt ON r.room_type_id = rt.room_type_id
LEFT JOIN rentals rent ON rent.room_id = r.room_id AND rent.ended_at IS NULL
ORDER BY rt.price_per_hour ASC, r.room_number ASC
";
$result = $mysqli->query($sql);
$rooms = [];
if ($result) {
    while ($row = $result->fetch_assoc()) $rooms[] = $row;
    $result->free();
}

$cnt = $mysqli->query("SELECT SUM(status='AVAILABLE') AS available, SUM(status='OCCUPIED') AS occupied, SUM(status='CLEANING') AS cleaning FROM rooms")->fetch_assoc();
$available = intval($cnt['available']); 
$occupied = intval($cnt['occupied']); 
$cleaning = intval($cnt['cleaning']);

$byType = [];
foreach ($rooms as $r) {
  $byType[$r['type_name']][] = $r;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Rooms - Wannabees Family KTV</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f8f9fa;
      color: #212529;
      line-height: 1.5;
    }
    header {
      background: #f5f5f5;
      padding: 10px 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      position: sticky;
      top: 0;
      z-index: 100;
      min-height: 60px;
    }
    .header-left {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }
    .header-left img {
      width: 36px;
      height: 36px;
      object-fit: contain;
      display: block;
      margin-right: .1px;
      border-radius: 6px;
    }
    .header-title {
      font-size: 16px;
      font-weight: 600;
      line-height: 1.2;
    }
    .header-subtitle {
      font-size: 12px;
      color: #666;
      display: none;
    }
    .header-nav {
      display: flex;  
      gap: 4px;
      flex-wrap: wrap;
      align-items: center;
    }
    .mobile-nav-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
      color: #333;
    }
    .btn {
      padding: 7px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
      font-weight: 500;
      transition: all 0.2s ease;
      background: white;
      color: #555;
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .btn i { font-size: 10px; }
    .btn:hover {
      background: #f8f8f8;
      border-color: #bbb;
    }
    .btn-primary {
      background: #f2a20a;
      color: white;
      border-color: #f2a20a;
    }
    .btn-primary:hover {
      background: #d89209;
      border-color: #d89209;
    }
    .btn-danger {
      background: white;
      color: #e74c3c;
      border-color: #e74c3c;
    }
    .btn-danger:hover {
      background: #fef5f5;
      border-color: #c0392b;
      color: #c0392b;
    }
    .logout-form {
      display: inline-block;
    }
    .logout-form .btn {
      padding: 6px 10px;
      font-size: 12px;
    }
    main {
      padding: 1.5rem;
      max-width: 1400px;
      margin: 0 auto;
    }
    .page-actions {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 1.5rem;
    }
    .btn-add {
      padding: 0.75rem 1.25rem;
      background: #f5c542;
      color: #2c2c2c;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 600;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
    }
    .btn-add:hover { background: #f2a20a; }
    .summary-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }
    .summary-card {
      background: #ffffff;
      padding: 1.5rem;
      border-radius: 8px;
      border: 1px solid #e9ecef;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
    }
    .summary-label {
      font-size: 0.875rem;
      font-weight: 500;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .summary-value { font-size: 2.5rem; font-weight: 700; color: #212529; }
    .summary-card.available .summary-value { color: #198754; }
    .summary-card.occupied .summary-value { color: #dc3545; }
    .summary-card.cleaning .summary-value { color: #0d6efd; }
    .room-section { margin-bottom: 2rem; }
    .section-title {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #212529;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .rooms-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 1rem;
    }
    .room-card {
      background: #ffffff;
      padding: 1.5rem 1rem;
      border-radius: 8px;
      border: 2px solid #e9ecef;
      cursor: pointer;
      transition: all 0.2s;
      text-align: center;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    .room-card:hover { border-color: #adb5bd; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .room-card.available { border-color: #198754; background: #f8fff9; }
    .room-card.available:hover { background: #eafff0; }
    .room-card.occupied { border-color: #dc3545; background: #fff8f8; }
    .room-card.occupied:hover { background: #fff0f0; }
    .room-card.cleaning { border-color: #0d6efd; background: #f8fbff; }
    .room-card.cleaning:hover { background: #e7f3ff; }
    .room-number { font-size: 1.5rem; font-weight: 700; color: #212529; }
    .room-type { font-size: 0.75rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
    .room-status-text {
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .room-card.available .room-status-text { background: #d1f4e0; color: #0d5c2f; }
    .room-card.occupied .room-status-text { background: #ffd9dd; color: #841f2b; }
    .room-card.cleaning .room-status-text { background: #cfe2ff; color: #084298; }
    .room-time { font-size: 0.875rem; font-weight: 600; color: #dc3545; font-variant-numeric: tabular-nums; }
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
      background: #ffffff;
      border-radius: 8px;
      max-width: 500px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }
    .modal-header { padding: 1.5rem; border-bottom: 1px solid #e9ecef; }
    .modal-title { font-size: 1.25rem; font-weight: 600; color: #212529; margin-bottom: 0.25rem; }
    .modal-subtitle { font-size: 0.875rem; color: #6c757d; }
    .modal-content { padding: 1.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #212529; margin-bottom: 0.5rem; }
    .form-group input, .form-group select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      font-size: 0.875rem;
      transition: all 0.2s;
    }
    .form-group input:focus, .form-group select:focus {
      outline: none;
      border-color: #f5c542;
      box-shadow: 0 0 0 3px rgba(245, 197, 66, 0.2);
    }
    .form-info {
      background: #e7f3ff;
      border-left: 4px solid #0d6efd;
      padding: 0.75rem 1rem;
      border-radius: 6px;
      margin-bottom: 1rem;
      font-size: 0.875rem;
      color: #084298;
    }
    .form-error {
      color: #dc3545;
      background: #f8d7da;
      padding: 0.75rem;
      border-radius: 6px;
      margin-bottom: 1rem;
      font-size: 0.875rem;
    }
    .modal-actions { padding: 1.5rem; border-top: 1px solid #e9ecef; display: flex; gap: 0.75rem; }
    .btn-modal { flex: 1; padding: 0.75rem; border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 600; transition: all 0.2s; }
    .btn-close { background: #f8f9fa; color: #495057; border: 1px solid #dee2e6; }
    .btn-close:hover { background: #e9ecef; }
    .btn-save { background: #f5c542; color: #2c2c2c; font-weight: 600; }
    .btn-save:hover { background: #f2a20a; }
    .bill-section { margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e9ecef; }
    .bill-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .bill-section-title { font-size: 0.875rem; font-weight: 600; color: #212529; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .bill-row { display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.875rem; }
    .bill-label { color: #6c757d; }
    .bill-value { font-weight: 600; color: #212529; }
    .order-item { display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 0.875rem; }
    .grand-total-box { background: #fffbf0; padding: 1rem; border-radius: 6px; border: 2px solid #f5c542; margin-bottom: 1.5rem; }
    .grand-total-row { display: flex; justify-content: space-between; align-items: center; }
    .grand-total-label { font-size: 0.875rem; font-weight: 600; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
    .grand-total-amount { font-size: 1.75rem; font-weight: 700; color: #212529; }
    .view-only-notice { background: #fff3cd; border-left: 4px solid #ffc107; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; color: #856404; }
    @media (max-width: 768px) {
      .header-subtitle { display: block; }
      .header-nav {
        position: fixed;
        top: 60px;
        left: 0;
        right: 0;
        background: white;
        flex-direction: column;
        gap: 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        z-index: 1000;
      }
      .header-nav.active {
        max-height: 400px;
      }
      .mobile-nav-toggle {
        display: block;
      }
      .btn {
        width: 100%;
        justify-content: flex-start;
        border-radius: 0;
        border: none;
        border-bottom: 1px solid #f0f0f0;
      }
      .btn i { font-size: 12px; }
      .header-nav .logout-form {
        width: 100%;
      }
      .header-nav .logout-form .btn {
        width: 100%;
        padding: 10px 12px;
      }
      main { padding: 1rem; }
      .summary-grid { grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
      .summary-card { padding: 1rem; }
      .summary-label { font-size: 0.625rem; }
      .summary-value { font-size: 1.75rem; }
      .rooms-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.75rem; }
      .room-card { padding: 1rem 0.75rem; }
      .room-number { font-size: 1.25rem; }
      .modal-box { max-width: 100%; }
    }
    @media (max-width: 480px) { .summary-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <img src="../assets/images/KTVL.png" alt="Logo" onerror="this.style.display='none'">
      <div>
        <div class="header-title">Wannabees Family KTV</div>
      </div>
    </div>
    <button class="mobile-nav-toggle" onclick="toggleMobileNav()">
      <i class="fas fa-bars"></i>
    </button>
    
    <div class="header-nav" id="headerNav">
      <button class="btn btn-primary" onclick="location.href='dashboard.php'"><i class="fas fa-door-open"></i> <span>Rooms</span></button>
      <button class="btn" onclick="location.href='inventory.php'"><i class="fas fa-box"></i> <span>Inventory</span></button>
      <button class="btn" onclick="location.href='sales_report.php'"><i class="fas fa-dollar-sign"></i> <span>Sales</span></button>
      <button class="btn" onclick="location.href='pricing.php'"><i class="fas fa-tag"></i> <span>Pricing</span></button>
      <button class="btn" onclick="location.href='users.php'"><i class="fas fa-users"></i> <span>Users</span></button>
      <button class="btn" onclick="location.href='guide.php'"><i class="fas fa-book"></i> <span>Guide</span></button>
      <form action="../auth/logout.php" method="post" class="logout-form">
        <button type="button" class="btn btn-danger" onclick="logoutNow(this)"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></button>
      </form>
    </div>
  </header>

  <main>
    <div class="page-actions">
      <button class="btn-add" onclick="openRoomModal()"><i class="fas fa-plus"></i> Add Room</button>
    </div>

    <div class="summary-grid">
      <div class="summary-card available"><div class="summary-label">Available</div><div class="summary-value"><?= $available ?></div></div>
      <div class="summary-card occupied"><div class="summary-label">Occupied</div><div class="summary-value"><?= $occupied ?></div></div>
      <div class="summary-card cleaning"><div class="summary-label">Cleaning</div><div class="summary-value"><?= $cleaning ?></div></div>
    </div>

    <?php foreach ($byType as $typeName => $typeRooms): ?>
      <div class="room-section">
        <div class="section-title"><?= htmlspecialchars($typeName) ?></div>
        <div class="rooms-grid">
          <?php foreach ($typeRooms as $room): ?>
            <div class="room-card <?= strtolower($room['status']) ?>" onclick="handleRoomClick(this)" data-room='<?= json_encode($room, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
              <div class="room-number"><?= htmlspecialchars($room['room_number']) ?></div>
              <div class="room-type"><?= htmlspecialchars($room['type_name']) ?></div>
              <div class="room-status-text"><?= htmlspecialchars($room['status']) ?></div>
              <?php if ($room['status'] === 'OCCUPIED' && $room['started_at']): ?>
                <div class="room-time" data-started="<?= htmlspecialchars($room['started_at']) ?>">00:00:00</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </main>

  <div id="roomModal" class="modal-overlay">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title" id="roomModalTitle">Add Room</div>
        <div class="modal-subtitle">Configure room details</div>
      </div>
      <div class="modal-content">
        <form method="post" id="roomForm">
          <input type="hidden" name="action" value="save_room">
          <input type="hidden" name="room_id" id="room_id" value="<?= isset($preserve['room_id']) ? intval($preserve['room_id']) : '' ?>">
          <?php if (!empty($error)): ?><div class="form-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
          <div class="form-group">
            <label for="room_number">Room Number</label>
            <input type="text" id="room_number" name="room_number" required placeholder="e.g., 101" value="<?= isset($preserve['room_number']) ? htmlspecialchars($preserve['room_number']) : '' ?>">
          </div>
          <div class="form-group">
            <label for="room_type_id">Room Type</label>
            <select id="room_type_id" name="room_type_id" required>
              <option value="">-- Select Room Type --</option>
              <?php foreach ($roomTypes as $rt): ?>
                <option value="<?= $rt['room_type_id'] ?>" <?= (isset($preserve['room_type_id']) && intval($preserve['room_type_id']) === intval($rt['room_type_id'])) ? 'selected' : '' ?>><?= htmlspecialchars($rt['type_name']) ?> (P<?= number_format($rt['price_per_hour'], 0) ?>/hr)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-info" id="statusInfo" style="display: none;"><i class="fas fa-info-circle"></i> Room status is automatically managed by the system based on rental activity.</div>
        </form>
      </div>
      <div class="modal-actions">
        <button class="btn-modal btn-close" onclick="closeRoomModal()">Cancel</button>
        <button class="btn-modal btn-save" onclick="document.getElementById('roomForm').submit()">Save Room</button>
      </div>
    </div>
  </div>

  <div id="billingModal" class="modal-overlay">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title" id="billRoomName">Room</div>
        <div class="modal-subtitle" id="billStartTime">Started:</div>
      </div>
      <div class="modal-content" id="billContent">
        <div class="view-only-notice"><i class="fas fa-eye"></i> View Only - For monitoring purposes</div>
        <p style="text-align:center;color:#6c757d;">Loading...</p>
      </div>
      <div class="modal-actions">
        <button class="btn-modal btn-close" onclick="closeBillingModal()">Close</button>
      </div>
    </div>
  </div>

  <script>
    let currentRoom = null;
    let currentBillData = null;

    function openRoomModal() {
      document.getElementById('roomModalTitle').textContent = 'Add Room';
      document.getElementById('room_id').value = '';
      document.getElementById('room_number').value = '';
      document.getElementById('room_type_id').value = '';
      document.getElementById('statusInfo').style.display = 'none';
      document.getElementById('roomModal').classList.add('active');
    }

    function openEditRoomModal(room) {
      document.getElementById('roomModalTitle').textContent = 'Edit Room';
      document.getElementById('room_id').value = room.room_id;
      document.getElementById('room_number').value = room.room_number;
      document.getElementById('room_type_id').value = room.room_type_id;
      document.getElementById('statusInfo').style.display = 'block';
      document.getElementById('roomModal').classList.add('active');
    }

    function closeRoomModal() { document.getElementById('roomModal').classList.remove('active'); }

    function handleRoomClick(el) {
      const room = JSON.parse(el.dataset.room);
      currentRoom = room;
      if (room.status === 'OCCUPIED') { openBillingModal(); }
      else { if (confirm('Room ' + room.room_number + ' - ' + room.type_name + ' Status: ' + room.status + ' Click OK to edit this room, or Cancel to close.')) { openEditRoomModal(room); } }
    }

    async function openBillingModal() {
      document.getElementById('billRoomName').textContent = 'Room ' + currentRoom.room_number;
      document.getElementById('billStartTime').textContent = 'Started: ' + new Date(currentRoom.started_at).toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true});
      document.getElementById('billingModal').classList.add('active');
      document.getElementById('billContent').innerHTML = '<p style="text-align:center;color:#6c757d;">Loading...</p>';
      try {
        const res = await fetch('../api/billing/get_bill.php?rental_id=' + currentRoom.rental_id);
        const data = await res.json();
        if (data.success) { currentBillData = data; renderBill(data); }
        else { document.getElementById('billContent').innerHTML = '<p style="text-align:center;color:#6c757d;">Error loading bill</p>'; }
      } catch (err) { 
        console.error('Billing error:', err); 
        document.getElementById('billContent').innerHTML = '<p style="text-align:center;color:#6c757d;">Network error: ' + err.message + '</p>'; 
      }
    }

    function renderBill(data) {
      const { bill, rental, orders, extensions } = data;
      let html = '<div class="view-only-notice"><i class="fas fa-eye"></i> View Only - For monitoring purposes</div>';
      html += '<div class="bill-section"><div class="bill-section-title">Room Rental</div>';
      html += '<div class="bill-row"><span class="bill-label">Room Type:</span><span class="bill-value">' + rental.type_name + '</span></div>';
      html += '<div class="bill-row"><span class="bill-label">Duration:</span><span class="bill-value">' + rental.total_minutes + ' minutes</span></div>';
      html += '<div class="bill-row"><span class="bill-label">Room Total:</span><span class="bill-value">P' + parseFloat(bill.total_room_cost).toFixed(2) + '</span></div></div>';
      if (extensions && extensions.length > 0) {
        html += '<div class="bill-section"><div class="bill-section-title">Time Extensions</div>';
        extensions.forEach(ext => { html += '<div class="bill-row"><span class="bill-label">' + ext.minutes_added + ' minutes</span><span class="bill-value">P' + parseFloat(ext.cost).toFixed(2) + '</span></div>'; });
        html += '</div>';
      }
      if (orders && orders.length > 0) {
        html += '<div class="bill-section"><div class="bill-section-title">Orders</div>';
        orders.forEach(item => { const lineTotal = parseFloat(item.price) * parseInt(item.quantity); html += '<div class="order-item"><span>' + item.product_name + ' x' + item.quantity + '</span><span>P' + lineTotal.toFixed(2) + '</span></div>'; });
        html += '<div class="bill-row"><span class="bill-label">Orders Total:</span><span class="bill-value">P' + parseFloat(bill.total_orders_cost).toFixed(2) + '</span></div></div>';
      }
      html += '<div class="grand-total-box"><div class="grand-total-row"><span class="grand-total-label">Grand Total:</span><span class="grand-total-amount">P' + parseFloat(bill.grand_total).toFixed(2) + '</span></div></div>';
      document.getElementById('billContent').innerHTML = html;
    }

    function closeBillingModal() { document.getElementById('billingModal').classList.remove('active'); currentBillData = null; }

    function startAllTimers() {
      document.querySelectorAll('.room-card.occupied .room-time').forEach(timeEl => {
        const startedAt = timeEl.dataset.started;
        if (!startedAt) return;
        function updateTimer() {
          const t = startedAt.split(/[- :]/);
          const startDate = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
          const now = new Date();
          const elapsedSeconds = Math.floor((now - startDate) / 1000);
          const safeElapsed = elapsedSeconds < 0 ? 0 : elapsedSeconds;
          const hours = Math.floor(safeElapsed / 3600);
          const minutes = Math.floor((safeElapsed % 3600) / 60);
          const seconds = safeElapsed % 60;
          timeEl.textContent = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }
        updateTimer();
        setInterval(updateTimer, 1000);
      });
    }

    document.getElementById('roomModal').addEventListener('click', function(e) { if (e.target === this) closeRoomModal(); });
    document.getElementById('billingModal').addEventListener('click', function(e) { if (e.target === this) closeBillingModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closeRoomModal(); closeBillingModal(); } });

    startAllTimers();
    setInterval(() => { location.reload(); }, 30000);

    function logoutNow(el) {
      const form = el && el.closest('form');
      if (navigator.sendBeacon) {
        navigator.sendBeacon('../auth/logout.php');
        setTimeout(() => { window.location.href = '../index.php'; }, 100);
        return;
      }
      if (form) form.submit();
      else {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = '../auth/logout.php';
        document.body.appendChild(f);
        f.submit();
      }
    }

    function toggleMobileNav() {
      document.getElementById('headerNav').classList.toggle('active');
    }

    <?php if (!empty($error)): ?>
    document.addEventListener('DOMContentLoaded', function(){
      document.getElementById('roomModal').classList.add('active');
      const title = document.getElementById('roomModalTitle');
      title.textContent = <?= isset($preserve['room_id']) && $preserve['room_id']>0 ? "'Edit Room'" : "'Add Room'" ?>;
      if (<?= isset($preserve['room_id']) && $preserve['room_id']>0 ? 'true' : 'false' ?>) { document.getElementById('statusInfo').style.display = 'block'; }
    });
    <?php endif; ?>
  </script>
</body>
</html>