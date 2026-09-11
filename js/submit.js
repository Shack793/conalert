const form = document.getElementById('case-form');
const messageBox = document.getElementById('form-message');

function showMessage(text, kind) {
  messageBox.textContent = text;
  messageBox.className = `form-message ${kind}`;
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  showMessage('', '');

  const submitBtn = form.querySelector('button[type="submit"]');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Submitting…';

  const formData = new FormData(form);
  const payload = Object.fromEntries(formData.entries());
  payload.consent_to_publish = form.consent_to_publish.checked;

  try {
    const res = await fetch('/api/cases.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();

    if (!res.ok) {
      showMessage(data.error || 'Something went wrong. Please try again.', 'error');
      return;
    }

    showMessage(data.message, 'success');
    form.reset();
  } catch (err) {
    showMessage('Network error — please try again in a moment, or email us directly.', 'error');
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Submit case';
  }
});
