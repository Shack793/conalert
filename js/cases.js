const listEl = document.getElementById('case-list');
const filterButtons = document.querySelectorAll('#filters button');
let allCases = [];
let activeFilter = '';

function formatUSD(n) {
  if (n === null || n === undefined) return 'undisclosed amount';
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
}

// Original-currency amount (fiat or crypto), e.g. "0.75 BTC". Shown exactly
// as reported — never converted, since crypto prices move.
function formatOriginal(amount, currency) {
  const n = new Intl.NumberFormat('en-US', { maximumFractionDigits: 8 }).format(Number(amount));
  return currency ? `${n} ${currency}` : n;
}

function formatAmount(c) {
  if (c.amount_original === null || c.amount_original === undefined) {
    return formatUSD(c.amount_usd) + (c.currency_lost ? ' &middot; ' + escapeHtml(c.currency_lost) : '');
  }
  const original = escapeHtml(formatOriginal(c.amount_original, c.currency_lost));
  const isUsd = (c.currency_lost || '').toUpperCase() === 'USD';
  if (isUsd || c.amount_usd === null || c.amount_usd === undefined) return original;
  return `${original} <span class="case-amount-usd">(≈ ${formatUSD(c.amount_usd)} when reported)</span>`;
}

function formatDate(iso) {
  if (!iso) return 'date not given';
  return new Date(iso).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function badgeClass(type) {
  return { casino: 'badge-casino', exchange: 'badge-exchange', other: 'badge-other' }[type] || 'badge-other';
}

function render() {
  const filtered = activeFilter ? allCases.filter((c) => c.platform_type === activeFilter) : allCases;

  if (filtered.length === 0) {
    listEl.innerHTML = `
      <div class="empty-state">
        No published cases in this category yet. That's a good thing — or it means nobody's filed one yet.
        <a href="/submit.html">Submit yours</a> if that's you.
      </div>`;
    return;
  }

  listEl.innerHTML = filtered.map((c) => `
    <article class="case-file" data-tab="CASE #${escapeHtml(String(c.display_number ?? c.id))}">
      <span class="badge ${badgeClass(c.platform_type)}">${c.platform_type}</span>
      <h3>${escapeHtml(c.platform_name)}</h3>
      <div class="case-meta">
        <span class="case-amount">${formatAmount(c)}</span>
        <span>${formatDate(c.incident_date)}</span>
        ${c.country ? `<span>${escapeHtml(c.country)}</span>` : ''}
      </div>
      <div class="case-summary rich-text">${c.public_summary_html || '<p>Summary pending admin review.</p>'}</div>
    </article>
  `).join('');
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

filterButtons.forEach((btn) => {
  btn.addEventListener('click', () => {
    filterButtons.forEach((b) => b.setAttribute('aria-pressed', 'false'));
    btn.setAttribute('aria-pressed', 'true');
    activeFilter = btn.dataset.filter;
    render();
  });
});

async function load() {
  try {
    const res = await fetch('/api/cases.php');
    if (!res.ok) throw new Error('failed');
    allCases = await res.json();
    render();
  } catch (err) {
    listEl.innerHTML = `<div class="empty-state">Couldn't load cases right now. Please refresh.</div>`;
  }
}

load();
