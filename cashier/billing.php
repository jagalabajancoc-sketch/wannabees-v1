<?php
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || intval($_SESSION['role_id']) !== 3) {
    header('Location: ../index.php');
    exit;
}

// Get rental_id from URL
if (!isset($_GET['rental_id'])) {
    header('Location: dashboard.php');
    exit;
}

$rental_id = intval($_GET['rental_id']);

// Get rental and bill details
$stmt = $mysqli->prepare("
    SELECT 
        r.rental_id,
        r.room_id,
        r.started_at,
        r.total_minutes,
        rm.room_number,
        rt.type_name,
        rt.price_per_hour,
        rt.price_per_30min,
        b.bill_id,
        b.total_room_cost,
        b.total_orders_cost,
        b.grand_total,
        b.is_paid,
        b.created_at
    FROM rentals r
    JOIN rooms rm ON r.room_id = rm.room_id
    JOIN room_types rt ON rm.room_type_id = rt.room_type_id
    LEFT JOIN bills b ON b.rental_id = r.rental_id
    WHERE r.rental_id = ? AND r.ended_at IS NULL
");
$stmt->bind_param('i', $rental_id);
$stmt->execute();
$rental = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$rental) {
    header('Location: dashboard.php');
    exit;
}

// Get available products
$products = $mysqli->query("SELECT product_id, product_name, price, Category as category FROM products WHERE is_active = 1 ORDER BY Category, product_name")->fetch_all(MYSQLI_ASSOC);

