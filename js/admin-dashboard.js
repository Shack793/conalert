const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const rowsEl = document.getElementById('case-rows');
const summaryEl = document.getElementById('summary');
const statusFilters = document.querySelectorAll('#status-filters button');
const drawer = document.getElementById('drawer');

let allCases = [];
let activeStatus = '';
let openCaseId = null;

function formatUSD(n) {
  if (n === null || n === undefined) return '—';
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
}

// Original-currency amount (fiat or crypto), e.g. "0.75 BTC". Shown exactly
// as reported — never converted, since crypto prices move.
function formatOriginal(amount, currency) {
  if (amount === null || amount === undefined || amount === '') return '';
  const n = new Intl.NumberFormat('en-US', { maximumFractionDigits: 8 }).format(Number(amount));
  return currency ? `${n} ${currency}` : n;
}

// Original amount first when we have it, with the USD value at report time.
function formatAmount(c) {
  const original = formatOriginal(c.amount_original, c.currency_lost);
  if (!original) return formatUSD(c.amount_usd);
  const isUsd = (c.currency_lost || '').toUpperCase() === 'USD';
  return isUsd || c.amount_usd === null || c.amount_usd === undefined
    ? original
    : `${original} (≈ ${formatUSD(c.amount_usd)})`;
}

function esc(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

// Turns already-escaped text into clickable links + <br> line breaks, for
// read-only display of reporter-submitted text (description, evidence links).
function linkifyEscaped(escapedText) {
  return escapedText
    .replace(/(https?:\/\/[^\s<]+)/gi, (match) => {
      let url = match;
      let trail = '';
      while (url && '.,!?)'.includes(url.slice(-1))) {
        trail = url.slice(-1) + trail;
        url = url.slice(0, -1);
      }
      return `<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>${trail}`;
    })
    .replace(/\n/g, '<br>');
}


async function apiFetch(url, options = {}) {
  const res = await fetch(url, {
    ...options,
    headers: {
      ...(options.headers || {}),
      'X-CSRF-Token': csrfToken
    }
  });
  if (res.status === 401) {
    window.location.href = '/admin/login.php';
    throw new Error('Not logged in');
  }
  return res;
}

async function loadSummary() {
  const res = await apiFetch('/admin/api/summary.php');
  const data = await res.json();
  summaryEl.innerHTML = Object.entries(data).map(([status, n]) => `
    <div class="pill"><b>${n}</b>${status}</div>
  `).join('');
}

async function loadCases() {
  rowsEl.innerHTML = '<tr><td colspan="8">Loading…</td></tr>';
  const url = activeStatus ? `/admin/api/cases.php?status=${activeStatus}` : '/admin/api/cases.php';
  const res = await apiFetch(url);
  allCases = await res.json();
  renderRows();
}

function renderRows() {
  if (allCases.length === 0) {
    rowsEl.innerHTML = '<tr><td colspan="8">No cases in this view.</td></tr>';
    return;
  }
  rowsEl.innerHTML = allCases.map((c) => `
    <tr data-id="${c.id}">
      <td>#${esc(c.case_number || c.id)}</td>
      <td>${new Date(c.created_at).toLocaleDateString()}</td>
      <td>${esc(c.platform_name)}</td>
      <td>${esc(c.platform_type)}</td>
      <td>${esc(formatAmount(c))}</td>
      <td>${esc(c.full_name)}</td>
      <td><span class="status-pill">${c.status}</span></td>
      <td>${c.priority}</td>
    </tr>
  `).join('');

  rowsEl.querySelectorAll('tr[data-id]').forEach((tr) => {
    tr.addEventListener('click', () => openDrawer(Number(tr.dataset.id)));
  });
}

statusFilters.forEach((btn) => {
  btn.addEventListener('click', () => {
    statusFilters.forEach((b) => b.setAttribute('aria-pressed', 'false'));
    btn.setAttribute('aria-pressed', 'true');
    activeStatus = btn.dataset.status;
    loadCases();
  });
});

async function openDrawer(id) {
  openCaseId = id;
  const res = await apiFetch(`/admin/api/case-detail.php?id=${id}`);
  const c = await res.json();

  document.getElementById('drawer-title').textContent = `Case #${c.case_number || c.id} — ${c.platform_name}`;
  document.getElementById('drawer-readonly').innerHTML = `
    <p><strong>Internal ID:</strong> ${c.id}</p>
    <p><strong>Reporter:</strong> ${esc(c.full_name)} — ${esc(c.email)}${c.country ? ' · ' + esc(c.country) : ''}</p>
    <p><strong>Amount:</strong> ${c.amount_original !== null && c.amount_original !== undefined
         ? esc(formatAmount(c))
         : `${formatUSD(c.amount_usd)} ${esc(c.currency_lost || '')}`} &nbsp;
       <strong>Incident date:</strong> ${esc(c.incident_date || 'not given')}</p>
    <p><strong>Consent to publish:</strong> ${Number(c.consent_to_publish) ? 'yes' : 'no'}</p>
    <p><strong>Description:</strong><br>${linkifyEscaped(esc(c.description))}</p>
    <p><strong>Evidence links:</strong><br>${linkifyEscaped(esc(c.evidence_links || 'none provided'))}</p>
  `;
  document.getElementById('drawer-case-number').value = c.case_number || c.id;
  document.getElementById('drawer-amount-original').value =
    c.amount_original !== null && c.amount_original !== undefined ? String(Number(c.amount_original)) : '';
  document.getElementById('drawer-currency').value = c.currency_lost || '';
  document.getElementById('drawer-amount-usd').value =
    c.amount_usd !== null && c.amount_usd !== undefined ? String(Number(c.amount_usd)) : '';
  document.getElementById('drawer-status').value = c.status;
  document.getElementById('drawer-priority').value = c.priority;
  document.getElementById('drawer-summary').value = c.public_summary || '';
  document.getElementById('drawer-notes').value = c.admin_notes || '';
  document.getElementById('drawer-message').className = 'form-message';
  document.getElementById('drawer-events').innerHTML = (c.events || []).map((e) => `
    <li>${new Date(e.created_at).toLocaleString()} — ${esc(e.event_type)}: ${esc(e.detail || '')}${e.actor ? ' (' + esc(e.actor) + ')' : ''}</li>
  `).join('') || '<li>No events yet.</li>';

  drawer.classList.add('open');
  drawer.setAttribute('aria-hidden', 'false');
}

document.getElementById('drawer-close').addEventListener('click', () => {
  drawer.classList.remove('open');
  drawer.setAttribute('aria-hidden', 'true');
});

document.getElementById('drawer-save').addEventListener('click', async () => {
  const msg = document.getElementById('drawer-message');
  const payload = {
    case_number: document.getElementById('drawer-case-number').value,
    amount_original: document.getElementById('drawer-amount-original').value,
    currency_lost: document.getElementById('drawer-currency').value,
    amount_usd: document.getElementById('drawer-amount-usd').value,
    status: document.getElementById('drawer-status').value,
    priority: document.getElementById('drawer-priority').value,
    public_summary: document.getElementById('drawer-summary').value,
    admin_notes: document.getElementById('drawer-notes').value
  };

  const res = await apiFetch(`/admin/api/case-detail.php?id=${openCaseId}`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const data = await res.json();

  if (!res.ok) {
    msg.textContent = data.error;
    msg.className = 'form-message error';
    return;
  }

  msg.textContent = 'Saved.';
  msg.className = 'form-message success';
  loadSummary();
  loadCases();
  openDrawer(openCaseId); // refresh event log
});

// ---- Change my password ----
const passwordDrawer = document.getElementById('password-drawer');
document.getElementById('change-password-link').addEventListener('click', (e) => {
  e.preventDefault();
  document.getElementById('current-password').value = '';
  document.getElementById('new-password').value = '';
  document.getElementById('password-message').className = 'form-message';
  passwordDrawer.classList.add('open');
  passwordDrawer.setAttribute('aria-hidden', 'false');
});
document.getElementById('password-drawer-close').addEventListener('click', () => {
  passwordDrawer.classList.remove('open');
  passwordDrawer.setAttribute('aria-hidden', 'true');
});
document.getElementById('password-save').addEventListener('click', async () => {
  const msg = document.getElementById('password-message');
  const payload = {
    current_password: document.getElementById('current-password').value,
    new_password: document.getElementById('new-password').value
  };
  const res = await apiFetch('/admin/api/change-password.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  if (!res.ok) {
    msg.textContent = data.error;
    msg.className = 'form-message error';
    return;
  }
  msg.textContent = data.message;
  msg.className = 'form-message success';
});

attachMarkdownToolbar(document.getElementById('drawer-summary'), { hint: 'Shown publicly if this case is published.' });
attachMarkdownToolbar(document.getElementById('drawer-notes'), { hint: 'Private — never shown publicly.' });

loadSummary();
loadCases();
