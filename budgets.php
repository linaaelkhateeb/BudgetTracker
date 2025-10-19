<?php
// budgets.php
// Front-end focused budgets page. PHP only provides initial mock data and renders the page.
// All CRUD and UI interactions are handled client-side (JavaScript) so the page "moves" and buttons work.
// No custom CSS is included here; add your styles later. Bootstrap is included for basic layout/components.

// --- Mock Data (server-side) ---
$categories = [
    ['id' => 1, 'name' => 'Groceries', 'type' => 'expense'],
    ['id' => 2, 'name' => 'Rent', 'type' => 'expense'],
    ['id' => 3, 'name' => 'Utilities', 'type' => 'expense'],
    ['id' => 4, 'name' => 'Entertainment', 'type' => 'expense'],
    ['id' => 5, 'name' => 'Salary', 'type' => 'income'],
];

$budgets = [
    ['id' => 101, 'category_id' => 1, 'category' => 'Groceries', 'amount' => 600.00, 'period' => 'monthly', 'start_date' => '2025-10-01', 'end_date' => '2025-10-31', 'notes' => 'Monthly grocery budget'],
    ['id' => 102, 'category_id' => 2, 'category' => 'Rent', 'amount' => 1200.00, 'period' => 'monthly', 'start_date' => '2025-10-01', 'end_date' => '2025-10-31', 'notes' => 'Rent for October'],
    ['id' => 103, 'category_id' => 4, 'category' => 'Entertainment', 'amount' => 200.00, 'period' => 'monthly', 'start_date' => '2025-10-01', 'end_date' => '2025-10-31', 'notes' => 'Movies, outings'],
];

// Some transactions to calculate "spent" against budgets (client will use these)
$transactions = [
    ['id' => 1, 'date' => '2025-10-03', 'description' => 'Supermarket', 'category' => 'Groceries', 'category_id' => 1, 'type' => 'expense', 'amount' => 95.25],
    ['id' => 2, 'date' => '2025-10-07', 'description' => 'Dinner Out', 'category' => 'Entertainment', 'category_id' => 4, 'type' => 'expense', 'amount' => 46.00],
    ['id' => 3, 'date' => '2025-10-10', 'description' => 'Weekly Groceries', 'category' => 'Groceries', 'category_id' => 1, 'type' => 'expense', 'amount' => 120.40],
    ['id' => 4, 'date' => '2025-10-02', 'description' => 'October Rent', 'category' => 'Rent', 'category_id' => 2, 'type' => 'expense', 'amount' => 1200.00],
    ['id' => 5, 'date' => '2025-10-15', 'description' => 'Concert', 'category' => 'Entertainment', 'category_id' => 4, 'type' => 'expense', 'amount' => 75.00],
];

