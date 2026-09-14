async function csrfFetch(url, options = {}) {
    const token = await getCsrfToken();
    options.credentials = 'same-origin';
    options.headers = {
        ...(options.headers || {}),
        'X-CSRF-Token': token
    };
    return fetch(url, options);
}
const $ = s => document.querySelector(s), esc = v => String(v ?? '').replace(/[&<>'"]/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
}[c])), money = v => '₱' + Number(v || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 2, maximumFractionDigits: 2
});
let products = [], category = 'all', search = '';
const teams = ['Ferrari', 'McLaren', 'Red Bull Racing', 'Mercedes', 'Aston Martin', 'Alpine', 'Williams', 'Haas', 'Racing Bulls', 'Audi', 'Cadillac'];
// Keep the header's red active underline synchronized with the section the
// customer selects. Teams and Story live on the storefront, so their links
// use hashes and should remain active after navigation without a page reload.
function updateNavActive() {
    const links = document.querySelectorAll('.nav-links a');
    if (!links.length)
        return;
    const path = location.pathname.split('/').pop() || 'index.html';
    const hash = location.hash;
    let active = '';
    if (path === 'index.html' || path === '') {
        if (hash === '#teams')
            active = 'teams';
        else if (hash === '#story')
            active = 'story';
        else if (hash === '#lineup' || hash === '')
            active = 'shop';
    }
    links.forEach(a => a.classList.remove('active'));
    links.forEach(a => {
        const href = a.getAttribute('href') || '';
        if (active === 'teams' && href.endsWith('#teams'))
            a.classList.add('active');
        else if (active === 'story' && href.endsWith('#story'))
            a.classList.add('active');
        else if (active === 'shop' && (href === '#lineup' || href.endsWith('index.html#lineup')))
            a.classList.add('active');
    });
}
document.querySelectorAll('.nav-links a').forEach(a => {
    a.addEventListener('click', () => {
        const href = a.getAttribute('href') || ''; // Let the browser perform the navigation, then synchronize the underline.
        if (href.includes('#teams') || href.includes('#story') || href === '#lineup') {
            requestAnimationFrame(updateNavActive);
        }
    });
});
window.addEventListener('hashchange', updateNavActive);
window.addEventListener('pageshow', updateNavActive);
function loadCart() {
    try {
        return JSON.parse(localStorage.getItem('f1_merch_cart') || '[]');
    }
    catch {
        return [];
    }
}
function saveCart(c) {
    localStorage.setItem('f1_merch_cart', JSON.stringify(c));
    updateCount();
}
function updateCount() {
    const el = $('#cartCount');
    if (el)
        el.textContent = loadCart().reduce((s, x) => s + Number(x.qty || 0), 0);
}
function addCart(p) {
    if (!p || Number(p.stock) < 1)
        return;
    const c = loadCart();
    let x = c.find(i => Number(i.id) === Number(p.id) && i.size === (p.sizes?.[0] || 'One Size'));
    if (x)
        x.qty = Math.min(Number(x.qty) + 1, Number(p.stock));
    else
        c.push({
            id: p.id, name: p.name, price: Number(p.price), qty: 1, image: p.image || '', size: p.sizes?.[0] || 'One Size'
        });
    saveCart(c);
    const old = document.activeElement;
    alert(`${p.name} added to cart.`);
    old?.blur();
}
async function toggleWish(btn) {
    try {
        const r = await getCurrentUser();
        if (!r.authenticated) {
            location.href = 'login.html?next=' + encodeURIComponent(location.href);
            return;
        }
        const res = await csrfFetch('api/wishlist.php?action=toggle', {
            method: 'POST', headers: {
                'Content-Type': 'application/json'
            }, credentials: 'same-origin', body: JSON.stringify({
                product_id: Number(btn.dataset.id)
            })
        });
        const d = await res.json();
        btn.textContent = d.wishlisted ? '♥' : '♡';
    }
    catch (e) {
        alert(e.message);
    }
}
function card(p, i) {
    return `<article class="card" data-id="${p.id}"><a href="product.html?id=${p.id}" class="card-media">${p.limited_edition ? '<span class="badge">LIMITED</span>' : ''}${p.featured ? '<span class="featured">FEATURED</span>' : ''}${p.image ? `<img class="product-image" src="${esc(p.image)}" alt="${esc(p.name)}">` : `<svg class="icon" viewBox="0 0 200 200"><rect width="200" height="200" rx="36" fill="#14161C"/><circle cx="168" cy="32" r="7" fill="#E2231A"/><text x="100" y="128" text-anchor="middle" font-family="Space Grotesk" font-weight="700" font-size="110" fill="#fff">F1</text></svg>`}<span class="grid-pos">${String(i + 1).padStart(2, '0')}</span></a><div class="card-body"><div class="product-meta"><span>${esc(p.team || p.category)}</span><span>${esc(p.category)}</span></div><div class="name">${esc(p.name)}</div><div class="desc">${esc(p.desc)}</div><div class="card-foot"><span class="product-price">${money(p.price)}</span><span class="product-stock ${p.stock < 5 ? 'low' : ''}">${p.stock ? `${p.stock} left` : 'Sold out'}</span><a class="view" href="product.html?id=${p.id}">View</a><button class="wish" data-id="${p.id}" aria-label="Wishlist">♡</button><button class="add" data-id="${p.id}" ${Number(p.stock) < 1 ? 'disabled' : ''}>+</button></div></div></article>`;
}
function render() {
    const q = search.toLowerCase();
    const visible = products.filter(p => (category === 'all' || p.category === category) && (!q || `${p.name} ${p.desc} ${p.team || ''} ${p.category}`.toLowerCase().includes(q)));
    const grid = $('#lineupGrid');
    grid.innerHTML = visible.length ? visible.map(card).join('') : '<div class="admin-empty">No pieces match your search.</div>';
    const sub = $('#lineupSubhead');
    sub.textContent = `${visible.length} piece${visible.length === 1 ? '' : 's'} on the grid this drop.`;
    document.querySelectorAll('.lineup-filter').forEach(b => b.classList.toggle('active', b.dataset.category === category));
    document.querySelectorAll('.add').forEach(b => b.onclick = () => addCart(products.find(p => Number(p.id) === Number(b.dataset.id))));
    document.querySelectorAll('.wish').forEach(b => b.onclick = () => toggleWish(b));
}
function renderTeams() {
    $('#teamGrid').innerHTML = teams.map(t => `<a class="team-tile" href="drops.html?team=${encodeURIComponent(t)}"><span>${esc(t)}</span><small>SHOP COLLECTION →</small></a>`).join('');
}
$('#searchInput')?.addEventListener('input', e => {
    search = e.target.value.trim();
    render();
});
document.querySelectorAll('.lineup-filter').forEach(b => b.onclick = () => {
    category = b.dataset.category;
    render();
});
document.querySelectorAll('.foot-link[data-filter]').forEach(a => a.onclick = e => {
    e.preventDefault();
    category = a.dataset.filter;
    document.getElementById('lineup').scrollIntoView({
        behavior: 'smooth'
    });
    render();
});
$('#menuBtn')?.addEventListener('click', () => {
    const open = $('#navLinks').classList.toggle('open');
    $('#menuBtn').setAttribute('aria-expanded', open);
});
$('#signupForm')?.addEventListener('submit', e => {
    e.preventDefault();
    $('#signupMsg').textContent = 'You are on the list — drop alerts are enabled.';
    e.target.reset();
});
// auth-nav.js owns the account link so profile photos/initials stay consistent
// across the storefront. Do not overwrite it here after authentication resolves.
updateCount();
const modalContent = {
    shipping: ['Shipping',
        `<p>Standard delivery runs 3–5 business days. Express delivery is available at checkout for priority shipping.</p>`],
    returns: ['Returns',
        `<p>Unworn items in original packaging can be returned within 30 days of delivery.</p>`],
    contact: ['Contact',
        `<p>Questions about an order, drop, or product? Email hello@f1merch.example.</p>`]
};
document.querySelectorAll('[data-modal]').forEach(a => a.onclick = e => {
    e.preventDefault();
    const x = modalContent[a.dataset.modal];
    if (!x)
        return;
    $('#modalTitle').textContent = x[0];
    $('#modalBody').innerHTML = x[1];
    $('#modalOverlay').hidden = false;
});
$('#modalClose')?.addEventListener('click', () => $('#modalOverlay').hidden = true);
$('#modalOverlay')?.addEventListener('click', e => {
    if (e.target.id === 'modalOverlay')
        e.target.hidden = true;
});
(async () => {
    try {
        products = await getProducts();
        render();
        renderTeams();
    }
    catch (e) {
        $('#lineupGrid').innerHTML = `<div class="admin-empty">Could not load products. ${esc(e.message)}</div>`;
    }
})();
updateNavActive();
