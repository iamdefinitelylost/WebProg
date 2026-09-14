const $ = s => document.querySelector(s), esc = v => String(v ?? '').replace(/[&<>'"]/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
}[c])), money = v => '₱' + Number(v || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 2
}), tabs = document.querySelectorAll('.admin-tabs button');
let products = [], orders = [], customers = [], reviews = [];
async function api(url, opt = {}) {
    const options = {
        credentials: 'same-origin',
        ...opt
    };
    if ((options.method || 'GET').toUpperCase() !== 'GET') {
        options.headers = {
            ...(options.headers || {}),
            'X-CSRF-Token': await getCsrfToken()
        };
    }
    const r = await fetch(url, options);
    const d = await r.json().catch(() => ({
        error: 'Invalid server response'
    }));
    if (!r.ok)
        throw Error(d.error || 'Request failed');
    return d;
}
function csv(rows, name) {
    if (!rows.length)
        return;
    const keys = Object.keys(rows[0]);
    const q = v => '"' + String(v ?? '').replace(/"/g, '""') + '"';
    const blob = new Blob([keys.map(q).join(',') + '\n' + rows.map(r => keys.map(k => q(r[k])).join(',')).join('\n')], {
        type: 'text/csv;charset=utf-8'
    });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = name;
    a.click();
    URL.revokeObjectURL(a.href);
}
async function loadProducts() {
    products = await getProducts();
    $('#productList').innerHTML = products.map(p => `<article class="admin-product"><div class="admin-thumb">${p.image ? `<img src="${esc(p.image)}">` : 'F1'}</div><div><strong>${esc(p.name)}</strong><small>${esc(p.team || p.category)} · ${money(p.price)}</small><small class="${p.stock < 5 ? 'low' : ''}">${p.stock} in stock ${p.limited_edition ? '· LIMITED' : ''}${p.featured ? ' · FEATURED' : ''}</small></div><div><button class="btn btn-small edit" data-id="${p.id}">Edit</button><button class="btn btn-small danger delete" data-id="${p.id}">Delete</button></div></article>`).join('');
    document.querySelectorAll('.edit').forEach(b => b.onclick = () => edit(products.find(p => p.id == b.dataset.id)));
    document.querySelectorAll('.delete').forEach(b => b.onclick = async () => {
        if (confirm('Delete this product?')) {
            await deleteProduct(b.dataset.id);
            loadProducts();
            loadDashboard();
        }
    });
}
function edit(p) {
    $('#productId').value = p.id;
    $('#fieldName').value = p.name;
    $('#fieldDesc').value = p.desc;
    $('#fieldPrice').value = p.price;
    $('#fieldStock').value = p.stock;
    $('#fieldCategory').value = p.category;
    $('#fieldTeam').value = p.team || '';
    $('#fieldSizes').value = (p.sizes || []).join(',');
    $('#fieldLimited').checked = p.limited_edition;
    $('#fieldFeatured').checked = p.featured;
    $('#formTitle').textContent = 'Edit product';
    window.scrollTo({
        top: document.querySelector('#productsTab').offsetTop - 30, behavior: 'smooth'
    });
}
$('#cancelEdit').onclick = () => {
    $('#productForm').reset();
    $('#productId').value = '';
    $('#formTitle').textContent = 'Add product';
};
$('#productForm').onsubmit = async (e) => {
    e.preventDefault();
    const fd = new FormData();
    [['name',
            '#fieldName'],
        ['desc',
            '#fieldDesc'],
        ['price',
            '#fieldPrice'],
        ['stock',
            '#fieldStock'],
        ['category',
            '#fieldCategory'],
        ['team',
            '#fieldTeam'],
        ['sizes',
            '#fieldSizes']].forEach(([k, s]) => fd.append(k, $(s).value));
    fd.append('limited_edition', $('#fieldLimited').checked ? '1' : '0');
    fd.append('featured', $('#fieldFeatured').checked ? '1' : '0');
    if ($('#fieldImage').files[0])
        fd.append('image', $('#fieldImage').files[0]);
    try {
        if ($('#productId').value)
            await updateProduct($('#productId').value, fd);
        else
            await addProduct(fd);
        $('#productForm').reset();
        $('#productId').value = '';
        $('#formTitle').textContent = 'Add product';
        $('#productMsg').textContent = 'Saved.';
        loadProducts();
        loadDashboard();
    }
    catch (e) {
        $('#productMsg').textContent = e.message;
    }
};
async function loadDashboard() {
    const d = await api('api/orders.php?action=analytics');
    const s = d.stats;
    const pending = (d.status.find(x => x.status === 'pending') || {}).count || 0;
    $('#statGrid').innerHTML = `<div class="stat"><small>SALES</small><b>${money(s.sales)}</b></div><div class="stat"><small>ORDERS</small><b>${s.orders}</b></div><div class="stat"><small>CUSTOMERS WITH ORDERS</small><b>${s.customers}</b></div><div class="stat"><small>LOW STOCK</small><b>${d.inventory.filter(x => x.stock < 5).length}</b></div><div class="stat"><small>PENDING</small><b>${pending}</b></div>`;
    const max = Math.max(1, ...d.monthly.map(x => Number(x.sales)));
    $('#salesChart').innerHTML = d.monthly.length ? d.monthly.map(x => `<div class="bar-row"><small>${x.month}</small><div><i style="width:${Number(x.sales) / max * 100}%"></i></div><b>${money(x.sales)}</b></div>`).join('') : '<div class="empty-state">No sales yet.</div>';
    $('#inventoryWatch').innerHTML = d.inventory.slice(0, 8).map(x => `<div class="inventory-row"><span>${esc(x.name)}</span><b class="${x.stock < 5 ? 'low' : ''}">${x.stock}</b></div>`).join('');
}
const labels = {
    pending: 'Pending',
    processing: 'Processing',
    shipped: 'Shipped',
    out_for_delivery: 'Out for delivery',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
};
async function loadOrders() {
    const d = await api('api/orders.php?action=admin_list');
    orders = d.orders;
    renderOrders();
}
function renderOrders() {
    const search = ($('#orderSearch')?.value || '').toLowerCase(), status = $('#orderStatus')?.value || 'all';
    const list = orders.filter(o => (status === 'all' || o.status === status) && (!search || `${o.order_number} ${o.email} ${o.customer_name} ${o.item_summary}`.toLowerCase().includes(search)));
    $('#ordersAdminList').innerHTML = list.length ? list.map(o => `<article class="admin-order"><div><strong>${esc(o.order_number)}</strong><small>${esc(o.email)} · ${new Date(o.created_at).toLocaleString()}</small><p>${esc(o.item_summary)}</p><small>${esc(o.customer_name)} · ${esc(o.phone)} · ${esc(o.address)}, ${esc(o.city)}, ${esc(o.province)} ${esc(o.postal_code)}</small></div><div><b>${money(o.total)}</b><select class="status-select" data-id="${o.id}">${Object.entries(labels).map(([k, v]) => `<option value="${k}" ${o.status === k ? 'selected' : ''}>${v}</option>`).join('')}</select></div></article>`).join('') : '<div class="empty-state">No matching orders.</div>';
    document.querySelectorAll('.status-select').forEach(s => s.onchange = async () => {
        await api('api/orders.php?action=admin_update', {
            method: 'POST', headers: {
                'Content-Type': 'application/json'
            }, body: JSON.stringify({
                id: s.dataset.id, status: s.value
            })
        });
        loadOrders();
        loadDashboard();
    });
}
async function loadCustomers() {
    const d = await api('api/auth.php?action=users');
    customers = d.users.filter(u => u.role === 'customer');
    renderCustomers();
}
function renderCustomers() {
    const search = ($('#customerSearch')?.value || '').toLowerCase(), status = $('#customerStatus')?.value || 'all';
    const list = customers.filter(u => (status === 'all' || (status === 'banned' && u.banned) || (status === 'active' && !u.banned)) && (!search || `${u.name} ${u.email}`.toLowerCase().includes(search)));
    $('#userList').innerHTML = list.map(u => `<article class="admin-product"><div class="admin-thumb">${u.avatar_path ? `<img src="${esc(u.avatar_path)}">` : esc((u.name || 'U')[0])}</div><div><strong>${esc(u.name)}</strong><small>${esc(u.email)} · Joined ${new Date(u.created_at).toLocaleDateString()}</small><small>${u.order_count || 0} orders · ${money(u.order_total || 0)} spent</small><small class="${u.banned ? 'low' : ''}">${u.banned ? 'BANNED' : 'ACTIVE'}</small></div><button class="btn btn-small ${u.banned ? '' : 'danger'} ban" data-id="${u.id}" data-ban="${u.banned ? '0' : '1'}">${u.banned ? 'Unban' : 'Ban'}</button></article>`).join('') || '<div class="empty-state">No matching customers.</div>';
    document.querySelectorAll('.ban').forEach(b => b.onclick = async () => {
        const action = b.dataset.ban === '1' ? 'ban' : 'unban';
        if (confirm(`${action === 'ban' ? 'Ban' : 'Unban'} this customer?`)) {
            await api('api/auth.php?action=ban', {
                method: 'POST', headers: {
                    'Content-Type': 'application/json'
                }, body: JSON.stringify({
                    id: Number(b.dataset.id), ban: b.dataset.ban === '1'
                })
            });
            loadCustomers();
        }
    });
}
async function loadReviews() {
    const d = await api('api/reviews.php?action=admin_list');
    reviews = d.reviews;
    renderReviews();
}
function renderReviews() {
    const search = ($('#reviewSearch')?.value || '').toLowerCase(), status = $('#reviewStatus')?.value || 'all';
    const list = reviews.filter(r => (status === 'all' || (status === 'visible' && Number(r.approved) === 1) || (status === 'hidden' && Number(r.approved) === 0)) && (!search || `${r.name} ${r.email} ${r.product_name} ${r.comment}`.toLowerCase().includes(search)));
    $('#reviewAdminList').innerHTML = list.length ? list.map(r => `<article class="admin-review"><div class="review-avatar">${r.avatar_path ? `<img src="${esc(r.avatar_path)}">` : esc((r.name || 'U')[0])}</div><div class="review-body"><strong>${esc(r.name)}</strong><small>${esc(r.email)} · ${esc(r.product_name)} · ${new Date(r.created_at).toLocaleDateString()}</small><div class="review-stars">${'★'.repeat(Number(r.rating))}${'☆'.repeat(5 - Number(r.rating))} <span>${r.rating}/5</span></div><p>${esc(r.comment)}</p></div><div class="review-actions"><button class="btn btn-small review-toggle" data-id="${r.id}" data-approved="${Number(r.approved) ? '0' : '1'}">${Number(r.approved) ? 'Hide' : 'Show'}</button><button class="btn btn-small danger review-delete" data-id="${r.id}">Delete</button></div></article>`).join('') : '<div class="empty-state">No matching reviews.</div>';
    document.querySelectorAll('.review-toggle').forEach(b => b.onclick = async () => {
        await api('api/reviews.php?action=admin_update', {
            method: 'POST', headers: {
                'Content-Type': 'application/json'
            }, body: JSON.stringify({
                id: Number(b.dataset.id), approved: b.dataset.approved === '1'
            })
        });
        loadReviews();
    });
    document.querySelectorAll('.review-delete').forEach(b => b.onclick = async () => {
        if (confirm('Delete this review permanently?')) {
            await api('api/reviews.php?action=admin_delete', {
                method: 'POST', headers: {
                    'Content-Type': 'application/json'
                }, body: JSON.stringify({
                    id: Number(b.dataset.id)
                })
            });
            loadReviews();
        }
    });
}
async function loadReports() {
    const d = await api('api/orders.php?action=analytics');
    const status = d.status.map(x => `<div class="report-card"><small>${esc(labels[x.status] || x.status)}</small><b>${x.count}</b></div>`).join('');
    const top = d.top_customers.map(x => `<div class="inventory-row"><span>${esc(x.name)}<small>${esc(x.email)}</small></span><b>${money(x.spent)}</b></div>`).join('');
    $('#reportSummary').innerHTML = `<div><h3>Order status</h3><div class="report-grid">${status}</div></div><div><h3>Top customers by spend</h3>${top || '<div class="empty-state">No customer order data.</div>'}</div>`;
}
async function exportData(type) {
    if (type === 'orders') {
        const d = await api('api/orders.php?action=admin_list');
        csv(d.orders, 'f1-merch-orders.csv');
    }
    if (type === 'customers') {
        const d = await api('api/auth.php?action=users');
        csv(d.users.map(({ id, name, email, role, banned, created_at, order_count, order_total }) => ({
            id, name, email, role, banned, created_at, order_count, order_total
        })), 'f1-merch-customers.csv');
    }
    if (type === 'products') {
        const p = await getProducts();
        csv(p.map(x => ({
            id: x.id, name: x.name, category: x.category, team: x.team, price: x.price, stock: x.stock, limited_edition: x.limited_edition, featured: x.featured
        })), 'f1-merch-products.csv');
    }
    if (type === 'reviews') {
        const d = await api('api/reviews.php?action=admin_list');
        csv(d.reviews.map(x => ({
            id: x.id, product: x.product_name, customer: x.name, email: x.email, rating: x.rating, comment: x.comment, approved: x.approved, created_at: x.created_at
        })), 'f1-merch-reviews.csv');
    }
}
tabs.forEach(t => t.onclick = () => {
    tabs.forEach(x => x.classList.toggle('active', x === t));
    document.querySelectorAll('.admin-tab').forEach(s => s.hidden = s.id !== t.dataset.tab + 'Tab');
    ({
        dashboard: loadDashboard, products: loadProducts, orders: loadOrders, customers: loadCustomers, reviews: loadReviews, reports: loadReports
    }[t.dataset.tab] || (() => {
    }))();
});
$('#resetProducts').onclick = async () => {
    if (confirm('Reset the catalog?')) {
        await api('api/products.php?action=reset', {
            method: 'POST'
        });
        loadProducts();
        loadDashboard();
    }
};
$('#refreshOrders').onclick = loadOrders;
$('#refreshCustomers').onclick = loadCustomers;
$('#refreshReviews').onclick = loadReviews;
['orderSearch', 'orderStatus'].forEach(id => document.getElementById(id)?.addEventListener('input', renderOrders));
['customerSearch', 'customerStatus', 'reviewSearch', 'reviewStatus'].forEach(id => document.getElementById(id)?.addEventListener('input', id.startsWith('review') ? renderReviews : renderCustomers));
[['exportOrders', 'orders'], ['exportCustomers', 'customers'], ['exportProducts', 'products'], ['exportReviews', 'reviews']].forEach(([id, type]) => document.getElementById(id)?.addEventListener('click', () => exportData(type)));
$('#adminLogout').onclick = async () => {
    await logoutUser();
    location.href = 'admin-login.html';
};
(async () => {
    const r = await getCurrentUser();
    if (!r.authenticated || r.authorized_role !== 'admin') {
        location.href = 'admin-login.html';
        return;
    }
    $('#adminName').textContent = r.user.name;
    loadDashboard();
    loadProducts();
})();
