<?php
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || intval($_SESSION['role_id']) !== 3) {
    header('Location: ../index.php');
    exit;
}

$cashierId = intval($_SESSION['user_id']);
$cashierName = htmlspecialchars($_SESSION['display_name'] ?: $_SESSION['username']);

// Handle POST actions (before queries to avoid wasted work on redirect)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_order_status') {
        $order_id = intval($_POST['order_id']);
        $new_status = strtoupper(trim($_POST['status']));
        $valid = ['NEW','PREPARING','READY_TO_DELIVER','DELIVERING','DELIVERED'];
        if ($order_id > 0 && in_array($new_status, $valid)) {
            $stmt = $mysqli->prepare("UPDATE orders SET status = ?, prepared_at = NOW() WHERE order_id = ?");
            $stmt->bind_param('si', $new_status, $order_id);
            $stmt->execute();
            $stmt->close();
            $now = date('Y-m-d H:i:s');
            $meta = json_encode(['to' => $new_status]);
            $stmt = $mysqli->prepare("INSERT INTO order_audit (order_id, action, user_id, role_id, meta, created_at) VALUES (?, 'STATUS_CHANGE', ?, 3, ?, ?)");
            $stmt->bind_param('iiss', $order_id, $cashierId, $meta, $now);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: dashboard.php');
        exit;
    }

    if ($action === 'mark_cleaning') {
        $room_id = intval($_POST['room_id']);
        if ($room_id > 0) {
            $mysqli->begin_transaction();
            try {
                $stmt = $mysqli->prepare("UPDATE rooms SET status = 'CLEANING' WHERE room_id = ? AND status != 'CLEANING'");
                $stmt->bind_param('i', $room_id);
                $stmt->execute();
                $stmt->close();
                $now = date('Y-m-d H:i:s');
                $stmt = $mysqli->prepare("INSERT INTO cleaning_logs (room_id, staff_id, cleaned_at) VALUES (?, ?, ?)");
                $stmt->bind_param('iis', $room_id, $cashierId, $now);
                $stmt->execute();
                $stmt->close();
                $mysqli->commit();
            } catch (Exception $e) {
                $mysqli->rollback();
            }
        }
        header('Location: dashboard.php');
        exit;
    }

    if ($action === 'mark_available') {
        $room_id = intval($_POST['room_id']);
        if ($room_id > 0) {
            $mysqli->begin_transaction();
            try {
                $stmt = $mysqli->prepare("UPDATE rooms SET status = 'AVAILABLE' WHERE room_id = ? AND status = 'CLEANING'");
                $stmt->bind_param('i', $room_id);
                $stmt->execute();
                $stmt->close();
                $now = date('Y-m-d H:i:s');
                $stmt = $mysqli->prepare("INSERT INTO cleaning_logs (room_id, staff_id, cleaned_at) VALUES (?, ?, ?)");
                $stmt->bind_param('iis', $room_id, $cashierId, $now);
                $stmt->execute();
                $stmt->close();
                $mysqli->commit();
            } catch (Exception $e) {
                $mysqli->rollback();
            }
        }
        header('Location: dashboard.php');
        exit;
    }
}

// Get active orders (pending/in-progress)
$activeOrdersSql = "
SELECT o.order_id, o.ordered_at, o.status, o.amount_tendered, o.change_amount,
       rm.room_number,
       GROUP_CONCAT(CONCAT(p.product_name, ' x', oi.quantity) SEPARATOR ', ') as items,
       SUM(oi.price * oi.quantity) as total
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id
JOIN rentals r ON o.rental_id = r.rental_id
JOIN rooms rm ON r.room_id = rm.room_id
WHERE r.ended_at IS NULL AND o.status IN ('NEW','PREPARING','READY_TO_DELIVER','DELIVERING')
GROUP BY o.order_id
ORDER BY o.ordered_at ASC
LIMIT 50";
$activeOrdersResult = $mysqli->query($activeOrdersSql);
$activeOrders = [];
while ($row = $activeOrdersResult->fetch_assoc()) {
    $activeOrders[] = $row;
}

