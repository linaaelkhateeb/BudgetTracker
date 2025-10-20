<?php
session_start();
require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../lib/csrf.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: ' . url('login.php'));
  exit;
}

$userId = (int)$_SESSION['user_id'];

// Load user
$stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) {
  header('Location: ' . url('auth/logout.php'));
  exit;
}

// Preferences
$prefStmt = $pdo->prepare('SELECT currency, date_format, week_start, alerts FROM preferences WHERE user_id = ? LIMIT 1');
$prefStmt->execute([$userId]);
$preferences = $prefStmt->fetch() ?: [
  'currency' => 'USD',
  'date_format' => 'Y-m-d',
  'week_start' => 1,
  'alerts' => 1,
];

// Categories controls
$sort = isset($_GET['sort']) ? strtolower($_GET['sort']) : 'name';
$q    = isset($_GET['q']) ? trim($_GET['q']) : '';
$view = 'grid';

$orderSql = 'name ASC';
if     ($sort === 'z')      $orderSql = 'name DESC';
elseif ($sort === 'recent') $orderSql = 'created_at DESC';
elseif ($sort === 'oldest') $orderSql = 'created_at ASC';
elseif ($sort === 'color')  $orderSql = 'color IS NULL, color ASC, name ASC';

$whereSql = 'user_id = ?';
$params   = [$userId];
if ($q !== '') { $whereSql .= ' AND name LIKE ?'; $params[] = "%$q%"; }

$sql = "SELECT id, name, color FROM categories WHERE $whereSql ORDER BY $orderSql";
$catStmt = $pdo->prepare($sql);
$catStmt->execute($params);
$categories = $catStmt->fetchAll();
$categoryCount = is_array($categories) ? count($categories) : 0;

