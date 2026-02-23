<?php
session_start();
require_once __DIR__ . '/../db.php';
if (!isset($_SESSION['user_id']) || intval($_SESSION['role_id']) !== 1) { 
    header('Location: ../index.php'); 
    exit; 
}
$ownerName = htmlspecialchars($_SESSION['display_name'] ?: $_SESSION['username']);

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_user') {
        $username = trim($_POST['username']);
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
        $display_name = trim($_POST['display_name']);
        $role_id = intval($_POST['role_id']);
        
        $stmt = $mysqli->prepare("INSERT INTO users (username, password, display_name, role_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $username, $password, $display_name, $role_id);
        
        try {
            $stmt->execute();
            $success = "User created successfully!";
        } catch (mysqli_sql_exception $e) {
            $error = "Error: Username already exists or invalid data.";
        }
        $stmt->close();
    }
    
    if ($_POST['action'] === 'update_user') {
        $user_id = intval($_POST['user_id']);
        $username = trim($_POST['username']);
        $display_name = trim($_POST['display_name']);
        $role_id = intval($_POST['role_id']);
        
        // Update user info (NOT password)
        $stmt = $mysqli->prepare("UPDATE users SET username = ?, display_name = ?, role_id = ? WHERE user_id = ?");
        $stmt->bind_param('ssii', $username, $display_name, $role_id, $user_id);
        
        try {
            $stmt->execute();
            $success = "User updated successfully!";
        } catch (mysqli_sql_exception $e) {
            $error = "Error: Username already exists or invalid data.";
        }
        $stmt->close();
    }
    
    if ($_POST['action'] === 'delete_user') {
        $user_id = intval($_POST['user_id']);
        if ($user_id != $_SESSION['user_id']) { // Can't delete yourself
            $stmt = $mysqli->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->close();
            $success = "User deleted successfully!";
        } else {
            $error = "You cannot delete your own account.";
        }
    }
}

// Get all users with their roles
$users_result = $mysqli->query("
    SELECT u.user_id, u.username, u.display_name, u.created_at, r.role_id, r.role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.role_id 
    ORDER BY u.created_at DESC
");
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[] = $row;
}

