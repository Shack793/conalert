const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const rowsEl = document.getElementById('user-rows');

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

function esc(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

async function loadUsers() {
  rowsEl.innerHTML = '<tr><td colspan="7">Loading…</td></tr>';
  const res = await apiFetch('/admin/api/users.php');
  const users = await res.json();

  if (!res.ok) {
    rowsEl.innerHTML = `<tr><td colspan="7">${esc(users.error || 'Could not load team logins.')}</td></tr>`;
    return;
  }

  rowsEl.innerHTML = users.map((u) => `
    <tr data-id="${u.id}">
      <td>${esc(u.name)}</td>
      <td>${esc(u.email)}</td>
      <td><span class="role-pill ${u.role}">${u.role}</span></td>
      <td><span class="status-pill ${u.status}">${u.status}</span></td>
      <td>${new Date(u.created_at).toLocaleDateString()}</td>
      <td>${u.last_login_at ? new Date(u.last_login_at).toLocaleString() : 'never'}</td>
      <td class="inline-actions">
        ${u.status === 'active'
          ? `<button data-action="revoke" data-id="${u.id}" class="danger">Revoke</button>`
          : `<button data-action="reactivate" data-id="${u.id}">Reactivate</button>`}
        <button data-action="reset" data-id="${u.id}">Reset password</button>
      </td>
    </tr>
  `).join('');

  rowsEl.querySelectorAll('button[data-action]').forEach((btn) => {
    btn.addEventListener('click', () => handleAction(btn.dataset.action, Number(btn.dataset.id)));
  });
}

async function handleAction(action, id) {
  let payload = {};
  let confirmMsg = '';

  if (action === 'revoke') {
    payload = { status: 'revoked' };
    confirmMsg = 'Revoke this login? They will be signed out immediately and unable to log back in.';
  } else if (action === 'reactivate') {
    payload = { status: 'active' };
    confirmMsg = 'Reactivate this login?';
  } else if (action === 'reset') {
    payload = { reset_password: true };
    confirmMsg = "Reset this person's password? A new temporary password will be generated.";
  }

  if (!confirm(confirmMsg)) return;

  const res = await apiFetch(`/admin/api/users.php?id=${id}`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });
  const data = await res.json();

  if (!res.ok) {
    alert(data.error || 'Something went wrong.');
    return;
  }

  if (data.temporary_password) {
    alert(`New temporary password: ${data.temporary_password}\n\nShare this with them securely — it will not be shown again.`);
  }

  loadUsers();
}

document.getElementById('show-new-user-form').addEventListener('click', () => {
  const form = document.getElementById('new-user-form');
  form.style.display = form.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('create-user-btn').addEventListener('click', async () => {
  const msg = document.getElementById('new-user-message');
  const payload = {
    name: document.getElementById('nu-name').value,
    email: document.getElementById('nu-email').value,
    role: document.getElementById('nu-role').value
  };

  const res = await apiFetch('/admin/api/users.php', {
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

  msg.innerHTML = `${esc(data.message)}<div class="temp-password-box">Email: ${esc(data.email)}<br>Temporary password: <strong>${esc(data.temporary_password)}</strong></div>`;
  msg.className = 'form-message success';
  document.getElementById('nu-name').value = '';
  document.getElementById('nu-email').value = '';
  loadUsers();
});

loadUsers();