// Login history
$hist = [];
try {
  $histStmt = $pdo->prepare('SELECT ip, user_agent, created_at FROM login_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
  $histStmt->execute([$userId]);
  $hist = $histStmt->fetchAll();
} catch (Throwable $e) {}

$csrf = htmlspecialchars(csrf_token(), ENT_QUOTES);
$flashSuccess = $_SESSION['form_success'] ?? null;
$flashErrors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_success'], $_SESSION['form_errors']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Settings – BudgetTracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('CSS/style.css') ?>">
  <link rel="stylesheet" href="<?= url('CSS/settings.css') ?>">
  <style>
    .category-color { width: 32px; height: 24px; border-radius: 6px; border: 1px solid rgba(0,0,0,.1); display:inline-block; }
    .table > :not(caption) > * > * { vertical-align: middle; }
  </style>
</head>
<body class="settings-page" data-csrf="<?= $csrf ?>">
<?php if ($flashSuccess || $flashErrors): ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
  <div id="settingsToast" class="toast align-items-center text-bg-<?= $flashSuccess ? 'success' : 'danger' ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        <?= htmlspecialchars($flashSuccess ?: (is_array($flashErrors) ? (reset($flashErrors) ?: 'Something went wrong.') : 'Something went wrong.' )) ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
  </div>
<?php endif; ?>
<div class="container py-4">
  <section class="settings-hero mb-4">
    <div class="d-flex align-items-center gap-3">
      <div class="brand-badge">BT</div>
      <div>
        <div class="brand-title">BudgetTracker</div>
        <div class="hero-sub">Personalize your experience. Manage your profile, categories, preferences, and security.</div>
      </div>
    </div>
    <div class="row g-3 mt-2">
      <div class="col-md-4">
        <div class="chip stat-chip chip-flex w-100">
          <i class="fa-solid fa-folder-open me-2"></i>
          <span class="chip-title">Categories</span>
          <span class="chip-count ms-2"><?= (int)$categoryCount ?></span>
        </div>
      </div>
      <div class="col-md-4">
        <form class="chip stat-chip chip-flex" action="<?= url('users/userssettings_prefs.php') ?>" method="post">
          <input type="hidden" name="csrf" value="<?= $csrf ?>">
          <i class="fa-solid fa-coins me-2"></i>
          <label class="chip-title me-2">Currency</label>
          <?php $currNow = strtoupper($preferences['currency'] ?? 'USD'); ?>
          <input class="form-control form-control-sm chip-input" list="currencyOptions" name="currency" value="<?= htmlspecialchars($currNow) ?>" onchange="this.form.submit()" placeholder="Type...">
          <datalist id="currencyOptions">
            <?php $currOpts=['USD','EUR','AED','GBP','SAR','JPY','CNY','INR','AUD','CAD','CHF','KWD','QAR','BHD','OMR','NZD','SEK','NOK','DKK','ZAR'];
              foreach ($currOpts as $c) { echo "<option value=\"$c\"></option>"; }
            ?>
          </datalist>
        </form>
      </div>
      <div class="col-md-4">
        <form class="chip stat-chip chip-flex" action="<?= url('users/userssettings_prefs.php') ?>" method="post">
          <input type="hidden" name="csrf" value="<?= $csrf ?>">
          <i class="fa-solid fa-bell me-2"></i>
          <label class="chip-title me-2">Alerts</label>
          <select class="form-select form-select-sm chip-select" name="alerts" onchange="this.form.submit()">
            <option value="1" <?= ((int)($preferences['alerts'] ?? 1)===1?'selected':'') ?>>On</option>
            <option value="0" <?= ((int)($preferences['alerts'] ?? 1)===0?'selected':'') ?>>Off</option>
          </select>
        </form>
      </div>
    </div>
  </section>

  <div class="row g-4 mt-2">
    <aside class="col-lg-3">
      <div class="card side-card p-2">
        <ul class="nav nav-pills flex-column settings-side" id="settingsTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab"><i class="fa-solid fa-user me-2"></i>Profile</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab"><i class="fa-solid fa-folder-tree me-2"></i>Categories</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab"><i class="fa-solid fa-sliders me-2"></i>Preferences</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab"><i class="fa-solid fa-shield-halved me-2"></i>Security</button>
          </li>
        </ul>
      </div>
    </aside>
    <main class="col-lg-9">
      <div class="quick-actions d-flex flex-wrap gap-2 mb-3">
        <a id="qa-add-category" class="btn btn-primary" href="#categories"><i class="fa-solid fa-plus me-2"></i>Add Category</a>
        <a id="qa-change-password" class="btn btn-outline-secondary" href="#security"><i class="fa-solid fa-key me-2"></i>Change Password</a>
        <a id="qa-edit-profile" class="btn btn-outline-secondary" href="#profile"><i class="fa-solid fa-user-pen me-2"></i>Edit Profile</a>
      </div>
      <div class="tab-content py-2">
        <!-- Profile -->
        <div class="tab-pane fade show active" id="profile" role="tabpanel">
          <div class="section-head d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><i class="fa-solid fa-user me-2"></i>Profile</h5>
            <span class="section-sub">Update your name, email, and currency.</span>
          </div>
          <form action="<?= url('users/userssettings_profile.php') ?>" method="post" class="row g-3">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <div class="col-md-6">
              <label class="form-label">Full name</label>
              <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email (.com only)</label>
              <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Currency</label>
              <input type="text" class="form-control" name="currency" maxlength="3" value="<?= htmlspecialchars($preferences['currency'], ENT_QUOTES) ?>">
            </div>
            <div class="col-12">
              <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Update Profile</button>
            </div>
          </form>
        </div>

        <!-- Categories -->
        <div class="tab-pane fade" id="categories" role="tabpanel">
          <div class="section-head d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><i class="fa-solid fa-folder-tree me-2"></i>Your Categories</h5>
          </div>
          <div class="card p-3 mb-3">
          <div class="category-controls d-flex justify-content-center flex-wrap gap-2 mb-3">
            <form class="d-flex flex-wrap gap-2" method="get" action="<?= url('users/userssettings.php') ?>">
              <input type="hidden" name="view" value="<?= htmlspecialchars($view) ?>">
              <input id="catSearch" type="text" class="form-control form-control-sm" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search categories...">
              <select class="form-select form-select-sm" name="sort" onchange="this.form.submit()">
                <option value="name"   <?= $sort==='name'?'selected':'' ?>>A–Z</option>
                <option value="z"      <?= $sort==='z'?'selected':'' ?>>Z–A</option>
                <option value="recent" <?= $sort==='recent'?'selected':'' ?>>Recent</option>
                <option value="oldest" <?= $sort==='oldest'?'selected':'' ?>>Oldest</option>
                <option value="color"  <?= $sort==='color'?'selected':'' ?>>Color</option>
              </select>
              <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i>Search</button>
            </form>
          </div>
          </div>
          <form action="<?= url('users/userssettings_categories.php') ?>" method="post" class="mb-3 d-flex gap-2 align-items-end">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <input type="hidden" name="action" value="create">
            <div>
              <label class="form-label">Category name</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div>
              <label class="form-label">Color</label>
              <input id="catColor" type="color" class="form-control form-control-color" name="color" value="#a16855">
              <div class="color-presets mt-2 d-flex flex-wrap gap-2">
                <button type="button" class="color-chip" data-color="#a16855" style="background:#a16855"></button>
                <button type="button" class="color-chip" data-color="#7b4b3a" style="background:#7b4b3a"></button>
                <button type="button" class="color-chip" data-color="#c6b8a2" style="background:#c6b8a2"></button>
                <button type="button" class="color-chip" data-color="#2ecc71" style="background:#2ecc71"></button>
                <button type="button" class="color-chip" data-color="#3498db" style="background:#3498db"></button>
                <button type="button" class="color-chip" data-color="#e74c3c" style="background:#e74c3c"></button>
              </div>
            </div>
            <div>
              <label class="form-label">Icon (Font Awesome class)</label>
              <div class="input-group">
                <span class="input-group-text icon-preview"><i class="fa-solid fa-tag" id="iconPreview"></i></span>
                <input type="text" class="form-control icon-input" name="icon" id="iconInput" placeholder="e.g. fa-cart-shopping">
              </div>
              <div class="mt-2 d-flex flex-wrap gap-2">
                <?php $suggestedIcons = ['fa-cart-shopping','fa-utensils','fa-bolt','fa-house','fa-car','fa-heart'];
                  foreach ($suggestedIcons as $ic) {
                    echo '<button type="button" class="btn btn-sm btn-outline-secondary ic-suggest" data-val="'.htmlspecialchars($ic).'"><i class="fa-solid '.htmlspecialchars($ic).'"></i></button>';
                  }
                ?>
              </div>
            </div>
            <button class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Add</button>
          </form>

          <?php if ($categoryCount === 0): ?>
            <div class="empty-card">
              <i class="fa-solid fa-box-open"></i>
              <div class="empty-title">No categories yet</div>
              <div class="empty-text">Create your first category to organize transactions better.</div>
              <a class="btn btn-primary mt-2" data-bs-toggle="tab" href="#categories" role="tab"><i class="fa-solid fa-plus me-2"></i>Add Category</a>
            </div>
          <?php else: ?>
            <?php if ($view === 'grid'): ?>
              <div class="category-grid mb-3">
                <?php foreach ($categories as $c): ?>
                  <div class="cat-card card p-3">
                    <div class="d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center gap-2">
                        <span class="category-color" style="background: <?= htmlspecialchars($c['color'] ?: '#dddddd') ?>"></span>
                        <?php if (!empty($c['icon'])): ?>
                          <i class="fa-solid <?= htmlspecialchars($c['icon']) ?>" title="<?= htmlspecialchars($c['icon']) ?>" style="opacity:.8"></i>
                        <?php endif; ?>
                        <strong class="editable-name" data-cat-id="<?= (int)$c['id'] ?>" title="Double-click to rename"><?= htmlspecialchars($c['name']) ?></strong>
                      </div>
                      <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#editCat<?= (int)$c['id'] ?>">
                          <i class="fa-solid fa-pen"></i>
                        </button>
                        <form action="<?= url('users/userssettings_categories.php') ?>" method="post" class="d-inline">
                          <input type="hidden" name="csrf" value="<?= $csrf ?>">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                          <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')"><i class="fa-solid fa-trash"></i></button>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead><tr><th>Name</th><th>Color</th><th class="text-end">Actions</th></tr></thead>
                  <tbody>
                  <?php foreach ($categories as $c): ?>
                    <tr>
                      <td><?= htmlspecialchars($c['name']) ?></td>
                      <td><span class="category-color" style="background: <?= htmlspecialchars($c['color'] ?: '#dddddd') ?>"></span></td>
                      <td class="text-end">
                        <form action="<?= url('users/userssettings_categories.php') ?>" method="post" class="d-inline">
                          <input type="hidden" name="csrf" value="<?= $csrf ?>">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                          <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')"><i class="fa-solid fa-trash"></i></button>
                        </form>
                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#editCat<?= (int)$c['id'] ?>">
                          <i class="fa-solid fa-pen"></i>
                        </button>
                        <!-- Edit modal -->
                        <div class="modal fade" id="editCat<?= (int)$c['id'] ?>" tabindex="-1">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form action="<?= url('users/userssettings_categories.php') ?>" method="post">
                                <div class="modal-header"><h5 class="modal-title">Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body">
                                  <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                  <input type="hidden" name="action" value="update">
                                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                  <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($c['name']) ?>" required>
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Color</label>
                                    <input type="color" class="form-control form-control-color" name="color" value="<?= htmlspecialchars($c['color'] ?: '#a16855') ?>">
                                  </div>
                                  <div class="mb-3">
                                    <label class="form-label">Icon (Font Awesome class)</label>
                                    <input type="text" class="form-control icon-input" name="icon" value="<?= htmlspecialchars($c['icon'] ?? '') ?>" placeholder="e.g. fa-cart-shopping">
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  <button class="btn btn-primary">Save</button>
                                </div>
                              </form>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <!-- Preferences -->
        <div class="tab-pane fade" id="preferences" role="tabpanel">
          <div class="section-head d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><i class="fa-solid fa-sliders me-2"></i>Preferences</h5>
            <span class="section-sub">Choose date format, week start, and alerts.</span>
          </div>
          <form action="<?= url('users/userssettings_prefs.php') ?>" method="post" class="row g-3">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <div class="col-md-4">
              <label class="form-label" data-bs-toggle="tooltip" title="How dates are displayed across the app.">Date format</label>
              <select class="form-select" name="date_format">
                <?php
                  $fmtOptions = ['Y-m-d','d/m/Y','m/d/Y'];
                  foreach ($fmtOptions as $fmt) {
                    $sel = $preferences['date_format'] === $fmt ? 'selected' : '';
                    echo "<option value=\"$fmt\" $sel>$fmt</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" data-bs-toggle="tooltip" title="Choose Sunday or Monday as the first day of the week.">Week starts on</label>
              <select class="form-select" name="week_start">
                <?php
                  $weekMap = [0=>'Sunday',1=>'Monday'];
                  foreach ($weekMap as $val=>$label) {
                    $sel = (int)$preferences['week_start'] === $val ? 'selected' : '';
                    echo "<option value=\"$val\" $sel>$label</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" data-bs-toggle="tooltip" title="Enable or disable in-app alerts.">Alerts</label>
              <select class="form-select" name="alerts">
                <option value="1" <?= (int)$preferences['alerts'] === 1 ? 'selected' : '' ?>>Enabled</option>
                <option value="0" <?= (int)$preferences['alerts'] === 0 ? 'selected' : '' ?>>Disabled</option>
              </select>
            </div>
            <div class="col-12">
              <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Save Preferences</button>
            </div>
          </form>
        </div>

        <!-- Security -->
        <div class="tab-pane fade" id="security" role="tabpanel">
          <div class="row g-4">
            <div class="col-lg-6">
              <div class="card p-3">
                <div class="section-head d-flex align-items-center justify-content-between">
                  <h5 class="mb-0"><i class="fa-solid fa-key me-2"></i>Change Password</h5>
                  <span class="section-sub">Keep your account secure.</span>
                </div>
                <form action="<?= url('users/userssettings_password.php') ?>" method="post" class="row g-3">
                  <input type="hidden" name="csrf" value="<?= $csrf ?>">
                  <div class="col-12">
                    <label class="form-label">Current password</label>
                    <div class="input-group">
                      <input type="password" class="form-control" name="current_password" id="pwdCurrent" required>
                      <button class="btn btn-outline-secondary pwd-toggle" type="button" data-target="#pwdCurrent"><i class="fa-solid fa-eye"></i></button>
                    </div>
                  </div>
                  <div class="col-12">
                    <label class="form-label">New password</label>
                    <div class="input-group">
                      <input type="password" class="form-control" name="new_password" id="pwdNew" required>
                      <button class="btn btn-outline-secondary pwd-toggle" type="button" data-target="#pwdNew"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <div class="mt-2 strength"><div class="strength-bar" id="pwdStrengthBar"></div></div>
                    <div class="strength-label" id="pwdStrengthLabel">Strength: Weak</div>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Confirm new password</label>
                    <div class="input-group">
                      <input type="password" class="form-control" name="confirm_password" id="pwdConfirm" required>
                      <button class="btn btn-outline-secondary pwd-toggle" type="button" data-target="#pwdConfirm"><i class="fa-solid fa-eye"></i></button>
                    </div>
                  </div>
                  <div class="col-12">
                    <button class="btn btn-primary">Update Password</button>
                  </div>
                </form>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card p-3">
                <h5>Recent Login History</h5>
                <?php if (!$hist): ?>
                  <div class="empty-inline d-flex align-items-center gap-2 text-muted">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>No login history to show yet.</span>
                  </div>
                <?php else: ?>
                  <ul class="list-group">
                    <?php foreach ($hist as $h): ?>
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                          <div>
                            <strong><?= htmlspecialchars($h['ip'] ?? 'Unknown') ?></strong>
                          </div>
                          <small class="text-muted">
                            <?php $ua = $h['user_agent'] ?? ''; $deviceIcon = (stripos($ua, 'mobile') !== false) ? 'fa-mobile-screen' : 'fa-desktop';
                              if (stripos($ua, 'android') !== false) $deviceIcon = 'fa-android';
                              if (stripos($ua, 'iphone') !== false) $deviceIcon = 'fa-mobile-screen';
                              if (stripos($ua, 'mac') !== false || stripos($ua, 'darwin') !== false) $deviceIcon = 'fa-apple';
                              if (stripos($ua, 'windows') !== false) $deviceIcon = 'fa-windows';
                              if (stripos($ua, 'linux') !== false) $deviceIcon = 'fa-linux';
                            ?>
                            <i class="fa-brands <?= $deviceIcon ?> me-1"></i>
                            <?= htmlspecialchars($ua ?: 'Device') ?>
                          </small>
                        </div>
                        <span class="badge bg-secondary"><?= htmlspecialchars($h['created_at']) ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function(){
    function showTab(tabButtonSelector){
      var btn = document.querySelector(tabButtonSelector);
      if (!btn) return;
      var tab = bootstrap.Tab.getOrCreateInstance(btn);
      tab.show();
      document.getElementById('settingsTabs').scrollIntoView({behavior:'smooth', block:'start'});
    }
    document.getElementById('qa-add-category')?.addEventListener('click', function(e){ e.preventDefault(); showTab('#categories-tab'); });
    document.getElementById('qa-change-password')?.addEventListener('click', function(e){ e.preventDefault(); showTab('#security-tab'); });
    document.getElementById('qa-edit-profile')?.addEventListener('click', function(e){ e.preventDefault(); showTab('#profile-tab'); });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });

    <?php if ($flashSuccess || $flashErrors): ?>
    var toastEl = document.getElementById('settingsToast');
    if (toastEl) { bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3500 }).show(); }
    <?php endif; ?>

    document.querySelectorAll('.color-chip').forEach(function(btn){
      btn.addEventListener('click', function(){
        var v = this.getAttribute('data-color');
        var inp = document.getElementById('catColor');
        if (inp) { inp.value = v; }
      });
    });

    document.querySelectorAll('.ic-suggest').forEach(function(btn){
      btn.addEventListener('click', function(){
        var val = this.getAttribute('data-val');
        var input = document.getElementById('iconInput');
        var prev = document.getElementById('iconPreview');
        if (input) { input.value = val; }
        if (prev) { prev.className = 'fa-solid ' + val; }
      });
    });
    document.getElementById('iconInput')?.addEventListener('input', function(){
      var prev = document.getElementById('iconPreview');
      if (prev) { prev.className = 'fa-solid ' + (this.value || 'fa-tag'); }
    });

    // Inline rename for categories
    document.querySelectorAll('.editable-name').forEach(function(el){
      el.addEventListener('dblclick', function(){
        var span = this;
        var id = span.getAttribute('data-cat-id');
        var old = span.textContent;
        var input = document.createElement('input');
        input.type = 'text'; input.value = old; input.className = 'form-control form-control-sm';
        span.replaceWith(input); input.focus(); input.select();
        function finish(ok){ input.replaceWith(span); if (ok) span.textContent = ok; }
        function save(){
          var val = input.value.trim();
          if (!val || val === old) { finish(); return; }
          fetch('<?= url('users/userssettings_categories.php') ?>', {
            method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ csrf: document.body.dataset.csrf, action: 'update', id: id, name: val })
          }).then(function(){ finish(val); }).catch(function(){ finish(); });
        }
        input.addEventListener('blur', save);
        input.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); save(); } if (e.key === 'Escape') { finish(); } });
      });
    });

    // Password show/hide toggles
    document.querySelectorAll('.pwd-toggle').forEach(function(btn){
      btn.addEventListener('click', function(){
        var sel = this.getAttribute('data-target');
        var inp = document.querySelector(sel);
        if (!inp) return;
        inp.type = (inp.type === 'password') ? 'text' : 'password';
        var ico = this.querySelector('i');
        if (ico) { ico.className = 'fa-solid ' + (inp.type === 'password' ? 'fa-eye' : 'fa-eye-slash'); }
      });
    });

    // Password strength meter
    function strengthScore(p){
      var s = 0; if (!p) return 0; if (p.length >= 8) s += 1; if (/[A-Z]/.test(p)) s += 1; if (/[a-z]/.test(p)) s += 1; if (/\d/.test(p)) s += 1; if (/[^A-Za-z0-9]/.test(p)) s += 1; return s;
    }
    function updateStrength(){
      var p = document.getElementById('pwdNew')?.value || ''; var score = strengthScore(p);
      var bar = document.getElementById('pwdStrengthBar'); var lab = document.getElementById('pwdStrengthLabel');
      var widths = ['10%','25%','40%','65%','85%','100%']; var labels = ['Very weak','Weak','Fair','Good','Strong','Very strong'];
      var colors = ['#e74c3c','#e67e22','#f1c40f','#27ae60','#2ecc71','#2ecc71'];
      var idx = Math.min(score, 5);
      if (bar){ bar.style.width = widths[idx]; bar.style.background = colors[idx]; }
      if (lab){ lab.textContent = 'Strength: ' + labels[idx]; }
    }
    document.getElementById('pwdNew')?.addEventListener('input', updateStrength);
    updateStrength();

    // Shortcuts overlay modal on '?'
    var shortcutsModal = document.getElementById('shortcutsModal');
    document.addEventListener('keydown', function(e){
      if (e.shiftKey && e.key === '?') {
        e.preventDefault();
        if (shortcutsModal) bootstrap.Modal.getOrCreateInstance(shortcutsModal).show();
      }
    });

    var waitingG = false, gTimer = null;
    document.addEventListener('keydown', function(e){
      if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
        var tag = e.target.tagName.toLowerCase();
        if (tag !== 'input' && tag !== 'textarea') { e.preventDefault(); document.getElementById('catSearch')?.focus(); }
      }
      if (!waitingG && (e.key === 'g' || e.key === 'G')) {
        waitingG = true; clearTimeout(gTimer); gTimer = setTimeout(function(){ waitingG = false; }, 1000);
        return;
      }
      if (waitingG) {
        if (e.key.toLowerCase() === 'p') { showTab('#profile-tab'); }
        if (e.key.toLowerCase() === 'c') { showTab('#categories-tab'); }
        if (e.key.toLowerCase() === 's') { showTab('#security-tab'); }
        waitingG = false; clearTimeout(gTimer);
      }
    });
  })();
</script>
</body>
</html>
