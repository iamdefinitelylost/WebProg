const KEY = 'f1_merch_cart', $ = s => document.querySelector(s), money = v => '₱' + Number(v || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 2
}), esc = v => String(v ?? '').replace(/[&<>'"]/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
}[c]));
function load() {
    try {
        return JSON.parse(localStorage.getItem(KEY) || '[]');
    }
    catch {
        return [];
    }
}
function save(c) {
    localStorage.setItem(KEY, JSON.stringify(c));
    render();
}
function render() {
    const c = load();
    $('#cartCount').textContent = c.reduce((s, x) => s + Number(x.qty), 0);
    $('#cartItems').innerHTML = c.length ? c.map((x, i) => `<article class="cart-item"><div class="cart-thumb">${x.image ? `<img src="${esc(x.image)}">` : 'F1'}</div><div class="cart-item-info"><small>${esc(x.size || 'One Size')}</small><h3>${esc(x.name)}</h3><strong>${money(x.price)}</strong><div class="qty"><button data-act="minus" data-i="${i}">−</button><span>${x.qty}</span><button data-act="plus" data-i="${i}">+</button><button class="remove" data-act="remove" data-i="${i}">Remove</button></div></div><strong>${money(x.price * x.qty)}</strong></article>`).join('') : '<div class="empty-state"><h3>Your cart is empty.</h3><a href="index.php#shop">Browse the collection →</a></div>';
    const t = c.reduce((s, x) => s + x.price * x.qty, 0);
    $('#subtotal').textContent = money(t);
    $('#total').textContent = money(t);
    document.querySelectorAll('[data-act]').forEach(b => b.onclick = () => {
        const a = load(), i = +b.dataset.i;
        if (b.dataset.act === 'remove')
            a.splice(i, 1);
        if (b.dataset.act === 'minus')
            a[i].qty = Math.max(1, a[i].qty - 1);
        if (b.dataset.act === 'plus')
            a[i].qty++;
        save(a);
    });
}
$('#clearCart').onclick = () => {
    localStorage.removeItem(KEY);
    render();
};
$('#checkoutBtn').onclick = async () => {
    if (!load().length)
        return;
    const r = await getCurrentUser();
    if (!r.authenticated) {
        location.href = 'login.php?next=checkout.php';
        return;
    }
    location.href = 'checkout.php';
};
(async () => {
    try {
        const ps = await getProducts();
        const map = new Map(ps.map(p => [p.id, p]));
        let c = load().filter(x => map.has(Number(x.id))).map(x => ({
            ...x, price: Number(map.get(Number(x.id)).price), image: map.get(Number(x.id)).image || x.image, size: x.size || map.get(Number(x.id)).sizes?.[0] || 'One Size'
        }));
        localStorage.setItem(KEY, JSON.stringify(c));
    }
    catch {
    }
    render();
})();
