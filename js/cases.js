const listEl = document.getElementById('case-list');
const filterButtons = document.querySelectorAll('#filters button');
let allCases = [];
let activeFilter = '';

function formatUSD(n) {
  if (n === null || n === undefined) return 'undisclosed amount';
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
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
    <article class="case-file" data-tab="CASE #${escapeHtml(c.case_number || c.id)}">
      <span class="badge ${badgeClass(c.platform_type)}">${c.platform_type}</span>
      <h3>${escapeHtml(c.platform_name)}</h3>
      <div class="case-meta">
        <span class="case-amount">${formatUSD(c.amount_usd)}${c.currency_lost ? ' &middot; ' + escapeHtml(c.currency_lost) : ''}</span>
        <span>${formatDate(c.incident_date)}</span>
        ${c.country ? `<span>${escapeHtml(c.country)}</span>` : ''}
      </div>
      <p class="case-summary">${escapeHtml(c.public_summary || 'Summary pending admin review.')}</p>
      ${c.evidence_links ? `<div class="evidence-links" style="margin-top:10px;font-size:0.85rem">${linkifyEvidenceLinks(c.evidence_links)}</div>` : ''}
    </article>
  `).join('');
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

function linkifyEvidenceLinks(raw){
  if(!raw || !raw.trim()) return '<em>none provided</em>';
  return raw.split('\n').map(s=>s.trim()).filter(Boolean).map(url=>{
    try{
      const u=new URL(url);
      if(!['http:','https:'].includes(u.protocol)) throw 0;
      return `<a href="${escapeHtml(u.href)}" target="_blank" rel="noopener noreferrer nofollow">${escapeHtml(url)}</a>`;
    }catch{ return escapeHtml(url); }
  }).join('<br>');
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
