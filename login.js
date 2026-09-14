const loginForm = document.getElementById('loginForm');
const signupForm = document.getElementById('signupFormAuth');
const msg = document.getElementById('authMessage');
const title = document.getElementById('authTitle');
const subtitle = document.getElementById('authSubtitle');
let formMode = 'login';
function message(text, kind = '') {
  msg.textContent = text;
  msg.className = `auth-message ${kind}`;
}
function updateForm() {
  loginForm.hidden = formMode !== 'login';
  signupForm.hidden = formMode !== 'signup';
  loginForm.style.display = formMode === 'login' ? 'grid' : 'none';
  signupForm.style.display = formMode === 'signup' ? 'grid' : 'none';
  document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.form === formMode);
  }
  );
  title.textContent = formMode === 'login' ? 'Sign in' : 'Create account';
  subtitle.textContent = formMode === 'login'
  ? 'Access your F1 Merch customer account.'
  : 'Join the grid and create your customer account.';
}
document.querySelectorAll('.toggle-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    formMode = btn.dataset.form;
    updateForm();
    message('');
  }
  );
}
);
loginForm.addEventListener('submit', async e => {
  e.preventDefault();
  const btn = loginForm.querySelector('button[type="submit"]');
  btn.disabled = true;
  message('Signing in…');
  try {
    const result = await loginUser(
    document.getElementById('loginEmail').value.trim(),
    document.getElementById('loginPassword').value,
    'customer'
    );
    if (!result || result.success !== true || result.authenticated !== true) {
      throw new Error(result?.error || 'Customer authentication failed.');
    }
    if (result.authorized_role !== 'customer' || !result.user || result.user.role !== 'customer') {
      throw new Error('This account is not a customer account.');
    }
    location.replace('index.html');
  } catch (err) {
    message(err.message || 'Unable to sign in.', 'error');
  } finally {
    btn.disabled = false;
  }
}
);
signupForm.addEventListener('submit', async e => {
  e.preventDefault();
  const password = document.getElementById('signupPassword').value;
  const confirm = document.getElementById('signupConfirm').value;
  if (password !== confirm) {
    message('Passwords do not match.', 'error');
    return;
  }
  const btn = signupForm.querySelector('button[type="submit"]');
  btn.disabled = true;
  message('Creating your account…');
  try {
    const result = await signupUser(
    document.getElementById('signupName').value.trim(),
    document.getElementById('signupEmailAuth').value.trim(),
    password
    );
    if (!result || result.success !== true) {
      throw new Error(result?.error || 'Could not create account.');
    }
    location.replace('index.html');
  } catch (err) {
    message(err.message || 'Unable to create account.', 'error');
  } finally {
    btn.disabled = false;
  }
}
);
updateForm();
