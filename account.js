async function csrfFetch(url,options= {
}
) {
  const token=await getCsrfToken();
  options.credentials='same-origin';
  options.headers= {
    ...(options.headers|| {
    }
    ),'X-CSRF-Token':token
  }
  ;
  return fetch(url,options);
}
const $=s=>document.querySelector(s),esc=v=>String(v??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c])),money=v=>'₱'+Number(v||0).toLocaleString('en-PH',{minimumFractionDigits:2}),status=['pending','processing','shipped','out_for_delivery','delivered'];let products=[];

function avatarUrl(path){
  if(!path) return '';
  const value=String(path).trim();
  if(/^https?:\/\//i.test(value) || value.startsWith('/')) return value;
  return value.replace(/^\.\//,'');
}

function setAvatar(path){
  const box=$('#avatarPreview');
  if(!box) return;
  const url=avatarUrl(path);
  if(url){
    box.innerHTML=`<img src="${esc(url)}" alt="Profile photo">`;
    box.classList.add('has-avatar');
  }else{
    box.textContent='ME';
    box.classList.remove('has-avatar');
  }
}

function previewSelectedAvatar(file){
  if(!file) return;
  if(!file.type.startsWith('image/')) return;
  const reader=new FileReader();
  reader.onload=e=>setAvatar(e.target.result);
  reader.readAsDataURL(file);
}

function timeline(s){const idx=status.indexOf(s);return `<div class="timeline">${status.map((x,i)=>`<div class="step ${i<=idx?'done':''}"><span>${i<=idx?'✓':''}</span><small>${x.replaceAll('_',' ')}</small></div>`).join('')}</div>`}
async function orders(){const r=await fetch('api/orders.php?action=my',{credentials:'same-origin'}),d=await r.json();$('#orderCount').textContent=`${d.orders.length} order${d.orders.length===1?'':'s'}`;$('#ordersList').innerHTML=d.orders.length?d.orders.map(o=>`<article class="order-card"><div class="order-top"><div><strong>${esc(o.order_number)}</strong><small>${new Date(o.created_at).toLocaleString()}</small></div><b>${money(o.total)}</b></div><p>${esc(o.item_summary)}</p>${timeline(o.status)}<details><summary>Order details</summary><p><b>Ship to:</b> ${esc(o.address)}, ${esc(o.city)}, ${esc(o.province)} ${esc(o.postal_code)}</p><p><b>Phone:</b> ${esc(o.phone)} · <b>Payment:</b> ${esc(o.payment_method==='gcash'?'GCash / E-wallet':'Cash on Delivery')}</p></details></article>`).join(''):'<div class="empty-state">No orders yet. <a href="index.html#shop">Start shopping →</a></div>'}
async function wishlist(){const r=await getCurrentUser();if(!r.authenticated)return;const wr=await fetch('api/wishlist.php?action=list'),wd=await wr.json();const set=new Set(wd.items);const ps=products.filter(p=>set.has(p.id));$('#wishlistGrid').innerHTML=ps.length?ps.map(p=>`<article class="product-card"><a class="product-media" href="product.html?id=${p.id}">${p.image?`<img class="product-img" src="${esc(p.image)}">`:'<div class="product-img placeholder">F1</div>'}</a><div class="product-info"><small>${esc(p.team||p.category)}</small><h3>${esc(p.name)}</h3><strong>${money(p.price)}</strong><a class="btn btn-primary btn-small" href="product.html?id=${p.id}">View product</a></div></article>`).join(''):'<div class="empty-state">Your wishlist is empty.</div>'}

(async()=>{
  const r=await getCurrentUser();
  if(!r.authenticated||r.user.role!=='customer'){location.href='login.html';return}

  $('#name').value=r.user.name;
  $('#email').value=r.user.email;
  $('#favoriteTeam').value=r.user.favorite_team||'';
  $('#bio').value=r.user.bio||'';
  $('#profileHeading').textContent=r.user.name;
  setAvatar(r.user.avatar_path);

  $('#avatarInput').addEventListener('change',e=>previewSelectedAvatar(e.target.files[0]));

  products=await getProducts();
  orders();
  wishlist();

  $('#profileForm').onsubmit=async e=>{
    e.preventDefault();
    const fd=new FormData();
    fd.append('name',$('#name').value.trim());
    fd.append('favorite_team',$('#favoriteTeam').value.trim());
    fd.append('bio',$('#bio').value.trim());
    if($('#avatarInput').files[0]) fd.append('avatar',$('#avatarInput').files[0]);

    try{
      const res=await csrfFetch('api/auth.php?action=profile',{method:'POST',credentials:'same-origin',body:fd,cache:'no-store'});
      const raw=await res.text();
      let d={};
      try{d=raw.trim()?JSON.parse(raw):{};}catch(_){throw new Error('Authentication API returned invalid JSON.');}
      if(!res.ok || !d.success) throw new Error(d.error||'Could not save profile.');

      if(d.user){
        $('#name').value=d.user.name||'';
        $('#profileHeading').textContent=d.user.name||'Your profile';
        setAvatar(d.user.avatar_path);
      }
      $('#avatarInput').value='';
      $('#profileMessage').textContent='Profile saved.';
    }catch(err){
      $('#profileMessage').textContent=err.message||'Could not save.';
    }
  };

  const logoutBtn=$('#logoutBtn');
  if(logoutBtn) logoutBtn.onclick=async()=>{await logoutUser();location.href='index.html'};
})();
