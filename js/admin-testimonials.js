const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const rowsEl = document.getElementById('testimonial-rows');
const statusFilters = document.querySelectorAll('#status-filters button');
let activeStatus = 'pending';

function esc(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
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

statusFilters.forEach((btn) => {
  btn.addEventListener('click', () => {
    statusFilters.forEach((b) => b.setAttribute('aria-pressed', 'false'));
    btn.setAttribute('aria-pressed', 'true');
    activeStatus = btn.dataset.status;
    load();
  });
});

async function load() {
  rowsEl.innerHTML = '<p>Loading…</p>';
  const url = activeStatus ? `/admin/api/testimonials.php?status=${activeStatus}` : '/admin/api/testimonials.php';
  const res = await apiFetch(url);
  const items = await res.json();

  if (!res.ok) {
    rowsEl.innerHTML = `<p>${esc(items.error || 'Could not load testimonials.')}</p>`;
    return;
  }
  if (items.length === 0) {
    rowsEl.innerHTML = '<div class="empty-state">Nothing in this view.</div>';
    return;
  }

  rowsEl.innerHTML = items.map((t) => `
    <div class="pending-card" data-id="${t.id}">
      <div class="t-name">${esc(t.name)}${t.email ? ' &middot; ' + esc(t.email) : ''}</div>
      ${t.role_or_context ? `<div class="t-role">${esc(t.role_or_context)}</div>` : ''}
      <p style="white-space:pre-wrap;">${esc(t.testimonial)}</p>
      <div class="case-meta">
        <span>Submitted ${new Date(t.created_at).toLocaleString()}</span>
        <span class="status-pill ${t.status}">${t.status}</span>
      </div>
      <div class="inline-actions">
        ${t.status !== 'approved' ? `<button data-action="approved" data-id="${t.id}">Approve</button>` : ''}
        ${t.status !== 'rejected' ? `<button data-action="rejected" data-id="${t.id}" class="danger">Reject</button>` : ''}
        ${t.status !== 'pending' ? `<button data-action="pending" data-id="${t.id}">Move back to pending</button>` : ''}
        <button data-action="delete" data-id="${t.id}" class="danger">Delete</button>
      </div>
    </div>
  `).join('');

  rowsEl.querySelectorAll('button[data-action]').forEach((btn) => {
    btn.addEventListener('click', () => handleAction(btn.dataset.action, Number(btn.dataset.id)));
  });
}

async function handleAction(action, id) {
  if (action === 'delete') {
    if (!confirm('Permanently delete this testimonial? This cannot be undone.')) return;
    const res = await apiFetch(`/admin/api/testimonials.php?id=${id}`, { method: 'DELETE' });
    if (!res.ok) {
      const data = await res.json();
      alert(data.error || 'Something went wrong.');
      return;
    }
    load();
    return;
  }

  const res = await apiFetch(`/admin/api/testimonials.php?id=${id}`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ status: action })
  });
  if (!res.ok) {
    const data = await res.json();
    alert(data.error || 'Something went wrong.');
    return;
  }
  load();
}

load();
