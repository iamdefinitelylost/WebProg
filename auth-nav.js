/* Shared storefront authentication navigation. */
(function () {
    'use strict';
    function getAccountLink() {
        return document.getElementById('accountLink') || document.querySelector('.nav-actions > a:not(.nav-cart-link)');
    }
    function clearAuthNav(link) {
        if (!link)
            return;
        link.classList.remove('authenticated');
        link.removeAttribute('data-authenticated');
        link.href = 'login.html';
        link.textContent = 'Sign in';
        link.removeAttribute('title');
        link.removeAttribute('aria-label');
    }
    function avatarUrl(path) {
        if (!path)
            return '';
        const value = String(path).trim();
        if (!value)
            return '';
        if (/^(https?:)?\//i.test(value) || value.startsWith('data:'))
            return value;
        return value.replace(/^\.\//, '');
    }
    function setAuthenticatedNav(link, user) {
        if (!link || !user)
            return;
        const name = String(user.name || '').trim();
        const avatar = avatarUrl(user.avatar_path);
        link.href = 'account.html';
        link.classList.add('authenticated');
        link.dataset.authenticated = 'true';
        link.title = name ? `Account: ${name}` : 'My account';
        link.setAttribute('aria-label', name ? `Account: ${name}` : 'My account'); // Show the customer's profile photo when available; otherwise use initials.
        if (avatar) {
            link.innerHTML = `<img class="nav-avatar" src="${avatar.replace(/&/g, '&amp;').replace(/"/g, '&quot;')}" alt="" aria-hidden="true">`;
        }
        else {
            const initials = name
                ? name.split(/\s+/).filter(Boolean).slice(0, 2).map(part => part[0]).join('').toUpperCase()
                : 'ME';
            link.textContent = initials || 'ME';
        }
    }
    async function refreshAuthNav() {
        const link = getAccountLink();
        if (!link || typeof getCurrentUser !== 'function')
            return;
        // Always start from the signed-out state so stale HTML such as "ME" cannot remain
        // visible while the current PHP session is being checked.
        clearAuthNav(link);
        try {
            const result = await getCurrentUser();
            if (result && result.authenticated === true && result.user) {
                setAuthenticatedNav(link, result.user);
            }
        }
        catch (error) {
            // Authentication failure should never make the storefront look signed in.
            clearAuthNav(link);
            console.warn('[F1 MERCH] Could not verify authentication state.', error);
        }
    }
    window.refreshAuthNav = refreshAuthNav;
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', refreshAuthNav, { once: true });
    }
    else {
        refreshAuthNav();
    }
    // If another page/component logs out and then navigates back, make the nav update
    // immediately as well.
    window.addEventListener('pageshow', refreshAuthNav);
    window.addEventListener('storage', refreshAuthNav);
})();
