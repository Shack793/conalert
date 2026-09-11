async function loadFooter(){
  try{
    const res = await fetch('/api/settings.php');
    if(!res.ok) return;
    const data = await res.json();
    // email
    const emailEl = document.getElementById('contact-email');
    if(emailEl && data.contact_email){
      emailEl.textContent = data.contact_email;
      emailEl.href = 'mailto:'+data.contact_email;
      if(emailEl.tagName.toLowerCase()!=='a'){
        // if span, replace with anchor
        const a=document.createElement('a');
        a.id='contact-email';
        a.href='mailto:'+data.contact_email;
        a.textContent=data.contact_email;
        emailEl.replaceWith(a);
      }
    }
    // align
    const footer=document.getElementById('site-footer');
    if(footer && data.footer_align){
      footer.classList.remove('footer--align-left','footer--align-center','footer--align-space-between');
      footer.classList.add('footer--align-'+data.footer_align);
    }
    // social
    const socialEl=document.getElementById('footer-social');
    if(socialEl && Array.isArray(data.social_links)){
      const iconMap={
        instagram:'icon-instagram', twitter:'icon-twitter', x:'icon-x',
        facebook:'icon-facebook', youtube:'icon-youtube', tiktok:'icon-tiktok', linkedin:'icon-linkedin'
      };
      socialEl.innerHTML=data.social_links.map(s=>{
        const icon=iconMap[s.platform.toLowerCase()]||'icon-twitter';
        const safeUrl=s.url.replace(/"/g,'&quot;');
        const label=(s.label||s.platform).replace(/</g,'&lt;');
        return `<a href="${safeUrl}" target="_blank" rel="noopener noreferrer" aria-label="${label}"><svg><use href="/img/icons.svg#${icon}"></use></svg></a>`;
      }).join('');
    }
  }catch(e){}
}
document.addEventListener('DOMContentLoaded', loadFooter);
