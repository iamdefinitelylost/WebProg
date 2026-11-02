const form = document.getElementById('adminLoginForm');
const messageEl = document.getElementById('adminAuthMessage');
function showMessage(text, kind = '') {
    messageEl.textContent = text;
    messageEl.className = `auth-message ${kind}`;
}
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    showMessage('Authenticating administrator…');
    try {
        const result = await loginUser(document.getElementById('adminEmail').value.trim(), document.getElementById('adminPassword').value, 'admin');
        if (!result || result.success !== true || result.authenticated !== true) {
            throw new Error(result?.error || 'Administrator authentication failed.');
        }
        if (result.authorized_role !== 'admin' || !result.user || result.user.role !== 'admin') {
            throw new Error('The signed-in account is not an administrator.');
        }
        location.replace('admin.php');
    }
    catch (err) {
        showMessage(err.message || 'Unable to authenticate administrator.', 'error');
    }
    finally {
        button.disabled = false;
    }
});