// Get roles for dropdown (exclude Role 2 - Staff, who no longer use the system)
$roles_result = $mysqli->query("SELECT role_id, role_name FROM roles WHERE role_id != 2 ORDER BY role_id");
$roles = [];
while ($row = $roles_result->fetch_assoc()) {
    $roles[] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Users Management — Wannabees KTV</title>
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
      background: #f0f0f0;
      color: #2c2c2c;
      height: 100vh;
      overflow-x: hidden;
    }
    
    /* Header - Consistent with inventory */
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
    
    .header-actions {
      display: flex;
      gap: 8px;
      align-items: center;
      margin-left: 12px;
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
    
    .btn i {
      font-size: 10px;
    }
    
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
    
    main {
      padding: 15px;
      max-width: 1200px;
      margin: 0 auto;
      height: calc(100vh - 60px);
      overflow-y: auto;
    }
    
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .page-title {
      font-size: 20px;
      font-weight: 700;
      color: #2c2c2c;
    }
    
    .alert {
      padding: 10px 15px;
      border-radius: 6px;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
    }
    
    .alert-success {
      background: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
    }
    
    .alert-error {
      background: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
    }
    
    .users-table-container {
      background: white;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
    }
    
    thead {
      background: #f8f8f8;
    }
    
    th {
      padding: 12px;
      text-align: left;
      font-size: 12px;
      font-weight: 600;
      color: #666;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    td {
      padding: 12px;
      border-top: 1px solid #f0f0f0;
      font-size: 13px;
    }
    
    tbody tr:hover {
      background: #f9f9f9;
    }
    
    .role-badge {
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      display: inline-block;
    }
    
    .role-owner {
      background: #fff3cd;
      color: #856404;
    }
    
    .role-staff {
      background: #d1ecf1;
      color: #0c5460;
    }
    
    .role-cashier {
      background: #d4edda;
      color: #155724;
    }
    
    .role-customer {
      background: #e2e3e5;
      color: #383d41;
    }
    
    /* Modal */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      z-index: 1000;
      overflow-y: auto;
      padding: 20px;
    }
    
    .modal.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .modal-content {
      background: white;
      border-radius: 12px;
      width: 100%;
      max-width: 500px;
      position: relative;
      padding: 24px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      animation: modalSlideIn 0.3s ease-out;
    }
    
    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .modal-close {
      position: absolute;
      top: 16px;
      right: 16px;
      background: none;
      border: none;
      font-size: 28px;
      color: #999;
      cursor: pointer;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: all 0.2s;
    }
    
    .modal-close:hover {
      background: #f0f0f0;
      color: #333;
    }
    
    .modal-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 8px;
      color: #2c2c2c;
    }
    
    .modal-subtitle {
      font-size: 13px;
      color: #666;
      margin-bottom: 20px;
    }
    
    .form-group {
      margin-bottom: 16px;
    }
    
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #555;
      margin-bottom: 6px;
    }
    
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
      transition: all 0.2s;
    }
    
    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #f2a20a;
      box-shadow: 0 0 0 3px rgba(242,162,10,0.1);
    }
    
    .modal-actions {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
      margin-top: 24px;
      padding-top: 16px;
      border-top: 1px solid #eee;
    }
    
    .btn-cancel {
      padding: 10px 20px;
      background: white;
      color: #666;
      border: 1px solid #ddd;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s;
    }
    
    .btn-cancel:hover {
      background: #f5f5f5;
      border-color: #bbb;
    }
    
    .btn-save {
      padding: 10px 20px;
      background: #f2a20a;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    
    .btn-save:hover {
      background: #d89209;
    }
    
    .btn-sm {
      padding: 5px 10px;
      font-size: 11px;
    }
    
    .form-info {
      background: #e3f2fd;
      border-left: 4px solid #2196f3;
      padding: 12px;
      border-radius: 6px;
      margin-bottom: 16px;
      font-size: 13px;
      color: #1976d2;
    }
    
    .form-info i {
      margin-right: 8px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .header-subtitle {
        display: block;
      }
      
      .users-table-container {
        overflow-x: auto;
      }
      
      table {
        min-width: 600px;
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
      
      .btn span {
        display: inline;
      }
      
      .btn i {
        font-size: 12px;
      }
      
      .header-nav .logout-form {
        width: 100%;
      }
      
      .header-nav .logout-form .btn {
        width: 100%;
        padding: 10px 12px;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <img src="../assets/images/KTVL.png" alt="Wannabees KTV" onerror="this.style.display='none'">
      <div>
        <div class="header-title">Wannabees Family KTV</div>
      </div>
    </div>
    <button class="mobile-nav-toggle" onclick="toggleMobileNav()">
      <i class="fas fa-bars"></i>
    </button>
    
    <div class="header-nav" id="headerNav">
      <button class="btn" onclick="location.href='dashboard.php'">
        <i class="fas fa-door-open"></i> <span>Rooms</span>
      </button>
      <button class="btn" onclick="location.href='inventory.php'">
        <i class="fas fa-box"></i> <span>Inventory</span>
      </button>
      <button class="btn" onclick="location.href='sales_report.php'">
        <i class="fas fa-dollar-sign"></i> <span>Sales</span>
      </button>
      <button class="btn" onclick="location.href='pricing.php'">
        <i class="fas fa-tag"></i> <span>Pricing</span>
      </button>
      <button class="btn btn-primary">
        <i class="fas fa-users"></i> <span>Users</span>
      </button>
      <button class="btn" onclick="location.href='guide.php'">
        <i class="fas fa-book"></i> <span>Guide</span>
      </button>
      <form method="post" action="../auth/logout.php" class="logout-form">
        <button type="button" class="btn btn-danger" onclick="logoutNow(this)">
          <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </button>
      </form>
    </div>
  </header>

  <main>
    <div class="page-header">
      <h1 class="page-title">Users Management</h1>
      <button class="btn btn-primary" onclick="openUserModal()">
        <i class="fas fa-plus"></i> Add User
      </button>
    </div>
    
    <?php if (isset($success)): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span><?= htmlspecialchars($success) ?></span>
      </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>
    
    <div class="users-table-container">
      <table>
        <thead>
          <tr>
            <th>Username</th>
            <th>Display Name</th>
            <th>Role</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?= htmlspecialchars($user['username']) ?></td>
              <td><?= htmlspecialchars($user['display_name']) ?></td>
              <td>
                <span class="role-badge role-<?= strtolower($user['role_name']) ?>">
                  <?= htmlspecialchars($user['role_name']) ?>
                </span>
              </td>
              <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
              <td>
                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                  <button class="btn btn-sm" onclick='editUser(<?= json_encode($user) ?>)' style="margin-right: 4px;">
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  </form>
                <?php else: ?>
                  <span style="color: #999; font-size: 11px;">(Current User)</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- User Modal -->
  <div id="userModal" class="modal">
    <div class="modal-content">
      <button class="modal-close" onclick="closeUserModal()">×</button>
      <h3 class="modal-title" id="modalTitle">Add New User</h3>
      <div class="modal-subtitle" id="modalSubtitle">Create a new system user</div>
      
      <form method="post">
        <input type="hidden" name="action" id="formAction" value="create_user">
        <input type="hidden" name="user_id" id="userId" value="">
        
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required placeholder="Login username">
        </div>
        
        <div class="form-group" id="passwordGroup">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="User password">
        </div>
        
        <div class="form-group">
          <label for="display_name">Display Name</label>
          <input type="text" id="display_name" name="display_name" required placeholder="Full name">
        </div>
        
        <div class="form-group">
          <label for="role_id">Role</label>
          <select id="role_id" name="role_id" required>
            <option value="">-- Select Role --</option>
            <?php foreach ($roles as $role): ?>
              <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-info" id="editInfo" style="display: none;">
          <i class="fas fa-info-circle"></i>
          Password cannot be changed here. Users must reset their own passwords.
        </div>
        
        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="closeUserModal()">Cancel</button>
          <button type="submit" class="btn-save">
            <i class="fas fa-save"></i> <span id="submitBtnText">Create User</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openUserModal() {
      // Reset to create mode
      document.getElementById('modalTitle').textContent = 'Add New User';
      document.getElementById('modalSubtitle').textContent = 'Create a new system user';
      document.getElementById('formAction').value = 'create_user';
      document.getElementById('submitBtnText').textContent = 'Create User';
      document.getElementById('userId').value = '';
      document.getElementById('username').value = '';
      document.getElementById('password').value = '';
      document.getElementById('display_name').value = '';
      document.getElementById('role_id').value = '';
      document.getElementById('passwordGroup').style.display = 'block';
      document.getElementById('password').required = true;
      document.getElementById('editInfo').style.display = 'none';
      
      document.getElementById('userModal').classList.add('active');
      document.body.style.overflow = 'hidden';
    }
    
    function editUser(user) {
      // Switch to edit mode
      document.getElementById('modalTitle').textContent = 'Edit User';
      document.getElementById('modalSubtitle').textContent = 'Update user information';
      document.getElementById('formAction').value = 'update_user';
      document.getElementById('submitBtnText').textContent = 'Update User';
      document.getElementById('userId').value = user.user_id;
      document.getElementById('username').value = user.username;
      document.getElementById('display_name').value = user.display_name;
      document.getElementById('role_id').value = user.role_id;
      document.getElementById('passwordGroup').style.display = 'none';
      document.getElementById('password').required = false;
      document.getElementById('editInfo').style.display = 'block';
      
      document.getElementById('userModal').classList.add('active');
      document.body.style.overflow = 'hidden';
    }
    
    function closeUserModal() {
      document.getElementById('userModal').classList.remove('active');
      document.body.style.overflow = 'auto';
    }
    
    document.getElementById('userModal').addEventListener('click', function(e) {
      if (e.target === this) closeUserModal();
    });
    
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeUserModal();
      }
    });
    
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
  </script>
</body>
</html>