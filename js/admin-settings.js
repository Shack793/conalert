const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const form = document.getElementById('settings-form');
const msg = document.getElementById('settings-message');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  msg.className = 'form-message';

  const formData = new FormData(form);
  formData.set('csrf_token', csrfToken);
  // Checkboxes only appear in FormData when checked — that's fine, the
  // server treats "missing" as unchecked (see admin/api/settings.php).

  const submitBtn = form.querySelector('button[type="submit"]');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Saving…';

  try {
    const res = await fetch('/admin/api/settings.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();

    if (res.status === 401) {
      window.location.href = '/admin/login.php';
      return;
    }

    if (!res.ok) {
      msg.textContent = data.error || 'Something went wrong.';
      msg.className = 'form-message error';
      return;
    }

    msg.textContent = data.message;
    msg.className = 'form-message success';

    if (data.settings) {
      document.getElementById('logo-preview').src = data.settings.logo_path + '?t=' + Date.now();
      document.getElementById('favicon-preview').src = data.settings.favicon_path + '?t=' + Date.now();
    }
  } catch (err) {
    msg.textContent = 'Network error — please try again.';
    msg.className = 'form-message error';
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Save settings';
  }
});
