const AUTH_ENDPOINT = 'api/auth.php';
async function authRequest(action, payload = {}) {
    const url = `${AUTH_ENDPOINT}?action=${encodeURIComponent(action)}&_=${Date.now()}`;
    const options = {
        method: action === 'me' ? 'GET' : 'POST',
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {
            Accept: 'application/json'
        }
    };
    if (action !== 'me') {
        options.headers['Content-Type'] = 'application/json; charset=UTF-8';
        options.body = JSON.stringify(payload);
    }
    if (!['login', 'signup'].includes(action)) {
        options.headers['X-CSRF-Token'] = payload._csrf || await getCsrfToken();
    }
    let res;
    try {
        res = await fetch(url, options);
    }
    catch (networkError) {
        throw new Error('Cannot connect to the authentication server. Make sure Apache is running and open the site through http://localhost/.');
    }
    const raw = await res.text();
    let data;
    try {
        data = raw.trim() ? JSON.parse(raw) : {};
    }
    catch (_) {
        throw new Error(`Authentication API returned invalid JSON (HTTP ${res.status}).`);
    }
    if (!res.ok) {
        throw new Error(data.error || `Authentication request failed (HTTP ${res.status}).`);
    }
    return data;
}
async function getCurrentUser() {
    return authRequest('me');
}
async function loginUser(email, password, role = 'customer') {
    return authRequest('login', {
        email, password, role
    });
}
async function signupUser(name, email, password) {
    return authRequest('signup', {
        name, email, password
    });
}
async function logoutUser() {
    const token = await getCsrfToken();
    return authRequest('logout', {
        _csrf: token
    });
}
let f1CsrfToken = null;
async function getCsrfToken() {
    if (f1CsrfToken)
        return f1CsrfToken;
    const r = await fetch(`${AUTH_ENDPOINT}?action=csrf&_=${Date.now()}`, {
        credentials: 'same-origin', cache: 'no-store', headers: {
            Accept: 'application/json'
        }
    });
    const d = await r.json();
    if (!r.ok || !d.csrf_token)
        throw new Error(d.error || 'Could not initialize security token.');
    f1CsrfToken = d.csrf_token;
    return f1CsrfToken;
}
