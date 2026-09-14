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
const id=new URLSearchParams(location.search).get('id'),$=s=>document.querySelector(s),esc=v=>String(v??'').replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c])),money=v=>'₱'+Number(v||0).toLocaleString('en-PH',{minimumFractionDigits:2});let product=null;
function cart(){try{return JSON.parse(localStorage.getItem('f1_merch_cart')||'[]')}catch{return[]}}function save(c){localStorage.setItem('f1_merch_cart',JSON.stringify(c));$('#cartCount').textContent=c.reduce((s,x)=>s+Number(x.qty||0),0)}
function render(){const sizes=product.sizes?.length?product.sizes:['One Size'];$('#productRoot').innerHTML=`<div class="product-detail"><div class="detail-media">${product.image?`<img src="${esc(product.image)}" alt="${esc(product.name)}">`:'<div class="detail-placeholder">F1</div>'}</div><div class="detail-copy"><span class="eyebrow">${esc(product.team||product.category)} ${product.limited_edition?'· LIMITED':''}</span><h1>${esc(product.name)}</h1><div class="detail-price">${money(product.price)}</div><p>${esc(product.desc)}</p><div class="detail-meta"><span>${product.stock?'IN STOCK':'SOLD OUT'}</span><span>${product.stock<5&&product.stock?'ONLY '+product.stock+' LEFT':''}</span></div><label>Size<select id="sizeSelect">${sizes.map(s=>`<option>${esc(s)}</option>`).join('')}</select></label><div class="quantity"><button id="minus">−</button><input id="qty" value="1" min="1" max="${Math.max(product.stock,1)}" type="number"><button id="plus">+</button></div><div class="detail-actions"><button id="addBtn" class="btn btn-primary" ${product.stock<1?'disabled':''}>Add to cart</button><button id="wishBtn" class="btn btn-ghost">♡ Wishlist</button></div><a class="back-link" href="index.html#shop">← Back to collection</a></div></div><div class="related"><h2>You may also like</h2><div id="relatedGrid" class="product-grid compact"></div></div>`;let q=1;$('#minus').onclick=()=>{q=Math.max(1,q-1);$('#qty').value=q};$('#plus').onclick=()=>{q=Math.min(product.stock,q+1);$('#qty').value=q};$('#addBtn').onclick=()=>{const c=cart(),size=$('#sizeSelect').value,x=c.find(i=>i.id==product.id&&i.size===size);if(x)x.qty=Math.min(x.qty+q,product.stock);else c.push({id:product.id,name:product.name,price:product.price,qty:q,size,image:product.image||''});save(c);location.href='cart.html'};$('#wishBtn').onclick=async()=>{const r=await getCurrentUser();if(!r.authenticated){location.href='login.html?next='+encodeURIComponent(location.href);return}const res=await csrfFetch('api/wishlist.php?action=toggle',{method:'POST',headers:{'Content-Type':'application/json'},credentials:'same-origin',body:JSON.stringify({product_id:product.id})});const d=await res.json();$('#wishBtn').textContent=d.wishlisted?'♥ Wishlisted':'♡ Wishlist'};loadReviews();}
async function loadReviews(){
  try{
    const r=await fetch('api/reviews.php?product_id='+product.id,{credentials:'same-origin',cache:'no-store'});
    const d=await r.json();
    $('#reviewAverage').textContent=d.count?`★ ${Number(d.average).toFixed(1)} / 5 · ${d.count} review${d.count===1?'':'s'}`:'No reviews yet';
    $('#reviewList').innerHTML=d.reviews?.length?d.reviews.map(x=>{
      const rating=Math.max(0,Math.min(5,Number(x.rating)||0));
      const avatar=x.avatar_path?avatarUrl(x.avatar_path):'';
      const avatarMarkup=avatar
        ? `<img class="review-avatar-img" src="${esc(avatar)}" alt="${esc(x.name)} profile photo" loading="lazy">`
        : `<span class="review-avatar-fallback">${esc(initials(x.name))}</span>`;
      return `<article class="review"><div class="review-author"><div class="review-avatar">${avatarMarkup}</div><div><strong>${esc(x.name)}</strong><small>${new Date(x.created_at).toLocaleDateString()}</small></div></div><div class="review-stars" aria-label="${rating} out of 5 stars">${'★'.repeat(rating)}${'☆'.repeat(5-rating)} <span>${rating}/5</span></div><p>${esc(x.comment)}</p></article>`;
    }).join(''):'<div class="empty-state">Be the first to review this product.</div>';

    const u=await getCurrentUser().catch(()=>({}));
    if(u.authenticated){
      $('#reviewForm').hidden=false;
      let rating=Number($('#rating').value)||0;
      const picker=$('#starsInput');
      const value=$('#ratingValue');
      const stars=[...picker.querySelectorAll('.rating-star')];
      const paint=(preview=rating)=>{
        stars.forEach(btn=>{
          const n=Number(btn.dataset.rating);
          btn.classList.toggle('active',n<=preview);
          btn.classList.toggle('preview',preview>0&&n<=preview&&preview!==rating);
          btn.setAttribute('aria-checked',n===rating?'true':'false');
        });
        value.textContent=`${rating} / 5`;
        value.classList.toggle('zero',rating===0);
      };
      const setRating=(n)=>{rating=Math.max(0,Math.min(5,Number(n)||0));$('#rating').value=rating;paint();};
      stars.forEach(btn=>{
        btn.onclick=()=>setRating(Number(btn.dataset.rating));
        btn.onmouseenter=()=>paint(Number(btn.dataset.rating));
        btn.onmouseleave=()=>paint();
        btn.onkeydown=e=>{if(e.key==='ArrowRight'||e.key==='ArrowUp'){e.preventDefault();setRating(Math.min(5,rating+1));}else if(e.key==='ArrowLeft'||e.key==='ArrowDown'){e.preventDefault();setRating(Math.max(0,rating-1));}else if(e.key==='Home'){e.preventDefault();setRating(0);}else if(e.key==='End'){e.preventDefault();setRating(5);}};
      });
      $('#ratingClear').onclick=()=>setRating(0);
      paint();
      $('#reviewForm').onsubmit=async e=>{
        e.preventDefault();
        $('#reviewMsg').textContent='';
        const res=await csrfFetch('api/reviews.php?product_id='+product.id,{method:'POST',headers:{'Content-Type':'application/json'},credentials:'same-origin',body:JSON.stringify({rating,comment:$('#reviewComment').value})});
        const x=await res.json();
        if(!res.ok){$('#reviewMsg').textContent=x.error||'Could not publish review.';return;}
        e.target.reset();
        setRating(0);
        await loadReviews();
      };
    }
  }catch(e){
    $('#reviewMsg').textContent=e.message||'Unable to load reviews.';
  }
}
function avatarUrl(path){
  if(!path)return '';
  const value=String(path).trim();
  if(/^https?:\/\//i.test(value)||value.startsWith('/'))return value;
  return value.replace(/^\.\//,'');
}
function initials(name){
  const parts=String(name||'ME').trim().split(/\s+/).filter(Boolean);
  return parts.slice(0,2).map(x=>x[0]).join('').toUpperCase()||'ME';
}
(async()=>{try{product=await getProducts().then(ps=>ps.find(p=>p.id==id));if(!product)throw Error('Product not found');render();save(cart())}catch(e){$('#productRoot').innerHTML='<div class="empty-state">'+esc(e.message)+'</div>'}})();