// Get current orders
$stmt = $mysqli->prepare("
    SELECT 
        oi.order_item_id,
        oi.product_id,
        oi.quantity,
        oi.price,
        p.product_name
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE o.rental_id = ?
    ORDER BY oi.order_item_id DESC
");
$stmt->bind_param('i', $rental_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get extensions
$stmt = $mysqli->prepare("
    SELECT extension_id, minutes_added, cost, extended_at
    FROM rental_extensions
    WHERE rental_id = ?
    ORDER BY extended_at ASC
");
$stmt->bind_param('i', $rental_id);
$stmt->execute();
$extensions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$cashierName = htmlspecialchars($_SESSION['display_name'] ?: $_SESSION['username']);
$totalExtensionCost = array_sum(array_column($extensions, 'cost'));
$totalOrdersCost = array_sum(array_map(fn($o) => $o['price'] * $o['quantity'], $orders));
$calculatedGrandTotal = $rental['total_room_cost'] + $totalOrdersCost + $totalExtensionCost;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Billing — Room <?= htmlspecialchars($rental['room_number']) ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f8f9fa;
      color: #212529;
      line-height: 1.5;
    }
    
    header {
      background: #ffffff;
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
    
    .header-left {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .header-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: #212529;
    }
    
    .header-subtitle {
      font-size: 0.875rem;
      color: #6c757d;
    }
    
    .back-link {
      padding: 0.5rem 1rem;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      background: #ffffff;
      color: #495057;
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.2s;
      cursor: pointer;
    }
    
    .back-link:hover {
      background: #f8f9fa;
      border-color: #f5c542;
      color: #f5c542;
    }
    
    main {
      padding: 1.5rem;
      max-width: 1400px;
      margin: 0 auto;
    }
    
    .billing-grid {
      display: grid;
      grid-template-columns: 1fr 400px;
      gap: 1.5rem;
    }
    
    .section-card {
      background: white;
      border-radius: 8px;
      border: 1px solid #e9ecef;
      padding: 1.5rem;
    }
    
    .section-title {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: #212529;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .section-title i {
      color: #f5c542;
    }
    
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 1rem;
    }
    
    .product-card {
      padding: 1rem;
      border: 2px solid #e9ecef;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s;
      text-align: center;
    }
    
    .product-card:hover {
      border-color: #f5c542;
      background: #fffbf0;
      transform: translateY(-2px);
    }
    
    .product-name {
      font-size: 0.875rem;
      font-weight: 600;
      color: #212529;
      margin-bottom: 0.5rem;
    }
    
    .product-price {
      font-size: 1rem;
      font-weight: 700;
      color: #f5c542;
    }
    
    .product-category {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 0.5rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .bill-section {
      margin-bottom: 1.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid #e9ecef;
    }
    
    .bill-section:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    
    .bill-section-title {
      font-size: 0.875rem;
      font-weight: 600;
      color: #212529;
      margin-bottom: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .bill-section-title i {
      color: #f5c542;
      font-size: 0.9rem;
    }
    
    .bill-row {
      display: flex;
      justify-content: space-between;
      padding: 0.5rem 0;
      font-size: 0.875rem;
    }
    
    .bill-label {
      color: #6c757d;
    }
    
    .bill-value {
      font-weight: 600;
      color: #212529;
    }
    
    .order-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem;
      background: #f8f9fa;
      border-radius: 6px;
      margin-bottom: 0.5rem;
      border-left: 3px solid #f5c542;
    }
    
    .order-item-info {
      flex: 1;
    }
    
    .order-item-name {
      font-size: 0.875rem;
      font-weight: 600;
      color: #212529;
    }
    
    .order-item-details {
      font-size: 0.75rem;
      color: #6c757d;
    }
    
    .order-item-actions {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .qty-btn {
      width: 28px;
      height: 28px;
      border: 1px solid #dee2e6;
      background: white;
      border-radius: 4px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.875rem;
      color: #495057;
      transition: all 0.2s;
    }
    
    .qty-btn:hover {
      background: #f5c542;
      border-color: #f5c542;
      color: white;
    }
    
    .qty-display {
      font-size: 0.875rem;
      font-weight: 600;
      min-width: 30px;
      text-align: center;
    }
    
    .remove-btn {
      padding: 0.25rem 0.5rem;
      background: #dc3545;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.75rem;
      transition: all 0.2s;
    }
    
    .remove-btn:hover {
      background: #c82333;
    }
    
    .extension-item {
      display: flex;
      justify-content: space-between;
      padding: 0.5rem 0;
      font-size: 0.875rem;
      border-bottom: 1px solid #eee;
    }
    
    .extension-item:last-child {
      border-bottom: none;
    }
    
    .grand-total-box {
      background: #fffbf0;
      padding: 1rem;
      border-radius: 6px;
      border: 2px solid #f5c542;
      margin-bottom: 1.5rem;
    }
    
    .grand-total-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .grand-total-label {
      font-size: 0.875rem;
      font-weight: 600;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .grand-total-amount {
      font-size: 1.75rem;
      font-weight: 700;
      color: #212529;
    }
    
    .payment-methods {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 0.75rem;
      margin-bottom: 1.5rem;
    }
    
    .payment-btn {
      padding: 0.75rem;
      border: 2px solid #e9ecef;
      border-radius: 6px;
      background: #ffffff;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 600;
      transition: all 0.2s;
      color: #495057;
    }
    
    .payment-btn:hover {
      border-color: #adb5bd;
    }
    
    .payment-btn.selected {
      border-color: #f5c542;
      background: #f5c542;
      color: #2c2c2c;
    }
    
    .action-buttons {
      display: flex;
      gap: 0.75rem;
    }
    
    .btn {
      flex: 1;
      padding: 0.75rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.875rem;
      font-weight: 600;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }
    
    .btn-cancel {
      background: #f8f9fa;
      color: #495057;
      border: 1px solid #dee2e6;
    }
    
    .btn-cancel:hover {
      background: #e9ecef;
    }
    
    .btn-pay {
      background: #f5c542;
      color: #2c2c2c;
    }
    
    .btn-pay:hover {
      background: #f2a20a;
    }
    
    .btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    .empty-state {
      text-align: center;
      padding: 2rem;
      color: #6c757d;
    }
    
    .empty-state i {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      color: #dee2e6;
    }
    
    .adjustment-section {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 6px;
      margin-bottom: 1rem;
    }
    
    .adjustment-row {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
      align-items: center;
    }
    
    .adjustment-row:last-child {
      margin-bottom: 0;
    }
    
    .adjustment-label {
      font-size: 0.75rem;
      color: #6c757d;
      width: 80px;
      text-transform: uppercase;
      font-weight: 600;
    }
    
    .adjustment-input {
      flex: 1;
      padding: 0.4rem;
      border: 1px solid #dee2e6;
      border-radius: 4px;
      font-size: 0.875rem;
    }
    
    .adjustment-input:focus {
      outline: none;
      border-color: #f5c542;
      box-shadow: 0 0 0 3px rgba(245, 197, 66, 0.1);
    }
    
    .notes-section {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 6px;
      margin-bottom: 1rem;
    }
    
    .notes-label {
      font-size: 0.75rem;
      color: #6c757d;
      text-transform: uppercase;
      font-weight: 600;
      margin-bottom: 0.5rem;
      display: block;
    }
    
    .notes-textarea {
      width: 100%;
      padding: 0.6rem;
      border: 1px solid #dee2e6;
      border-radius: 4px;
      font-size: 0.85rem;
      resize: vertical;
      min-height: 60px;
      font-family: inherit;
    }
    
    .notes-textarea:focus {
      outline: none;
      border-color: #f5c542;
      box-shadow: 0 0 0 3px rgba(245, 197, 66, 0.1);
    }
    
    .quick-actions {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
    }
    
    .extension-quick-btn {
      flex: 1;
      padding: 0.6rem;
      background: white;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.75rem;
      font-weight: 600;
      transition: all 0.2s;
      color: #495057;
      text-align: center;
    }
    
    .extension-quick-btn:hover {
      border-color: #f5c542;
      background: #fffbf0;
    }
    
    .print-btn {
      width: 100%;
      padding: 0.5rem;
      background: white;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
      transition: all 0.2s;
      color: #495057;
    }
    
    .print-btn:hover {
      background: #f8f9fa;
      border-color: #f5c542;
    }
    
    @media print {
      header, .quick-actions, .action-buttons, .print-btn, .back-link {
        display: none;
      }
      .billing-grid {
        grid-template-columns: 1fr;
      }
      .section-card {
        page-break-inside: avoid;
      }
    }
    
    @media (max-width: 1024px) {
      .billing-grid {
        grid-template-columns: 1fr;
      }
      
      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      }
    }
    
    @media (max-width: 768px) {
      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
      }
      
      .payment-methods {
        grid-template-columns: 1fr;
      }
      
      .adjustment-row {
        flex-direction: column;
      }
      
      .adjustment-label {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="header-container">
      <div class="header-left">
        <div>
          <div class="header-title">Room <?= htmlspecialchars($rental['room_number']) ?> — <?= htmlspecialchars($rental['type_name']) ?></div>
          <div class="header-subtitle">Started: <?= date('h:i A', strtotime($rental['started_at'])) ?> • Cashier: <?= $cashierName ?></div>
        </div>
      </div>
      <span class="back-link" onclick="goBackToDashboard()" style="cursor: pointer;">
        <i class="fas fa-arrow-left"></i>
        Back to Dashboard
      </span>
    </div>
  </header>

  <main>
    <div class="billing-grid">
      <!-- Products Section -->
      <div class="section-card">
        <h2 class="section-title">
          <i class="fas fa-shopping-cart"></i>
          Add Items
        </h2>
        
        <?php if (count($products) > 0): ?>
          <div class="products-grid">
            <?php foreach ($products as $p): ?>
              <div class="product-card" onclick="addProduct(<?= $p['product_id'] ?>, '<?= htmlspecialchars($p['product_name']) ?>', <?= $p['price'] ?>)" title="<?= htmlspecialchars($p['product_name']) ?>">
                <div class="product-category"><?= htmlspecialchars($p['category']) ?></div>
                <div class="product-name"><?= htmlspecialchars($p['product_name']) ?></div>
                <div class="product-price">₱<?= number_format($p['price'], 2) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <p>No products available</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Bill Summary Section -->
      <div class="section-card">
        <h2 class="section-title">
          <i class="fas fa-receipt"></i>
          Bill Summary
        </h2>
        
        <!-- Room Rental -->
        <div class="bill-section">
          <div class="bill-section-title">
            <i class="fas fa-door-open"></i>
            Room Rental
          </div>
          <div class="bill-row">
            <span class="bill-label">Duration:</span>
            <span class="bill-value"><?= $rental['total_minutes'] ?> minutes</span>
          </div>
          <div class="bill-row">
            <span class="bill-label">Rate:</span>
            <span class="bill-value">₱<?= number_format($rental['price_per_hour'], 2) ?>/hr</span>
          </div>
          <div class="bill-row">
            <span class="bill-label">Room Total:</span>
            <span class="bill-value">₱<?= number_format($rental['total_room_cost'], 2) ?></span>
          </div>
        </div>

        <!-- Time Extensions -->
        <?php if (count($extensions) > 0): ?>
        <div class="bill-section">
          <div class="bill-section-title">
            <i class="fas fa-plus-circle"></i>
            Time Extensions
          </div>
          <?php foreach ($extensions as $ext): ?>
          <div class="extension-item">
            <span><?= $ext['minutes_added'] ?> min</span>
            <span class="bill-value">₱<?= number_format($ext['cost'], 2) ?></span>
          </div>
          <?php endforeach; ?>
          <div class="bill-row" style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid #eee; font-weight: 600;">
            <span>Extension Total:</span>
            <span class="bill-value">₱<?= number_format($totalExtensionCost, 2) ?></span>
          </div>
        </div>
        <?php endif; ?>

        <!-- Orders -->
        <div class="bill-section">
          <div class="bill-section-title">
            <i class="fas fa-utensils"></i>
            Orders (<?= count($orders) ?>)
          </div>
          <div id="ordersList">
            <?php if (count($orders) > 0): ?>
              <?php foreach ($orders as $order): ?>
              <div class="order-item" data-order-id="<?= $order['order_item_id'] ?>">
                <div class="order-item-info">
                  <div class="order-item-name"><?= htmlspecialchars($order['product_name']) ?></div>
                  <div class="order-item-details">₱<?= number_format($order['price'], 2) ?> × <?= $order['quantity'] ?> = ₱<?= number_format($order['price'] * $order['quantity'], 2) ?></div>
                </div>
                <div class="order-item-actions">
                  <button class="qty-btn" onclick="updateQuantity(<?= $order['order_item_id'] ?>, -1)" title="Decrease">−</button>
                  <span class="qty-display"><?= $order['quantity'] ?></span>
                  <button class="qty-btn" onclick="updateQuantity(<?= $order['order_item_id'] ?>, 1)" title="Increase">+</button>
                  <button class="remove-btn" onclick="removeOrder(<?= $order['order_item_id'] ?>)" title="Remove">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <p>No items ordered</p>
              </div>
            <?php endif; ?>
          </div>
          <?php if (count($orders) > 0): ?>
          <div class="bill-row" style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid #eee; font-weight: 600;">
            <span>Orders Total:</span>
            <span class="bill-value">₱<span id="ordersTotalDisplay"><?= number_format($totalOrdersCost, 2) ?></span></span>
          </div>
          <?php endif; ?>
        </div>

        <!-- Quick Extension Buttons -->
        <div class="quick-actions">
          <button class="extension-quick-btn" onclick="quickExtension(30)" title="Add 30 minutes">
            <i class="fas fa-hourglass-end"></i> +30min
          </button>
          <button class="extension-quick-btn" onclick="quickExtension(60)" title="Add 60 minutes">
            <i class="fas fa-hourglass-end"></i> +1hr
          </button>
        </div>

        <!-- Adjustments & Discount -->
        <div class="adjustment-section">
          <div class="adjustment-row">
            <span class="adjustment-label">Discount:</span>
            <input type="number" id="discountAmount" class="adjustment-input" placeholder="0.00" min="0" step="0.01" onchange="updateGrandTotal()">
          </div>
          <div class="adjustment-row">
            <span class="adjustment-label">Add Charge:</span>
            <input type="number" id="additionalCharge" class="adjustment-input" placeholder="0.00" min="0" step="0.01" onchange="updateGrandTotal()">
          </div>
        </div>

        <!-- Notes -->
        <div class="notes-section">
          <label class="notes-label">
            <i class="fas fa-sticky-note"></i>
            Additional Notes
          </label>
          <textarea id="billNotes" class="notes-textarea" placeholder="Add any special notes for this transaction..."></textarea>
        </div>

        <!-- Grand Total -->
        <div class="grand-total-box">
          <div class="grand-total-row">
            <span class="grand-total-label">Grand Total:</span>
            <span class="grand-total-amount" id="grandTotal">₱<?= number_format($calculatedGrandTotal, 2) ?></span>
          </div>
        </div>

        <!-- Print Button -->
        <button class="print-btn" onclick="window.print()">
          <i class="fas fa-print"></i>
          Print Receipt
        </button>

        <!-- Payment Method -->
        <div class="bill-section" style="margin-top: 1rem; margin-bottom: 1rem;">
          <div class="bill-section-title">
            <i class="fas fa-credit-card"></i>
            Payment Method
          </div>
          <div class="payment-methods">
            <button class="payment-btn selected" data-method="CASH" onclick="selectPayment(this)">
              <i class="fas fa-money-bill-wave"></i> CASH
            </button>
            <button class="payment-btn" data-method="GCASH" onclick="selectPayment(this)">
              <i class="fas fa-mobile-alt"></i> GCASH
            </button>
          </div>
        </div>

        <!-- Amount Tendered / Change -->
        <div class="adjustment-section" style="margin-bottom:1rem;">
          <div class="adjustment-row">
            <span class="adjustment-label">Tendered:</span>
            <input type="number" id="amountTendered" class="adjustment-input" placeholder="0.00" min="0" step="0.01" oninput="updateChange()">
          </div>
          <div class="adjustment-row" id="changeRow" style="display:none;">
            <span class="adjustment-label" style="color:#28a745;font-weight:700;">Change:</span>
            <span id="changeAmount" style="font-size:1.125rem;font-weight:700;color:#28a745;"></span>
          </div>
          <div class="adjustment-row" id="underpayRow" style="display:none;">
            <span style="color:#dc3545;font-size:0.8rem;font-weight:600;"><i class="fas fa-exclamation-triangle"></i> Amount is less than total</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <button class="btn btn-cancel" onclick="goBackToDashboard()">
            <i class="fas fa-times"></i>
            Cancel
          </button>
          <button class="btn btn-pay" onclick="processPayment()">
            <i class="fas fa-check"></i>
            Process Payment
          </button>
        </div>
      </div>
    </div>
  </main>

  <script>
    const rentalId = <?= $rental_id ?>;
    let selectedPaymentMethod = 'CASH';

    function goBackToDashboard() {
      // Use simple redirect without confirmation to avoid redirect loop
      window.location.href = 'dashboard.php';
    }

    function getCurrentTotal() {
      const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
      const additionalCharge = parseFloat(document.getElementById('additionalCharge').value) || 0;
      const baseTotal = <?= $calculatedGrandTotal ?>;
      return baseTotal - discount + additionalCharge;
    }

    function updateGrandTotal() {
      const newTotal = getCurrentTotal();
      document.getElementById('grandTotal').textContent = '₱' + newTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      updateChange();
    }

    function updateChange() {
      const tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
      const total = getCurrentTotal();
      const changeRow = document.getElementById('changeRow');
      const underpayRow = document.getElementById('underpayRow');
      if (tendered <= 0) { changeRow.style.display='none'; underpayRow.style.display='none'; return; }
      const change = tendered - total;
      if (change >= 0) {
        document.getElementById('changeAmount').textContent = '₱' + change.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
        changeRow.style.display='flex';
        underpayRow.style.display='none';
      } else {
        changeRow.style.display='none';
        underpayRow.style.display='flex';
      }
    }

    function quickExtension(minutes) {
      if (confirm(`Add ${minutes} minutes to this rental?`)) {
        alert(`Extension feature would add ${minutes} minutes. Implement extension API.`);
      }
    }

    async function addProduct(productId, productName, price) {
      try {
        const formData = new FormData();
        formData.append('rental_id', rentalId);
        formData.append('product_id', productId);
        formData.append('quantity', 1);
        formData.append('price', price);

        const res = await fetch('../api/orders/add_order.php', {
          method: 'POST',
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          location.reload();
        } else {
          alert('Error: ' + (data.error || 'Unable to add item'));
        }
      } catch (err) {
        alert('Network error: ' + err.message);
      }
    }

    async function updateQuantity(orderItemId, change) {
      try {
        const formData = new FormData();
        formData.append('order_item_id', orderItemId);
        formData.append('change', change);

        const res = await fetch('update_order_quantity.php', {
          method: 'POST',
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          location.reload();
        } else {
          alert('Error: ' + (data.error || 'Unable to update quantity'));
        }
      } catch (err) {
        alert('Network error: ' + err.message);
      }
    }

    async function removeOrder(orderItemId) {
      if (!confirm('Remove this item?')) return;

      try {
        const formData = new FormData();
        formData.append('order_item_id', orderItemId);

        const res = await fetch('remove_order.php', {
          method: 'POST',
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          location.reload();
        } else {
          alert('Error: ' + (data.error || 'Unable to remove item'));
        }
      } catch (err) {
        alert('Network error: ' + err.message);
      }
    }

    function selectPayment(btn) {
      document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('selected'));
      btn.classList.add('selected');
      selectedPaymentMethod = btn.dataset.method;
    }

    async function processPayment() {
      const finalAmount = getCurrentTotal();
      const tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
      if (tendered > 0 && tendered < finalAmount - 0.005) { // 0.005 tolerance for floating-point rounding (half-cent)
        alert('Amount tendered (₱' + tendered.toFixed(2) + ') is less than the grand total (₱' + finalAmount.toFixed(2) + '). Please collect the correct amount.');
        return;
      }
      if (!confirm('Process payment and end rental?')) return;

      const btn = event.target.closest('.btn-pay');
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
      btn.disabled = true;

      try {
        const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
        const additionalCharge = parseFloat(document.getElementById('additionalCharge').value) || 0;
        const notes = document.getElementById('billNotes').value;
        const amountToSend = tendered > 0 ? tendered : finalAmount;

        const formData = new FormData();
        formData.append('bill_id', <?= $rental['bill_id'] ?>);
        formData.append('amount', amountToSend);
        formData.append('payment_method', selectedPaymentMethod);
        formData.append('discount', discount);
        formData.append('additional_charge', additionalCharge);
        formData.append('notes', notes);

        const res = await fetch('../api/billing/process_payment.php', {
          method: 'POST',
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          const changeMsg = data.change_amount > 0 ? ` Change: ₱${data.change_amount.toFixed(2)}` : '';
          alert('Payment processed successfully!' + changeMsg);
          window.location.href = 'dashboard.php';
        } else {
          alert('Error: ' + (data.error || 'Unknown error occurred'));
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      } catch (err) {
        alert('Network error: ' + err.message);
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
    }
  </script>
</body>
</html>