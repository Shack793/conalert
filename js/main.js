function formatUSD(n) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n || 0);
}

async function loadStats() {
  const statCases = document.getElementById('stat-cases');
  const statAmount = document.getElementById('stat-amount');
  if (!statCases || !statAmount) return;

  try {
    const res = await fetch('/api/stats.php');
    if (!res.ok) throw new Error('stats request failed');
    const data = await res.json();
    statCases.textContent = data.published_cases;
    statAmount.textContent = formatUSD(data.total_amount_usd);
  } catch (err) {
    statCases.textContent = '—';
    statAmount.textContent = '—';
  }
}

loadStats();
