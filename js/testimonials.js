const listEl = document.getElementById('testimonial-list');
const form = document.getElementById('testimonial-form');
const messageBox = document.getElementById('testimonial-message');

function esc(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

async function loadTestimonials() {
  try {
    const res = await fetch('/api/testimonials.php');
    const data = await res.json();
    if (!res.ok || data.length === 0) {
      listEl.innerHTML = '<div class="empty-state">No testimonials published yet — be the first to share yours below.</div>';
      return;
    }
    listEl.innerHTML = `<div class="testimonial-grid">${data.map((t) => `
      <div class="testimonial-card">
        <div class="t-name">${esc(t.name)}</div>
        ${t.role_or_context ? `<div class="t-role">${esc(t.role_or_context)}</div>` : ''}
        <div class="rich-text">${t.testimonial_html}</div>
      </div>
    `).join('')}</div>`;
  } catch (err) {
    listEl.innerHTML = '<div class="empty-state">Couldn\'t load testimonials right now. Please refresh.</div>';
  }
}

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

  try {
    const res = await fetch('/api/testimonials.php', {
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
    showMessage('Network error — please try again in a moment.', 'error');
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Submit testimonial';
  }
});

loadTestimonials();
