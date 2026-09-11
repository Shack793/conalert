const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

async function apiFetch(url, options={}){
  const res = await fetch(url, { ...options, headers:{...(options.headers||{}), 'X-CSRF-Token': csrfToken}});
  if(res.status===401) window.location.href='/admin/login.php';
  return res;
}

function esc(s){ const d=document.createElement('div'); d.textContent=s??''; return d.innerHTML; }

async function load(){
  const res = await apiFetch('/admin/api/settings.php');
  const data = await res.json();
  // settings is object with key->value or with wrapper
  const settings = data.settings || {};
  const contact = settings.contact_email?.value || settings.contact_email || 'cases@conalert.org';
  const align = settings.footer_align?.value || settings.footer_align || 'space-between';
  document.getElementById('contact-email-input').value = contact;
  document.getElementById('footer-align').value = align;
  renderSocial(data.social_links || []);
}

function renderSocial(list){
  const el=document.getElementById('social-list');
  if(!list.length){ el.innerHTML='<p style="color:#666;font-size:13px;">No social links yet.</p>'; return; }
  el.innerHTML=`<table class="user-table"><thead><tr><th>Platform</th><th>URL</th><th>Order</th><th>Actions</th></tr></thead><tbody>`+
    list.map(s=>`<tr>
      <td>${esc(s.platform)}<br><small>${esc(s.label||'')}</small></td>
      <td style="word-break:break-all;">${esc(s.url||'(blank)')}</td>
      <td>${s.sort_order}</td>
      <td><button class="btn danger" data-del="${s.id}">Delete</button></td>
    </tr>`).join('')+`</tbody></table>`;
  el.querySelectorAll('button[data-del]').forEach(b=>{
    b.addEventListener('click', async()=>{
      if(!confirm('Delete this social link?')) return;
      const r=await apiFetch('/admin/api/social-links.php?id='+b.dataset.del, {method:'DELETE'});
      const d=await r.json();
      const msg=document.getElementById('social-message');
      if(!r.ok){ msg.textContent=d.error; msg.className='form-message error'; } else { msg.textContent='Deleted'; msg.className='form-message success'; load(); }
    });
  });
}

document.getElementById('save-settings').addEventListener('click', async()=>{
  const msg=document.getElementById('settings-message');
  const payload={
    contact_email: document.getElementById('contact-email-input').value.trim(),
    footer_align: document.getElementById('footer-align').value,
  };
  const res=await apiFetch('/admin/api/settings.php',{method:'PATCH', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)});
  const data=await res.json();
  if(!res.ok){ msg.textContent=data.error; msg.className='form-message error'; return; }
  msg.textContent='Saved.'; msg.className='form-message success';
});

document.getElementById('add-social').addEventListener('click', async()=>{
  const msg=document.getElementById('social-message');
  const payload={
    platform: document.getElementById('new-platform').value.trim(),
    url: document.getElementById('new-url').value.trim(),
    label: document.getElementById('new-label').value.trim(),
    sort_order: 99
  };
  if(!payload.platform){ msg.textContent='Platform required'; msg.className='form-message error'; return; }
  // try POST, if exists it will do upsert via PATCH path alternative: use settings bulk
  const res=await apiFetch('/admin/api/social-links.php',{method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)});
  const data=await res.json();
  if(!res.ok){
    // try update via settings PATCH upsert
    const res2=await apiFetch('/admin/api/settings.php',{method:'PATCH', headers:{'Content-Type':'application/json'}, body:JSON.stringify({social_links:[payload]})});
    const d2=await res2.json();
    if(!res2.ok){ msg.textContent=d2.error; msg.className='form-message error'; return; }
  }
  msg.textContent='Saved.'; msg.className='form-message success';
  document.getElementById('new-platform').value=''; document.getElementById('new-url').value=''; document.getElementById('new-label').value='';
  load();
});

load();