$currentPage = 'budgets';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Budgets - BudgetWise</title>

  <!-- Bootstrap & icons for structure only; you will supply custom CSS later -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="page-wrapper d-flex">
    <div class="sidebar p-3">
      <div class="brand mb-4">BudgetWise</div>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link <?php if($currentPage == 'dashboard') echo 'active'; ?>" href="#">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?php if($currentPage == 'transactions') echo 'active'; ?>" href="#">Transactions</a></li>
        <li class="nav-item"><a class="nav-link <?php if($currentPage == 'budgets') echo 'active'; ?>" href="#">Budgets</a></li>
        <li class="nav-item"><a class="nav-link <?php if($currentPage == 'reports') echo 'active'; ?>" href="#">Reports</a></li>
        <li class="nav-item"><a class="nav-link <?php if($currentPage == 'settings') echo 'active'; ?>" href="#">Settings</a></li>
      </ul>
    </div>

    <main class="main-content flex-grow-1 p-4">
      <div class="d-flex align-items-center mb-4">
        <h1 class="h3 me-auto">Budgets</h1>
        <div class="btn-group me-2" role="group" aria-label="view toggles">
          <button id="gridViewBtn" class="btn btn-outline-secondary active"><i class="fas fa-th-large"></i></button>
          <button id="listViewBtn" class="btn btn-outline-secondary"><i class="fas fa-list"></i></button>
        </div>
        <button id="createBudgetBtn" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#budgetModal">
          <i class="fas fa-plus me-1"></i> Create New Budget
        </button>
      </div>

      <!-- Alerts -->
      <div id="alertsContainer"></div>

      <!-- Filters / Summary Row -->
      <div class="row mb-4 gx-3">
        <div class="col-md-4">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
            <select id="periodFilter" class="form-select">
              <option value="all">All periods</option>
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="yearly">Yearly</option>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <input id="searchInput" class="form-control" placeholder="Search budgets by category or notes..." />
        </div>
        <div class="col-md-4 text-end">
          <button id="exportCsvBtn" class="btn btn-outline-primary"><i class="fas fa-file-csv me-1"></i> Export CSV</button>
        </div>
      </div>

      <!-- Budgets container (grid or list view) -->
      <div id="budgetsContainer" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
        <!-- Cards will be rendered here by JS -->
      </div>
    </main>
  </div>

  <!-- Budget Create/Edit Modal -->
  <div class="modal fade" id="budgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <form id="budgetForm">
          <div class="modal-header">
            <h5 id="budgetModalTitle" class="modal-title">Create Budget</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="budgetId" name="id" value="">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Category</label>
                <select id="budgetCategory" class="form-select" required>
                  <option value="">-- Select Category --</option>
                  <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= $c['type'] ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Amount</label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input id="budgetAmount" type="number" step="0.01" class="form-control" required />
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Period</label>
                <select id="budgetPeriod" class="form-select">
                  <option value="monthly">Monthly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="yearly">Yearly</option>
                  <option value="custom">Custom Range</option>
                </select>
              </div>

              <div class="col-md-6 d-flex gap-2 align-items-end">
                <div class="w-50">
                  <label class="form-label">Start</label>
                  <input id="budgetStart" type="date" class="form-control" required />
                </div>
                <div class="w-50" id="endDateWrapper" style="display: none;">
                  <label class="form-label">End</label>
                  <input id="budgetEnd" type="date" class="form-control" />
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Notes (optional)</label>
                <textarea id="budgetNotes" rows="3" class="form-control"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button id="saveBudgetBtn" type="submit" class="btn btn-success">Save Budget</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Transactions Modal (show transactions for a budget) -->
  <div class="modal fade" id="budgetTxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 id="budgetTxTitle" class="modal-title">Transactions</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="budgetTxList" class="list-group">
            <!-- Transactions will be rendered here -->
          </div>
        </div>
        <div class="modal-footer">
          <button id="closeBudgetTxBtn" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS bundle (for modals) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // --- Initial state (seeded from PHP) ---
    const initialBudgets = <?php echo json_encode($budgets, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP); ?>;
    const transactions = <?php echo json_encode($transactions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP); ?>;
    const categories = <?php echo json_encode($categories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP); ?>;

    // Persist budgets in localStorage so the UI changes survive refresh while developing
    const STORAGE_KEY = 'budgetwise_budgets_v1';
    let budgets = loadBudgets();

    // View state
    let viewMode = 'grid'; // 'grid' or 'list'

    // Elements
    const budgetsContainer = document.getElementById('budgetsContainer');
    const alertsContainer = document.getElementById('alertsContainer');
    const searchInput = document.getElementById('searchInput');
    const periodFilter = document.getElementById('periodFilter');
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const createBudgetBtn = document.getElementById('createBudgetBtn');

    // Modal references
    const budgetModalEl = document.getElementById('budgetModal');
    const budgetModal = new bootstrap.Modal(budgetModalEl);
    const budgetForm = document.getElementById('budgetForm');
    const budgetModalTitle = document.getElementById('budgetModalTitle');
    const budgetIdInput = document.getElementById('budgetId');
    const budgetCategory = document.getElementById('budgetCategory');
    const budgetAmount = document.getElementById('budgetAmount');
    const budgetPeriod = document.getElementById('budgetPeriod');
    const budgetStart = document.getElementById('budgetStart');
    const budgetEnd = document.getElementById('budgetEnd');
    const endDateWrapper = document.getElementById('endDateWrapper');
    const budgetNotes = document.getElementById('budgetNotes');

    const budgetTxModal = new bootstrap.Modal(document.getElementById('budgetTxModal'));
    const budgetTxList = document.getElementById('budgetTxList');
    const budgetTxTitle = document.getElementById('budgetTxTitle');

    // --- Helpers ---
    function loadBudgets() {
      try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (raw) return JSON.parse(raw);
      } catch (e) { /* ignore parse errors */ }
      // otherwise use initial from server
      localStorage.setItem(STORAGE_KEY, JSON.stringify(initialBudgets));
      return JSON.parse(JSON.stringify(initialBudgets));
    }

    function saveBudgets() {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(budgets));
    }

    function showAlert(type, html, timeout = 4000) {
      const id = 'a' + Date.now();
      const wrapper = document.createElement('div');
      wrapper.innerHTML = `<div id="${id}" class="alert alert-${type} alert-dismissible fade show" role="alert">${html}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
      alertsContainer.appendChild(wrapper);
      if (timeout) setTimeout(() => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('show');
        setTimeout(() => el && el.remove(), 200);
      }, timeout);
    }

    function formatCurrency(v) {
      return '$' + Number(v).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function calcSpentForBudget(budget) {
      const start = new Date(budget.start_date);
      const end = budget.end_date ? new Date(budget.end_date) : new Date(budget.start_date);
      // sum transactions matching category_id and within date range
      let sum = 0;
      for (const t of transactions) {
        if (t.category_id != budget.category_id) continue;
        const td = new Date(t.date);
        if (td >= start && td <= end) sum += Number(t.amount);
      }
      return sum;
    }

    function percentOf(amount, total) {
      if (total <= 0) return 0;
      return Math.min(100, Math.round((amount / total) * 100));
    }

    // --- Rendering ---
    function renderBudgets() {
      // Apply filters
      const q = searchInput.value.trim().toLowerCase();
      const period = periodFilter.value;
      budgetsContainer.innerHTML = '';

      const filtered = budgets.filter(b => {
        if (period !== 'all' && b.period !== period) return false;
        if (!q) return true;
        const hay = (b.category + ' ' + (b.notes || '')).toLowerCase();
        return hay.indexOf(q) !== -1;
      });

      if (filtered.length === 0) {
        budgetsContainer.innerHTML = `<div class="col-12"><div class="card p-4 text-center">No budgets found. Click "Create New Budget" to add one.</div></div>`;
        return;
      }

      for (const b of filtered) {
        const spent = calcSpentForBudget(b);
        const remaining = b.amount - spent;
        const pct = percentOf(spent, b.amount);

        const col = document.createElement('div');
        col.className = (viewMode === 'grid') ? 'col' : 'col-12';
        const card = document.createElement('div');
        card.className = 'card h-100';
        card.innerHTML = `
          <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-start mb-2">
              <div class="me-auto">
                <h5 class="card-title mb-1">${escapeHtml(b.category)}</h5>
                <div class="text-muted small">${escapeHtml(b.period)} • ${escapeHtml(b.start_date)}${b.end_date ? ' — ' + escapeHtml(b.end_date) : ''}</div>
              </div>
              <div class="text-end">
                <div class="fw-bold mono-number">${formatCurrency(b.amount)}</div>
                <div class="text-muted small">Budgeted</div>
              </div>
            </div>

            <div class="mb-3">
              <div class="progress" style="height:12px;">
                <div class="progress-bar" role="progressbar" style="width: ${pct}%;" aria-valuenow="${pct}" aria-valuemin="0" aria-valuemax="100">${pct}%</div>
              </div>
            </div>

            <div class="d-flex mt-auto align-items-center">
              <div>
                <div class="small text-muted">Spent</div>
                <div class="fw-bold mono-number">${formatCurrency(spent)}</div>
                <div class="small text-muted">Remaining: <span class="mono-number">${formatCurrency(remaining)}</span></div>
              </div>
              <div class="ms-auto d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary" data-action="view" data-id="${b.id}" title="View transactions"><i class="fas fa-eye"></i></button>
                <button class="btn btn-sm btn-outline-primary" data-action="edit" data-id="${b.id}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${b.id}" title="Delete"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
        `;
        col.appendChild(card);
        budgetsContainer.appendChild(col);
      }
    }

    function escapeHtml(s) {
      if (s === null || s === undefined) return '';
      return String(s).replace(/[&<>"']/g, function (m) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
      });
    }

    // --- Events & actions ---
    // Toggle view
    gridViewBtn.addEventListener('click', () => {
      viewMode = 'grid';
      gridViewBtn.classList.add('active');
      listViewBtn.classList.remove('active');
      renderBudgets();
    });
    listViewBtn.addEventListener('click', () => {
      viewMode = 'list';
      listViewBtn.classList.add('active');
      gridViewBtn.classList.remove('active');
      renderBudgets();
    });

    // Filters
    searchInput.addEventListener('input', debounce(renderBudgets, 250));
    periodFilter.addEventListener('change', renderBudgets);

    // Export CSV
    exportCsvBtn.addEventListener('click', () => {
      const rows = [['id','category','amount','period','start_date','end_date','notes']];
      for (const b of budgets) {
        rows.push([b.id, b.category, b.amount, b.period, b.start_date, b.end_date || '', b.notes || '']);
      }
      const csv = rows.map(r => r.map(cell => `"${String(cell).replace(/"/g,'""')}"`).join(',')).join('\n');
      const blob = new Blob([csv], {type: 'text/csv'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'budgets_export.csv';
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(url);
      showAlert('success', '<strong>Exported:</strong> budgets_export.csv', 3000);
    });

    // Create new -> open modal with defaults
    createBudgetBtn.addEventListener('click', () => {
      openCreateModal();
    });

    // Budget period toggle (show end date for custom)
    budgetPeriod.addEventListener('change', () => {
      if (budgetPeriod.value === 'custom') {
        endDateWrapper.style.display = '';
        budgetEnd.required = true;
      } else {
        endDateWrapper.style.display = 'none';
        budgetEnd.required = false;
        budgetEnd.value = '';
      }
    });

    // Submit budget form (create or update)
    budgetForm.addEventListener('submit', (e) => {
      e.preventDefault();
      saveBudgetFromModal();
    });

    // Delegate card buttons
    budgetsContainer.addEventListener('click', (e) => {
      const btn = e.target.closest('button');
      if (!btn) return;
      const action = btn.dataset.action;
      const id = btn.dataset.id;
      if (action === 'view') {
        openTransactionsForBudget(id);
      } else if (action === 'edit') {
        openEditModal(id);
      } else if (action === 'delete') {
        handleDeleteBudget(id);
      }
    });

    // --- Modal actions ---
    function openCreateModal() {
      budgetModalTitle.textContent = 'Create Budget';
      budgetIdInput.value = '';
      budgetCategory.value = '';
      budgetAmount.value = '';
      budgetPeriod.value = 'monthly';
      budgetStart.value = (new Date()).toISOString().slice(0,10);
      budgetEnd.value = '';
      budgetNotes.value = '';
      endDateWrapper.style.display = 'none';
      budgetEnd.required = false;
      budgetModal.show();
    }

    function openEditModal(id) {
      const b = budgets.find(x => String(x.id) === String(id));
      if (!b) { showAlert('danger', 'Budget not found'); return; }
      budgetModalTitle.textContent = 'Edit Budget';
      budgetIdInput.value = b.id;
      budgetCategory.value = b.category_id;
      budgetAmount.value = b.amount;
      budgetPeriod.value = b.period;
      budgetStart.value = b.start_date;
      budgetEnd.value = b.end_date || '';
      budgetNotes.value = b.notes || '';
      if (b.period === 'custom') {
        endDateWrapper.style.display = '';
        budgetEnd.required = true;
      } else {
        endDateWrapper.style.display = 'none';
        budgetEnd.required = false;
      }
      budgetModal.show();
    }

    function saveBudgetFromModal() {
      const id = budgetIdInput.value;
      const catId = Number(budgetCategory.value);
      const cat = categories.find(c => c.id === catId);
      if (!cat) { showAlert('danger', 'Please select a valid category'); return; }
      const amount = Number(budgetAmount.value);
      if (!amount || amount <= 0) { showAlert('danger', 'Please enter a valid amount'); return; }
      const period = budgetPeriod.value;
      const start = budgetStart.value;
      const end = budgetEnd.value || null;
      const notes = budgetNotes.value.trim();

      if (!start) { showAlert('danger', 'Please select a start date'); return; }

      if (id) {
        // update
        const idx = budgets.findIndex(x => String(x.id) === String(id));
        if (idx === -1) { showAlert('danger', 'Budget not found for update'); return; }
        budgets[idx] = {
          ...budgets[idx],
          category_id: catId,
          category: cat.name,
          amount,
          period,
          start_date: start,
          end_date: end,
          notes
        };
        saveBudgets();
        renderBudgets();
        budgetModal.hide();
        showAlert('success', '<strong>Updated:</strong> Budget updated successfully.');
      } else {
        // create new with generated ID
        const newId = generateId();
        const newBudget = {
          id: newId,
          category_id: catId,
          category: cat.name,
          amount,
          period,
          start_date: start,
          end_date: end,
          notes
        };
        budgets.unshift(newBudget); // show newest first
        saveBudgets();
        renderBudgets();
        budgetModal.hide();
        showAlert('success', '<strong>Created:</strong> New budget added.');
      }
    }

    function handleDeleteBudget(id) {
      if (!confirm('Are you sure you want to delete this budget? This cannot be undone in the demo.')) return;
      const idx = budgets.findIndex(x => String(x.id) === String(id));
      if (idx === -1) { showAlert('danger', 'Budget not found'); return; }
      budgets.splice(idx, 1);
      saveBudgets();
      renderBudgets();
      showAlert('warning', '<strong>Deleted:</strong> Budget removed.');
    }

    function openTransactionsForBudget(id) {
      const b = budgets.find(x => String(x.id) === String(id));
      if (!b) { showAlert('danger', 'Budget not found'); return; }
      budgetTxTitle.textContent = `Transactions for ${b.category}`;
      budgetTxList.innerHTML = '';
      const start = new Date(b.start_date);
      const end = b.end_date ? new Date(b.end_date) : new Date(b.start_date);
      const filteredTx = transactions.filter(t => {
        if (t.category_id != b.category_id) return false;
        const td = new Date(t.date);
        return td >= start && td <= end;
      });
      if (filteredTx.length === 0) {
        budgetTxList.innerHTML = `<div class="list-group-item">No transactions for this budget period.</div>`;
      } else {
        for (const tx of filteredTx) {
          const item = document.createElement('div');
          item.className = 'list-group-item d-flex justify-content-between align-items-start';
          item.innerHTML = `<div>
              <div class="fw-bold">${escapeHtml(tx.description)}</div>
              <div class="text-muted small">${escapeHtml(tx.date)} • ${escapeHtml(tx.category)}</div>
            </div>
            <div class="text-end">
              <div class="fw-bold mono-number">${tx.type === 'income' ? '+' : '-'}${formatCurrency(tx.amount).replace('$','')}</div>
            </div>`;
          budgetTxList.appendChild(item);
        }
      }
      budgetTxModal.show();
    }

    // Utility to generate unique-ish id in demo
    function generateId() {
      return Date.now() + Math.floor(Math.random() * 1000);
    }

    // small debounce
    function debounce(fn, wait) {
      let t;
      return function () {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, arguments), wait);
      };
    }

    // initialize
    renderBudgets();

    // expose some functions for debugging from console (optional)
    window.__budgets = budgets;
    window.__saveBudgets = saveBudgets;
  </script>
</body>
</html>