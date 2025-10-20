<?php
session_start();
/*
  usersettings.php
  Admin Settings page (Profile, Categories, Preferences, Security)
  - Assumes a simple admin auth via $_SESSION['is_admin'] (boolean) and $_SESSION['admin_id'].
  - Adjust session/auth logic to match your application.
  - DB tables assumed (change to match your schema):
      admins (id, name, email, role, password_hash)
      categories (id, name, description, created_at)
      settings (id, currency, date_format, week_start, alerts_enabled)
*/

// --- Simple admin check ---
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: admin/login.php");
    exit;
}

// --- DB Connection (change credentials) ---
$host = "localhost";
$user = "root";
$pass = "";
$db = "budget_db";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_errno) {
    die("DB connection failed: " . $conn->connect_error);
}

// --- Helpers ---
function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// --- Handle POST actions ---
// Update admin profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'admin');
        $admin_id = intval($_SESSION['admin_id']);

        $stmt = $conn->prepare("UPDATE admins SET name=?, email=?, role=? WHERE id=?");
        $stmt->bind_param('sssi', $name, $email, $role, $admin_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['flash'] = "Profile updated.";
        header("Location: usersettings.php#profile");
        exit;
    }

    // Change password
    if ($action === 'change_password') {
        $admin_id = intval($_SESSION['admin_id']);
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new !== $confirm) {
            $_SESSION['flash_error'] = "New password and confirmation do not match.";
            header("Location: usersettings.php#security");
            exit;
        }

        // fetch current hash
        $stmt = $conn->prepare("SELECT password_hash FROM admins WHERE id=?");
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $stmt->bind_result($hash);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current, $hash)) {
            $_SESSION['flash_error'] = "Current password is incorrect.";
            header("Location: usersettings.php#security");
            exit;
        }
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET password_hash=? WHERE id=?");
        $stmt->bind_param('si', $new_hash, $admin_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['flash'] = "Password changed successfully.";
        header("Location: usersettings.php#security");
        exit;
    }

    // Add category
    if ($action === 'add_category') {
        $name = trim($_POST['cat_name'] ?? '');
        $desc = trim($_POST['cat_desc'] ?? '');
        if ($name !== '') {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param('ss', $name, $desc);
            $stmt->execute();
            $stmt->close();
            $_SESSION['flash'] = "Category added.";
        } else {
            $_SESSION['flash_error'] = "Category name required.";
        }
        header("Location: usersettings.php#categories");
        exit;
    }

    // Edit category
    if ($action === 'edit_category') {
        $id = intval($_POST['cat_id']);
        $name = trim($_POST['cat_name'] ?? '');
        $desc = trim($_POST['cat_desc'] ?? '');
        if ($name !== '') {
            $stmt = $conn->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
            $stmt->bind_param('ssi', $name, $desc, $id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['flash'] = "Category updated.";
        } else {
            $_SESSION['flash_error'] = "Category name required.";
        }
        header("Location: usersettings.php#categories");
        exit;
    }

    // Delete category
    if ($action === 'delete_category') {
        $id = intval($_POST['cat_id']);
        $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash'] = "Category deleted.";
        header("Location: usersettings.php#categories");
        exit;
    }

    // Update preferences (settings table single row assumed id=1)
    if ($action === 'update_prefs') {
        $currency = trim($_POST['currency'] ?? 'EGP');
        $date_format = trim($_POST['date_format'] ?? 'Y-m-d');
        $week_start = trim($_POST['week_start'] ?? 'Monday');
        $alerts = isset($_POST['alerts']) ? 1 : 0;

        // upsert into settings (simple approach)
        $stmt = $conn->prepare("SELECT id FROM settings LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($sid);
        $exists = $stmt->fetch();
        $stmt->close();

        if ($exists) {
            $stmt = $conn->prepare("UPDATE settings SET currency=?, date_format=?, week_start=?, alerts_enabled=? WHERE id=?");
            $id = intval($sid);
            $stmt->bind_param('sssii', $currency, $date_format, $week_start, $alerts, $id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO settings (currency, date_format, week_start, alerts_enabled) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('sssi', $currency, $date_format, $week_start, $alerts);
            $stmt->execute();
            $stmt->close();
        }

        $_SESSION['flash'] = "Preferences saved.";
        header("Location: usersettings.php#preferences");
        exit;
    }
}

// --- Fetch data for display ---
// Admin profile
$admin_id = intval($_SESSION['admin_id']);
$admin = ['name'=>'Admin','email'=>'admin@example.com','role'=>'admin'];
$stmt = $conn->prepare("SELECT id, name, email, role FROM admins WHERE id=? LIMIT 1");
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$stmt->bind_result($aid, $aname, $aemail, $arole);
if ($stmt->fetch()) {
    $admin = ['id'=>$aid, 'name'=>$aname, 'email'=>$aemail, 'role'=>$arole];
}
$stmt->close();

// Categories
$categories = [];
$res = $conn->query("SELECT id, name, description, created_at FROM categories ORDER BY name ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) $categories[] = $r;
    $res->free();
}

// Settings
$settings = ['currency'=>'EGP', 'date_format'=>'Y-m-d', 'week_start'=>'Monday', 'alerts_enabled'=>1];
$res = $conn->query("SELECT currency, date_format, week_start, alerts_enabled FROM settings LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $settings = $row;
    $settings['alerts_enabled'] = intval($settings['alerts_enabled']);
}

// flash messages
$flash = $_SESSION['flash'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Settings</title>
  <link rel="stylesheet" href="css/style1.css" />
  <style>
    /* minor local tweaks for admin layout */
    .admin-wrap { max-width:1200px; margin: 28px auto; padding: 18px; }
    .tabs { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
    .tab { padding:10px 14px; border-radius:10px; background:linear-gradient(180deg, rgba(255,255,255,0.9), rgba(255,255,255,0.8)); border:1px solid var(--glass-border); cursor:pointer; font-weight:700; color:var(--support-1); }
    .tab.active { background: linear-gradient(180deg,var(--action-blue), #2b86c7); color:#fff; box-shadow:0 12px 28px rgba(52,152,219,0.12); }
    .panel { background: var(--card-bg); border-radius:12px; padding:18px; box-shadow: var(--shadow); border:1px solid var(--glass-border); }
    .two-col { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
    @media (max-width:800px){ .two-col{grid-template-columns:1fr} }
    .table { width:100%; border-collapse:collapse; }
    .table th, .table td { padding:10px; border-bottom:1px solid #eee; text-align:left; }
    .muted { color:var(--muted); font-size:0.92rem; }
    .small-btn { padding:6px 8px; border-radius:8px; font-weight:700; border:1px solid rgba(0,0,0,0.06); background:#fff; }
    .danger { color:var(--expense-red); }
    .success { color:var(--income-green); }
    .flash { padding:10px 12px; border-radius:8px; margin-bottom:12px; }
    .flash.ok { background: #e8f8f0; color: #0b6f44; border:1px solid rgba(46,204,113,0.12); }
    .flash.err { background: #fff0f0; color: #b00020; border:1px solid rgba(231,76,60,0.12); }
  </style>
</head>
<body>
  <div class="admin-wrap">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;">
      <div>
        <div style="display:flex;gap:12px;align-items:center;">
          <div class="brand-badge">⚙️</div>
          <div>
            <div class="brand-title" style="font-size:1.1rem;">Admin Settings</div>
            <div class="muted">Manage system preferences, categories and your admin profile</div>
          </div>
        </div>
      </div>
      <div>
        <a href="admin/dashboard.php" class="small-btn">Return to Dashboard</a>
        <a href="admin/logout.php" class="small-btn">Logout</a>
      </div>
    </div>

    <?php if ($flash): ?>
      <div class="flash ok"><?php echo esc($flash); ?></div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
      <div class="flash err"><?php echo esc($flash_error); ?></div>
    <?php endif; ?>

    <div class="tabs" role="tablist" aria-label="Admin settings tabs">
      <div class="tab active" data-target="profile">Profile</div>
      <div class="tab" data-target="categories">Categories</div>
      <div class="tab" data-target="preferences">Preferences</div>
      <div class="tab" data-target="security">Security</div>
    </div>

    <!-- PROFILE -->
    <div id="profile" class="panel tab-panel" role="tabpanel">
      <h3>Profile</h3>
      <div class="two-col" style="align-items:start">
        <div>
          <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="<?php echo esc($admin['name']); ?>" required />
            <label class="form-label" style="margin-top:8px;">Email</label>
            <input class="form-control" name="email" value="<?php echo esc($admin['email']); ?>" required />
            <label class="form-label" style="margin-top:8px;">Role</label>
            <select class="form-control" name="role">
              <option value="admin" <?php echo $admin['role']==='admin' ? 'selected':''; ?>>Admin</option>
              <option value="manager" <?php echo $admin['role']==='manager' ? 'selected':''; ?>>Manager</option>
            </select>
            <div style="margin-top:12px;">
              <button type="submit" class="btn btn-primary">Save Profile</button>
            </div>
          </form>
        </div>

        <div>
          <h4>Admin Info</h4>
          <p class="muted">ID: <?php echo esc($admin['id']); ?></p>
          <p class="muted">Email: <?php echo esc($admin['email']); ?></p>
          <p class="muted">Role: <?php echo esc($admin['role']); ?></p>
        </div>
      </div>
    </div>

    <!-- CATEGORIES -->
    <div id="categories" class="panel tab-panel hidden" role="tabpanel">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h3>Categories</h3>
        <div>
          <button id="openAddCat" class="small-btn">+ New Category</button>
        </div>
      </div>

      <table class="table">
        <thead>
          <tr><th>Name</th><th>Description</th><th>Created</th><th style="width:160px">Actions</th></tr>
        </thead>
        <tbody>
          <?php if (count($categories)===0): ?>
            <tr><td colspan="4" class="muted">No categories yet.</td></tr>
          <?php else: foreach ($categories as $c): ?>
            <tr>
              <td><?php echo esc($c['name']); ?></td>
              <td><?php echo esc($c['description']); ?></td>
              <td class="muted"><?php echo esc($c['created_at']); ?></td>
              <td>
                <button class="small-btn" onclick='openEditCat(<?php echo intval($c['id']); ?>, <?php echo json_encode(esc($c['name'])); ?>, <?php echo json_encode(esc($c['description'])); ?>)'>Edit</button>
                <form method="POST" style="display:inline-block;margin-left:6px;" onsubmit="return confirm('Delete category?');">
                  <input type="hidden" name="action" value="delete_category">
                  <input type="hidden" name="cat_id" value="<?php echo intval($c['id']); ?>">
                  <button type="submit" class="small-btn danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- PREFERENCES -->
    <div id="preferences" class="panel tab-panel hidden" role="tabpanel">
      <h3>Preferences</h3>
      <form method="POST" style="max-width:720px;">
        <input type="hidden" name="action" value="update_prefs">
        <label class="form-label">Currency</label>
        <input class="form-control" name="currency" value="<?php echo esc($settings['currency']); ?>" required />

        <label class="form-label" style="margin-top:8px;">Date Format (PHP)</label>
        <input class="form-control" name="date_format" value="<?php echo esc($settings['date_format']); ?>" required />
        <div class="muted small" style="margin-bottom:8px;">Examples: Y-m-d, d/m/Y, m/d/Y</div>

        <label class="form-label" style="margin-top:8px;">Week Start</label>
        <select class="form-control" name="week_start">
          <option value="Sunday" <?php echo $settings['week_start']==='Sunday' ? 'selected':''; ?>>Sunday</option>
          <option value="Monday" <?php echo $settings['week_start']==='Monday' ? 'selected':''; ?>>Monday</option>
        </select>

        <label style="display:flex;align-items:center;gap:8px;margin-top:10px;">
          <input type="checkbox" name="alerts" <?php echo intval($settings['alerts_enabled']) ? 'checked':''; ?>/> Enable alerts & email notifications
        </label>

        <div style="margin-top:12px;">
          <button class="btn btn-primary" type="submit">Save Preferences</button>
        </div>
      </form>
    </div>

    <!-- SECURITY -->
    <div id="security" class="panel tab-panel hidden" role="tabpanel">
      <h3>Security</h3>

      <div style="max-width:640px;">
        <form method="POST">
          <input type="hidden" name="action" value="change_password">
          <label class="form-label">Current Password</label>
          <input type="password" class="form-control" name="current_password" required />
          <label class="form-label" style="margin-top:8px;">New Password</label>
          <input type="password" class="form-control" name="new_password" required />
          <label class="form-label" style="margin-top:8px;">Confirm New Password</label>
          <input type="password" class="form-control" name="confirm_password" required />
          <div style="margin-top:12px;">
            <button type="submit" class="btn btn-primary">Change Password</button>
          </div>
        </form>
      </div>
    </div>

  </div>

  <!-- Add / Edit Category Modal -->
  <div id="catModal" class="modal hidden" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-content" style="position:relative;">
      <button class="close" id="closeCatModal">&times;</button>
      <h2 id="catModalTitle">New Category</h2>
      <form id="catForm" method="POST">
        <input type="hidden" name="action" id="catAction" value="add_category">
        <input type="hidden" name="cat_id" id="catId" value="">
        <label class="form-label">Name</label>
        <input name="cat_name" id="catName" class="form-control" required />
        <label class="form-label" style="margin-top:8px;">Description</label>
        <input name="cat_desc" id="catDesc" class="form-control" />
        <div style="margin-top:12px;">
          <button type="submit" class="confirm-btn">Save</button>
        </div>
      </form>
    </div>
  </div>

<script>
  // Tab switching
  const tabs = document.querySelectorAll('.tab');
  const panels = document.querySelectorAll('.tab-panel');
  tabs.forEach(t => t.addEventListener('click', () => {
    tabs.forEach(x => x.classList.remove('active'));
    t.classList.add('active');
    const target = t.getAttribute('data-target');
    panels.forEach(p => {
      if (p.id === target) { p.classList.remove('hidden'); p.scrollIntoView({behavior:'smooth', block:'start'});} 
      else p.classList.add('hidden');
    });
  }));

  // If URL hash present, open that tab
  const hash = window.location.hash.replace('#','');
  if (hash) {
    const t = document.querySelector('.tab[data-target="'+hash+'"]');
    if (t) t.click();
  }

  // Category modal interactions
  const catModal = document.getElementById('catModal');
  const openAddCat = document.getElementById('openAddCat');
  const closeCatModal = document.getElementById('closeCatModal');
  const catForm = document.getElementById('catForm');
  const catModalTitle = document.getElementById('catModalTitle');
  const catAction = document.getElementById('catAction');
  const catId = document.getElementById('catId');
  const catName = document.getElementById('catName');
  const catDesc = document.getElementById('catDesc');

  openAddCat && openAddCat.addEventListener('click', () => {
    catModalTitle.textContent = 'New Category';
    catAction.value = 'add_category';
    catId.value = '';
    catName.value = '';
    catDesc.value = '';
    catModal.classList.remove('hidden');
  });
  closeCatModal && closeCatModal.addEventListener('click', () => catModal.classList.add('hidden'));
  window.addEventListener('click', (e) => { if (e.target === catModal) catModal.classList.add('hidden'); });

  function openEditCat(id, name, desc) {
    catModalTitle.textContent = 'Edit Category';
    catAction.value = 'edit_category';
    catId.value = id;
    catName.value = name || '';
    catDesc.value = desc || '';
    catModal.classList.remove('hidden');
  }

  // Accessibility: close modal with Escape
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') catModal.classList.add('hidden'); });
</script>

</body>
</html>