// Get all rooms
$roomsSql = "
SELECT r.room_id, r.room_number, r.status, rt.type_name,
       rent.rental_id, rent.started_at, rent.total_minutes
FROM rooms r
JOIN room_types rt ON r.room_type_id = rt.room_type_id
LEFT JOIN rentals rent ON rent.room_id = r.room_id AND rent.ended_at IS NULL
ORDER BY r.room_number ASC";
$roomsResult = $mysqli->query($roomsSql);
$rooms = [];
while ($row = $roomsResult->fetch_assoc()) {
    $rooms[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard - Wannabees KTV</title>
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
            background: white;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .header-left { display: flex; align-items: center; gap: 1rem; }
        .header-left img { height: 40px; }
        .header-title { font-size: 1.125rem; font-weight: 600; }
        .header-subtitle { font-size: 0.875rem; color: #6c757d; }
.mobile-nav-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: #212529;
        }
        .header-nav { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .nav-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            color: #495057;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .nav-btn:hover { background: #f8f9fa; }
        .nav-btn.active { background: #f5c542; color: #2c2c2c; border-color: #f5c542; }
        .nav-btn.logout { border-color: #dc3545; color: #dc3545; }
        .nav-btn.logout:hover { background: #dc3545; color: white; }
        @media (max-width: 768px) {
            .mobile-nav-toggle {
                display: block;
            }
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
            .nav-btn {
                width: 100%;
                justify-content: flex-start;
                border-radius: 0;
                border: none;
                border-bottom: 1px solid #f0f0f0;
            }
            .nav-btn span {
                display: inline;
            }
            .nav-btn i {
                min-width: 20px;
            }
        }
        main {
            padding: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        .page-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        @media (max-width: 900px) { .page-grid { grid-template-columns: 1fr; } }
        .panel {
            background: white;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            overflow: hidden;
        }
        .panel-header {
            padding: 1rem 1.25rem;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .panel-header i { color: #f5c542; }
        .panel-body { padding: 1rem; }
        .order-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid #f5c542;
        }
        .order-card:last-child { margin-bottom: 0; }
        .order-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
        .order-room { font-weight: 700; font-size: 1rem; }
        .order-status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-NEW { background: #fff3cd; color: #856404; }
        .status-PREPARING { background: #cce5ff; color: #004085; }
        .status-READY_TO_DELIVER { background: #d4edda; color: #155724; }
        .status-DELIVERING { background: #d1ecf1; color: #0c5460; }
        .status-DELIVERED { background: #e2e3e5; color: #383d41; }
        .order-items { font-size: 0.8rem; color: #6c757d; margin-bottom: 0.4rem; }
        .order-money { font-size: 0.8rem; margin-bottom: 0.6rem; }
        .order-money span { font-weight: 600; }
        .order-actions { display: flex; gap: 0.4rem; flex-wrap: wrap; }
        .btn-status {
            padding: 0.35rem 0.75rem;
            border: none;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            background: #f5c542;
            color: #2c2c2c;
            transition: background 0.2s;
        }
        .btn-status:hover { background: #f2a20a; }
        .btn-status.deliver { background: #28a745; color: white; }
        .btn-status.deliver:hover { background: #218838; }
        .empty-state { text-align: center; padding: 2rem; color: #6c757d; }
        .empty-state i { font-size: 2.5rem; margin-bottom: 0.75rem; color: #dee2e6; display: block; }
        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 0.75rem;
        }
        .room-card {
            background: white;
            border-radius: 12px;
            padding: 1rem 0.75rem;
            border: 2px solid #e9ecef;
            text-align: center;
            transition: all 0.2s ease;
            cursor: default;
        }
        .room-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
        .room-card.occupied { border-color: #f5c542; background: linear-gradient(135deg, #fffbf0 0%, #fff8e1 100%); }
        .room-card.cleaning { border-color: #17a2b8; background: linear-gradient(135deg, #f0fbfc 0%, #e0f7fa 100%); }
        .room-card.available { border-color: #28a745; background: linear-gradient(135deg, #f0fff4 0%, #e0f8e9 100%); }
        .room-num { font-size: 1.25rem; font-weight: 700; }
        .room-type { font-size: 0.7rem; color: #6c757d; margin: 0.2rem 0 0.5rem; }
        .room-status-text { font-size: 0.7rem; font-weight: 600; margin-bottom: 0.5rem; }
        .room-card.occupied .room-status-text { color: #f2a20a; }
        .room-card.cleaning .room-status-text { color: #17a2b8; }
        .room-card.available .room-status-text { color: #28a745; }
        .btn-room {
            width: 100%;
            padding: 0.3rem;
            border: none;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.25rem;
        }
        .btn-cleaning { background: #17a2b8; color: white; }
        .btn-cleaning:hover { background: #138496; }
        .btn-available { background: #28a745; color: white; }
        .btn-available:hover { background: #218838; }
        .btn-billing { background: #f5c542; color: #2c2c2c; }
        .btn-billing:hover { background: #f2a20a; }
        .summary-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .summary-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            text-align: center;
        }
        .summary-card .s-label { font-size: 0.7rem; color: #6c757d; text-transform: uppercase; }
        .summary-card .s-value { font-size: 1.75rem; font-weight: 700; }
        .s-new { color: #856404; }
        .s-occupied { color: #f2a20a; }
        .s-cleaning { color: #17a2b8; }
        .s-available { color: #28a745; }
        .meta-info { font-size: 0.75rem; color: #6c757d; margin-top: 0.25rem; }
        .change-amount { color: #28a745; font-weight: 600; }
        /* Toast notifications */
        #toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999; display: flex; flex-direction: column; gap: 0.5rem; }
        .toast { background: #2c2c2c; color: white; padding: 0.75rem 1.25rem; border-radius: 8px; font-size: 0.875rem; box-shadow: 0 4px 12px rgba(0,0,0,0.2); opacity: 0; transform: translateY(10px); transition: all 0.3s ease; max-width: 300px; }
        .toast.show { opacity: 1; transform: translateY(0); }
        .toast.success { border-left: 4px solid #28a745; }
        /* Pulse animation for NEW orders */
        @keyframes pulse-badge { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
        .status-NEW { animation: pulse-badge 1.5s ease-in-out infinite; }
        /* Pending Transactions */
        .tx-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0.75rem; }
        .tx-card { background: #f8f9fa; border-radius: 8px; padding: 1rem; border-left: 4px solid #f5c542; }
        .tx-card.gcash { border-left-color: #007bff; }
        .tx-card.cash  { border-left-color: #28a745; }
        .tx-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
        .tx-room { font-weight: 700; font-size: 0.95rem; }
        .tx-badge { padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .tx-badge.gcash-badge { background: #cce5ff; color: #004085; }
        .tx-badge.cash-badge  { background: #d4edda; color: #155724; }
        .tx-detail { font-size: 0.8rem; color: #6c757d; margin-bottom: 0.5rem; }
        .tx-gcash-info { font-size: 0.8rem; background: #e9f2ff; padding: 0.4rem 0.6rem; border-radius: 6px; margin-bottom: 0.5rem; }
        .tx-actions { display: flex; gap: 0.4rem; flex-wrap: wrap; }
        .btn-tx { padding: 0.35rem 0.8rem; border: none; border-radius: 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer; }
        .btn-approve  { background: #28a745; color: white; }
        .btn-approve:hover  { background: #218838; }
        .btn-reject   { background: #dc3545; color: white; }
        .btn-reject:hover   { background: #c82333; }
        .btn-collect  { background: #17a2b8; color: white; }
        .btn-collect:hover  { background: #138496; }
        .tx-notes-input { width: 100%; margin-top: 0.5rem; padding: 0.35rem 0.5rem; border: 1px solid #dee2e6; border-radius: 4px; font-size: 0.75rem; }
    </style>    <script>
        function toggleMobileNav() {
            const nav = document.getElementById('headerNav');
            if (nav) nav.classList.toggle('active');
        }
        document.addEventListener('click', function(e) {
            const nav = document.getElementById('headerNav');
            const toggle = document.querySelector('.mobile-nav-toggle');
            if (nav && toggle && !nav.contains(e.target) && !toggle.contains(e.target)) {
                nav.classList.remove('active');
            }
        });
    </script></head>
<body>
    <header>
        <div class="header-container">
            <div class="header-left">
                <img src="../assets/images/KTVL.png" alt="Logo" onerror="this.style.display='none'">
                <div>
                    <div class="header-title">Wannabees Family KTV</div>
                </div>
            </div>
            <button class="mobile-nav-toggle" onclick="toggleMobileNav()"><i class="fas fa-bars"></i></button>
            <nav class="header-nav" id="headerNav">
                <a href="dashboard.php" class="nav-btn active"><i class="fas fa-home"></i> <span>Dashboard</span></a>
                <a href="transactions.php" class="nav-btn"><i class="fas fa-history"></i> <span>Transactions</span></a>
                <a href="sales_report.php" class="nav-btn"><i class="fas fa-chart-line"></i> <span>Sales</span></a>
                <a href="guide.php" class="nav-btn"><i class="fas fa-book"></i> <span>Guide</span></a>
                <a href="../auth/logout.php" class="nav-btn logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </nav>
        </div>
    </header>

    <main>
        <?php
        $newOrders = count(array_filter($activeOrders, fn($o) => $o['status'] === 'NEW'));
        $occupied = count(array_filter($rooms, fn($r) => $r['status'] === 'OCCUPIED'));
        $cleaning = count(array_filter($rooms, fn($r) => $r['status'] === 'CLEANING'));
        $available = count(array_filter($rooms, fn($r) => $r['status'] === 'AVAILABLE'));
        ?>
        <div class="summary-bar">
            <div class="summary-card">
                <div class="s-label">New Orders</div>
                <div class="s-value s-new"><?= $newOrders ?></div>
            </div>
            <div class="summary-card">
                <div class="s-label">Active Orders</div>
                <div class="s-value s-occupied"><?= count($activeOrders) ?></div>
            </div>
            <div class="summary-card">
                <div class="s-label">Rooms Occupied</div>
                <div class="s-value s-occupied"><?= $occupied ?></div>
            </div>
            <div class="summary-card">
                <div class="s-label">Rooms Cleaning</div>
                <div class="s-value s-cleaning"><?= $cleaning ?></div>
            </div>
            <div class="summary-card">
                <div class="s-label">Rooms Available</div>
                <div class="s-value s-available"><?= $available ?></div>
            </div>
        </div>

        <div class="page-grid">
            <!-- Active Orders Panel -->
            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-utensils"></i> Active Orders (<?= count($activeOrders) ?>)
                    <span style="margin-left:auto;font-size:0.75rem;font-weight:400;color:#6c757d;">Auto-refreshes every 10s</span>
                </div>
                <div class="panel-body" id="ordersPanel">
                    <?php if (empty($activeOrders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>No active orders right now</p>
                    </div>
                    <?php else: ?>
                        <?php
                        $nextStatus = ['NEW'=>'PREPARING','PREPARING'=>'READY_TO_DELIVER','READY_TO_DELIVER'=>'DELIVERING','DELIVERING'=>'DELIVERED'];
                        $nextLabel = ['NEW'=>'Start Preparing','PREPARING'=>'Ready to Deliver','READY_TO_DELIVER'=>'Delivering','DELIVERING'=>'Mark Delivered'];
                        foreach ($activeOrders as $order): ?>
                        <div class="order-card">
                            <div class="order-top">
                                <span class="order-room"><i class="fas fa-door-open"></i> Room <?= $order['room_number'] ?></span>
                                <span class="order-status-badge status-<?= $order['status'] ?>"><?= str_replace('_', ' ', $order['status']) ?></span>
                            </div>
                            <div class="order-items"><?= htmlspecialchars($order['items']) ?></div>
                            <div class="order-money">
                                Total: <span>₱<?= number_format($order['total'], 2) ?></span>
                                <?php if ($order['amount_tendered'] !== null): ?>
                                &nbsp;|&nbsp; Tendered: <span>₱<?= number_format($order['amount_tendered'], 2) ?></span>
                                &nbsp;|&nbsp; Change: <span style="color:#28a745">₱<?= number_format($order['change_amount'] ?? 0, 2) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="meta-info"><i class="fas fa-clock"></i> <?= date('g:i A', strtotime($order['ordered_at'])) ?> &nbsp; Order #<?= $order['order_id'] ?></div>
                            <?php if (isset($nextStatus[$order['status']])): ?>
                            <div class="order-actions" style="margin-top:0.5rem;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="update_order_status">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <input type="hidden" name="status" value="<?= $nextStatus[$order['status']] ?>">
                                    <button type="submit" class="btn-status <?= $nextStatus[$order['status']] === 'DELIVERED' ? 'deliver' : '' ?>">
                                        <i class="fas fa-arrow-right"></i> <?= $nextLabel[$order['status']] ?>
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rooms Management Panel -->
            <div class="panel">
                <div class="panel-header">
                    <i class="fas fa-door-open"></i> Rooms
                </div>
                <div class="panel-body">
                    <div class="room-grid">
                        <?php foreach ($rooms as $room): ?>
                        <div class="room-card <?= strtolower($room['status']) ?>">
                            <div class="room-num"><?= $room['room_number'] ?></div>
                            <div class="room-type"><?= htmlspecialchars($room['type_name']) ?></div>
                            <div class="room-status-text"><?= $room['status'] ?></div>
                            <?php if ($room['status'] === 'OCCUPIED' && $room['rental_id']): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="mark_cleaning">
                                    <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">
                                    <button type="submit" class="btn-room btn-cleaning" onclick="return confirm('Mark Room <?= $room['room_number'] ?> as CLEANING?')">
                                        <i class="fas fa-broom"></i> Cleaning
                                    </button>
                                </form>
                                <a href="billing.php?rental_id=<?= $room['rental_id'] ?>" class="btn-room btn-billing" style="display:block;text-align:center;text-decoration:none;padding:0.3rem;margin-top:0.25rem;">
                                    <i class="fas fa-file-invoice-dollar"></i> Bill
                                </a>
                            <?php elseif ($room['status'] === 'CLEANING'): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="mark_available">
                                    <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">
                                    <button type="submit" class="btn-room btn-available" onclick="return confirm('Mark Room <?= $room['room_number'] ?> as AVAILABLE?')">
                                        <i class="fas fa-check"></i> Available
                                    </button>
                                </form>
                            <?php elseif ($room['status'] === 'AVAILABLE'): ?>
                                <button class="btn-room btn-available" onclick="startRental(<?= $room['room_id'] ?>, <?= $room['room_number'] ?>)">
                                    <i class="fas fa-play"></i> Start
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Room Payments Panel (full width below) -->
        <div class="panel" style="grid-column:1/-1;">
            <div class="panel-header">
                <i class="fas fa-credit-card"></i> Pending Room Payments
                <span style="margin-left:auto;font-size:0.75rem;font-weight:400;color:#6c757d;">Auto-refreshes every 10s</span>
            </div>
            <div class="panel-body" id="pendingTxPanel">
                <div class="empty-state"><i class="fas fa-check-circle"></i><p>No pending payments</p></div>
            </div>
        </div>
    </div>
    </main>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- QR Code Modal (shown after rental start) -->
    <div id="qrModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1100;align-items:center;justify-content:center;padding:20px;">
        <div style="background:white;border-radius:16px;padding:2rem;max-width:400px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <h3 style="font-size:1.25rem;margin-bottom:0.25rem;"><i class="fas fa-qrcode" style="color:#f5c542;"></i> Room Access QR Code</h3>
            <p style="color:#6c757d;font-size:0.875rem;margin-bottom:1.25rem;">Room <strong id="qrRoomNumber"></strong> — Give this to the customer</p>
            <img id="qrImage" src="" alt="QR Code" style="width:220px;height:220px;border:4px solid #f5c542;border-radius:12px;margin-bottom:1.25rem;">
            <div style="background:#fff9e6;border:2px solid #f5c542;border-radius:10px;padding:1rem;margin-bottom:1.25rem;">
                <div style="font-size:0.8rem;color:#856404;margin-bottom:0.4rem;font-weight:600;">OTP CODE</div>
                <div id="qrOtpCode" style="font-size:2.5rem;font-weight:900;letter-spacing:0.4rem;color:#2c2c2c;font-family:monospace;"></div>
            </div>
            <div style="display:flex;gap:0.75rem;">
                <button onclick="window.print()" style="flex:1;padding:0.6rem;border:1px solid #dee2e6;border-radius:6px;background:white;cursor:pointer;font-size:0.875rem;"><i class="fas fa-print"></i> Print</button>
                <button onclick="closeQrModal()" style="flex:1;padding:0.6rem;border:none;border-radius:6px;background:#f5c542;cursor:pointer;font-size:0.875rem;font-weight:600;"><i class="fas fa-check"></i> Done</button>
            </div>
        </div>
    </div>

    <!-- Start Rental Modal -->
    <div id="startRentalModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;padding:20px;">
        <div style="background:white;border-radius:12px;padding:1.5rem;max-width:360px;width:100%;">
            <h3 style="margin-bottom:1rem;font-size:1.125rem;"><i class="fas fa-play"></i> Start Rental — Room <span id="startRentalRoom"></span></h3>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.875rem;font-weight:600;display:block;margin-bottom:0.5rem;">Duration</label>
                <select id="startRentalMinutes" style="width:100%;padding:0.5rem;border:1px solid #dee2e6;border-radius:6px;font-size:0.875rem;">
                    <option value="30">30 Minutes</option>
                    <option value="60" selected>1 Hour</option>
                    <option value="120">2 Hours</option>
                    <option value="180">3 Hours</option>
                </select>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button onclick="closeStartRentalModal()" style="flex:1;padding:0.6rem;border:1px solid #dee2e6;border-radius:6px;background:white;cursor:pointer;font-size:0.875rem;">Cancel</button>
                <button onclick="confirmStartRental()" style="flex:1;padding:0.6rem;border:none;border-radius:6px;background:#f5c542;cursor:pointer;font-size:0.875rem;font-weight:600;">Start Rental</button>
            </div>
        </div>
    </div>

    <script>
        // Toast notification helper
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `<i class="fas fa-check-circle" style="margin-right:0.5rem;"></i>${message}`;
            container.appendChild(toast);
            requestAnimationFrame(() => { requestAnimationFrame(() => { toast.classList.add('show'); }); });
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // AJAX refresh for orders panel every 10 seconds
        const nextStatusMap = {NEW:'PREPARING',PREPARING:'READY_TO_DELIVER',READY_TO_DELIVER:'DELIVERING',DELIVERING:'DELIVERED'};
        const nextLabelMap = {NEW:'Start Preparing',PREPARING:'Ready to Deliver',READY_TO_DELIVER:'Delivering',DELIVERING:'Mark Delivered'};
        function buildOrdersHtml(orders) {
            if (!orders || orders.length === 0) {
                return '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No active orders right now</p></div>';
            }
            return orders.map(o => {
                const statusLabel = o.status.replace(/_/g,' ');
                const ns = nextStatusMap[o.status];
                const isDeliver = ns === 'DELIVERED';
                const moneyHtml = o.amount_tendered !== null
                    ? ` &nbsp;|&nbsp; Tendered: <span>₱${parseFloat(o.amount_tendered).toFixed(2)}</span> &nbsp;|&nbsp; Change: <span class="change-amount">₱${parseFloat(o.change_amount||0).toFixed(2)}</span>`
                    : '';
                const actionHtml = ns ? `<div class="order-actions" style="margin-top:0.5rem;">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="update_order_status">
                        <input type="hidden" name="order_id" value="${o.order_id}">
                        <input type="hidden" name="status" value="${ns}">
                        <button type="submit" class="btn-status${isDeliver?' deliver':''}">
                            <i class="fas fa-arrow-right"></i> ${nextLabelMap[o.status]}
                        </button>
                    </form></div>` : '';
                // MySQL returns 'YYYY-MM-DD HH:MM:SS'; replace space with 'T' for ISO 8601 compatibility
                const time = new Date(o.ordered_at.replace(' ','T'));
                const timeStr = time.toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit'});
                return `<div class="order-card">
                    <div class="order-top">
                        <span class="order-room"><i class="fas fa-door-open"></i> Room ${o.room_number}</span>
                        <span class="order-status-badge status-${o.status}">${statusLabel}</span>
                    </div>
                    <div class="order-items">${o.items||''}</div>
                    <div class="order-money">Total: <span>₱${parseFloat(o.total).toFixed(2)}</span>${moneyHtml}</div>
                    <div class="meta-info"><i class="fas fa-clock"></i> ${timeStr} &nbsp; Order #${o.order_id}</div>
                    ${actionHtml}
                </div>`;
            }).join('');
        }
        function refreshOrders() {
            fetch('../api/orders/get_pending_orders.php')
                .then(r => r.json())
                .then(data => {
                    if (data && data.orders !== undefined) {
                        const panel = document.getElementById('ordersPanel');
                        if (panel) panel.innerHTML = buildOrdersHtml(data.orders);
                    }
                })
                .catch(() => {});
        }
        setInterval(refreshOrders, 10000);

        let startRentalRoomId = null;

        function startRental(roomId, roomNumber) {
            startRentalRoomId = roomId;
            document.getElementById('startRentalRoom').textContent = roomNumber;
            document.getElementById('startRentalModal').style.display = 'flex';
        }

        function closeStartRentalModal() {
            document.getElementById('startRentalModal').style.display = 'none';
            startRentalRoomId = null;
        }

        async function confirmStartRental() {
            const minutes = parseInt(document.getElementById('startRentalMinutes').value);
            const btn = event.target;
            btn.textContent = 'Starting...';
            btn.disabled = true;
            try {
                const fd = new FormData();
                fd.append('room_id', startRentalRoomId);
                fd.append('minutes', minutes);
                const res = await fetch('../api/rooms/start_rental.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    closeStartRentalModal();
                    showQrModal(data);
                } else {
                    alert('Error: ' + (data.error || 'Could not start rental'));
                    btn.textContent = 'Start Rental';
                    btn.disabled = false;
                }
            } catch (err) {
                alert('Network error: ' + err.message);
                btn.textContent = 'Start Rental';
                btn.disabled = false;
            }
        }

        function showQrModal(data) {
            const qrSize = 250;
            const qrImgUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${qrSize}x${qrSize}&data=${encodeURIComponent(data.qr_url)}`;
            const modal = document.getElementById('qrModal');
            document.getElementById('qrRoomNumber').textContent = data.room_number;
            document.getElementById('qrOtpCode').textContent = data.otp_code;
            document.getElementById('qrImage').src = qrImgUrl;
            modal.style.display = 'flex';
        }

        function closeQrModal() {
            document.getElementById('qrModal').style.display = 'none';
            showToast('Rental started successfully ✓');
            location.reload();
        }

        // Show toast for POST action feedback
        <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['toast'])): ?>
        document.addEventListener('DOMContentLoaded', () => {
            showToast('<?= htmlspecialchars($_GET['toast']) ?> ✓');
        });
        <?php endif; ?>

        // Pending Room Transactions Panel
        function buildPendingTxHtml(transactions) {
            if (!transactions || transactions.length === 0) {
                return '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No pending payments</p></div>';
            }
            const html = '<div class="tx-grid">' + transactions.map(tx => {
                const isGcash = tx.payment_method === 'GCASH';
                const typeLabel = tx.transaction_type === 'ORDER' ? '<i class="fas fa-utensils"></i> Order' : '<i class="fas fa-clock"></i> Extension';
                const gcashInfo = isGcash ? `<div class="tx-gcash-info"><i class="fas fa-mobile-alt"></i> <strong>${escHtml(tx.gcash_account_name||'')}</strong> &nbsp;|&nbsp; Ref: ${escHtml(tx.gcash_reference_number||'')}</div>` : '';
                const actions = isGcash
                    ? `<button class="btn-tx btn-approve" onclick="updateTx(${tx.transaction_id},'approve',this)"><i class="fas fa-check"></i> Approve</button>
                       <button class="btn-tx btn-reject"  onclick="updateTx(${tx.transaction_id},'reject',this)"><i class="fas fa-times"></i> Reject</button>`
                    : `<button class="btn-tx btn-collect" onclick="updateTx(${tx.transaction_id},'mark_collected',this)"><i class="fas fa-hand-holding-usd"></i> Mark Collected</button>`;
                return `<div class="tx-card ${isGcash ? 'gcash' : 'cash'}">
                    <div class="tx-top">
                        <span class="tx-room"><i class="fas fa-door-open"></i> Room ${tx.room_number}</span>
                        <span class="tx-badge ${isGcash ? 'gcash-badge' : 'cash-badge'}">${tx.payment_method}</span>
                    </div>
                    <div class="tx-detail">${typeLabel} &nbsp;•&nbsp; <strong>₱${parseFloat(tx.amount).toFixed(2)}</strong></div>
                    ${gcashInfo}
                    <input class="tx-notes-input" type="text" id="notes_${tx.transaction_id}" placeholder="Cashier notes (optional)">
                    <div class="tx-actions" style="margin-top:0.4rem;">${actions}</div>
                </div>`;
            }).join('') + '</div>';
            return html;
        }

        function escHtml(str) {
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        async function updateTx(txId, action, btn) {
            btn.disabled = true;
            const origText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            const notes = document.getElementById('notes_' + txId);
            const fd = new FormData();
            fd.append('transaction_id', txId);
            fd.append('action', action);
            fd.append('cashier_notes', notes ? notes.value : '');
            try {
                const res = await fetch('../api/cashier/update_transaction.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    showToast('Transaction updated: ' + data.new_status);
                    refreshPendingTx();
                } else {
                    alert('Error: ' + (data.error || 'Unknown'));
                    btn.disabled = false;
                    btn.innerHTML = origText;
                }
            } catch (err) {
                alert('Network error: ' + err.message);
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        }

        function refreshPendingTx() {
            fetch('../api/cashier/get_pending_transactions.php')
                .then(r => r.json())
                .then(data => {
                    if (data && data.transactions !== undefined) {
                        const panel = document.getElementById('pendingTxPanel');
                        if (panel) panel.innerHTML = buildPendingTxHtml(data.transactions);
                    }
                })
                .catch(() => {});
        }

        refreshPendingTx();
        setInterval(refreshPendingTx, 10000);
    </script>
</body>
</html>