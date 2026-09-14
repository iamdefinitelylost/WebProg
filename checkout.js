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
const KEY='f1_merch_cart',$=s=>document.querySelector(s),money=v=>'₱'+Number(v||0).toLocaleString('en-PH', {
  minimumFractionDigits:2
}
),esc=v=>String(v??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));let cart=[];function load(){try{return JSON.parse(localStorage.getItem(KEY)||'[]')}catch{return[]}}function subtotal(){return cart.reduce((s,x)=>s+Number(x.price)*Number(x.qty),0)}function ship(){return $('input[name="shipping_method"]:checked')?.value==='express'?450:0}function totals(){$('#checkoutSubtotal').textContent=money(subtotal());$('#checkoutShipping').textContent=ship()?money(ship()):'FREE';$('#checkoutTotal').textContent=money(subtotal()+ship())}function render(){if(!cart.length){location.href='cart.html';return}$('#checkoutItems').innerHTML=cart.map(x=>`<div class="checkout-item"><span><b>${esc(x.name)}</b><small>${esc(x.size||'One Size')} · Qty ${x.qty}</small></span><strong>${money(x.price*x.qty)}</strong></div>`).join('');totals()}async function init(){const r=await getCurrentUser();if(!r.authenticated||r.user.role!=='customer'){location.href='login.html?next=checkout.html';return}$('#fullName').value=r.user.name||'';$('#email').value=r.user.email||'';cart=load();try{const ps=await getProducts(),map=new Map(ps.map(p=>[p.id,p]));cart=cart.filter(x=>map.has(Number(x.id))).map(x=>({...x,price:Number(map.get(Number(x.id)).price)}));localStorage.setItem(KEY,JSON.stringify(cart))}catch{}render();document.querySelectorAll('input[name="shipping_method"]').forEach(x=>x.onchange=totals)}$('#checkoutForm').onsubmit=async e=>{e.preventDefault();const btn=$('#placeOrderBtn');btn.disabled=true;btn.textContent='Processing…';const f=new FormData(e.target);const payload={full_name:f.get('full_name').trim(),phone:f.get('phone').trim(),address:f.get('address').trim(),city:f.get('city').trim(),province:f.get('province').trim(),postal_code:f.get('postal_code').trim(),shipping_method:f.get('shipping_method'),payment_method:f.get('payment_method'),items:cart.map(x=>({id:Number(x.id),qty:Number(x.qty),size:x.size||''}))};try{const r=await csrfFetch('api/orders.php?action=create',{method:'POST',headers:{'Content-Type':'application/json'},credentials:'same-origin',body:JSON.stringify(payload)}),d=await r.json();if(!r.ok)throw Error(d.error||'Could not place order.');localStorage.removeItem(KEY);location.href='success.html?order='+encodeURIComponent(d.order.order_number)}catch(err){$('#checkoutMessage').textContent=err.message;btn.disabled=false;btn.textContent='Place order'}};init();
