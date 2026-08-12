<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — UniKLAB RCMP</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <style>
    :root {
      --navy: #202734;
      --navy-mid: #161b24;
      --navy-light: #e7ecec;
      --gold: #50a7b2;
      --gold-bright: #7cc3cc;
      --gold-light: #e3f1f3;
      --white: #ffffff;
      --off-white: #eef2ef;
      --border: #d4dcdb;
      --text: #202734;
      --text-mid: #4b5862;
      --text-light: #7e8c90;
      --danger: #d4342a;
      --danger-bg: #fdeceb;
      --success: #2f8a52;
      --success-bg: #e8f5ec;
      --radius-sm: 4px;
      --radius: 8px;
      --radius-lg: 14px;
      --shadow: 0 8px 24px rgba(8, 34, 57, .14);
      --shadow-lg: 0 20px 52px rgba(32, 39, 52, .28);
      --font-serif: 'Libre Baskerville', Georgia, serif;
      --font-sans: 'DM Sans', system-ui, sans-serif;
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      font-size: 16px;
    }

    body {
      font-family: var(--font-sans);
      background: linear-gradient(130deg, #14181f 0%, #1c232d 48%, #202734 100%);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
      overflow: hidden;
    }

    /* Background decoration */
    body::before {
      content: '';
      position: absolute;
      top: -100px;
      right: -100px;
      width: 500px;
      height: 500px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .06);
      pointer-events: none;
    }

    body::after {
      content: '';
      position: absolute;
      bottom: -120px;
      left: -80px;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .04);
      pointer-events: none;
    }

    .login-wrap {
      width: 100%;
      max-width: 420px;
      position: relative;
      z-index: 1;
    }

    /* Brand header */
    .login-brand {
      text-align: center;
      margin-bottom: 32px;
    }

    .login-brand-icon {
      width: 56px;
      height: 56px;
      background: linear-gradient(135deg, #2c3744 0%, #202734 100%);
      border-radius: var(--radius);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-serif);
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--white);
      margin: 0 auto 14px;
      position: relative;
      overflow: hidden;
    }

    .login-brand-icon::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 40%;
      background: rgba(255, 255, 255, .12);
    }

    .login-brand h1 {
      font-family: var(--font-serif);
      font-size: 1.5rem;
      color: var(--white);
      font-weight: 700;
      margin-bottom: 4px;
    }

    .login-brand p {
      font-size: .82rem;
      color: rgba(255, 255, 255, .45);
      letter-spacing: .02em;
    }

    /* Card */
    .login-card {
      background: var(--white);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      overflow: hidden;
    }

    .login-card-header {
      background: var(--off-white);
      border-bottom: 1px solid var(--border);
      padding: 18px 28px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .login-card-header .lock-icon {
      width: 30px;
      height: 30px;
      background: var(--navy-light);
      border-radius: var(--radius-sm);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }

    .login-card-header h2 {
      font-family: var(--font-serif);
      font-size: .95rem;
      color: var(--navy);
      font-weight: 700;
    }

    .login-card-header p {
      font-size: .73rem;
      color: var(--text-light);
      margin-top: 1px;
    }

    .login-card-body {
      padding: 28px;
    }

    /* Form elements */
    .form-group {
      margin-bottom: 18px;
    }

    .form-label {
      display: block;
      font-size: .78rem;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 7px;
      letter-spacing: .02em;
    }

    .input-wrap {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      font-size: .9rem;
      opacity: .45;
      pointer-events: none;
    }

    .form-control {
      width: 100%;
      padding: 10px 13px 10px 36px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      font-family: var(--font-sans);
      font-size: .9rem;
      color: var(--text);
      background: var(--white);
      transition: border .15s, box-shadow .15s;
      outline: none;
    }

    .form-control:focus {
      border-color: var(--navy);
      box-shadow: 0 0 0 3px rgba(80, 167, 178, .15);
    }

    /* Show/hide password */
    .pw-toggle {
      position: absolute;
      right: 11px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      font-size: .85rem;
      color: var(--text-light);
      padding: 4px;
    }

    .pw-toggle:hover {
      color: var(--navy);
    }

    /* Error message */
    .login-error {
      display: none;
      background: var(--danger-bg);
      border: 1px solid #f0a89f;
      border-left: 3px solid var(--danger);
      color: var(--danger);
      padding: 11px 14px;
      border-radius: var(--radius-sm);
      font-size: .83rem;
      margin-bottom: 18px;
      align-items: center;
      gap: 8px;
    }

    .login-error.show {
      display: flex;
    }

    /* Submit */
    .btn-login {
      width: 100%;
      padding: 11px 20px;
      background: linear-gradient(135deg, #2c3744 0%, #202734 100%);
      color: var(--white);
      border: none;
      border-radius: var(--radius-sm);
      font-family: var(--font-sans);
      font-size: .95rem;
      font-weight: 700;
      cursor: pointer;
      transition: background .18s, transform .15s, box-shadow .15s;
      letter-spacing: .02em;
    }

    .btn-login:hover {
      background: linear-gradient(135deg, #34414f 0%, #1f2832 100%);
      transform: translateY(-1px);
      box-shadow: 0 10px 22px rgba(32, 39, 52, .26);
    }

    .btn-login:active {
      transform: none;
    }

    .sso-divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 18px 0;
      color: var(--text-light);
      font-size: .72rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .06em;
    }

    .sso-divider::before,
    .sso-divider::after {
      content: '';
      height: 1px;
      background: var(--border);
      flex: 1;
    }

    .btn-sso {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 9px;
      padding: 10px 16px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      background: var(--off-white);
      color: var(--text-mid);
      font-family: var(--font-sans);
      font-size: .88rem;
      font-weight: 700;
      cursor: not-allowed;
      opacity: .72;
    }

    .sso-mark {
      width: 16px;
      height: 16px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-template-rows: 1fr 1fr;
      gap: 2px;
      flex-shrink: 0;
    }

    .sso-mark span:nth-child(1) {
      background: #f25022;
    }

    .sso-mark span:nth-child(2) {
      background: #7fba00;
    }

    .sso-mark span:nth-child(3) {
      background: #00a4ef;
    }

    .sso-mark span:nth-child(4) {
      background: #ffb900;
    }

    /* Card footer */
    .login-card-footer {
      padding: 14px 28px;
      border-top: 1px solid var(--border);
      background: var(--off-white);
      text-align: center;
    }

    .login-card-footer a {
      font-size: .8rem;
      color: var(--text-mid);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: color .15s;
    }

    .login-card-footer a:hover {
      color: var(--navy);
    }

    /* Bottom note */
    .login-note {
      text-align: center;
      margin-top: 20px;
      font-size: .75rem;
      color: rgba(255, 255, 255, .3);
      line-height: 1.6;
    }

    /* Access restricted notice */
    .restricted-notice {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      background: var(--navy-light);
      border: 1px solid var(--border);
      border-left: 3px solid var(--navy);
      border-radius: var(--radius-sm);
      padding: 11px 14px;
      margin-bottom: 20px;
      font-size: .8rem;
      color: var(--navy-mid);
      line-height: 1.55;
    }
  </style>
</head>

<body>

  <div class="login-wrap">

    <div class="login-brand">
      <div class="login-brand-icon">LB</div>
      <h1>UniKLAB RCMP Admin</h1>
      <p>UniKLAB RCMP Laboratory Booking System</p>
    </div>

    <div class="login-card">
      <div class="login-card-header">
        <div class="lock-icon">🔒</div>
        <div>
          <h2>Administrator Access</h2>
          <p>Restricted — authorised staff only</p>
        </div>
      </div>
      <div class="login-card-body">

        <div class="restricted-notice">
          <span style="font-size:1rem;flex-shrink:0;">⚠️</span>
          <span>This portal is for <strong>admins and lab staff only</strong>. Use your Staff ID to sign in.</span>
        </div>

        <div class="login-error" id="loginError">
          <span>❌</span> <span id="errorMsg">Invalid Staff ID or password. Please try again.</span>
        </div>

        <form id="loginForm" method="POST" action="index.php" onsubmit="return handleLogin(event)">
          <div class="form-group">
            <label class="form-label">Staff ID</label>
            <div class="input-wrap">
              <span class="input-icon">👤</span>
              <input type="text" name="staff_id" class="form-control" id="adminUser" placeholder="Staff ID" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="username" oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)" required />
            </div>
          </div>
          <div class="form-group" style="margin-bottom:22px;">
            <label class="form-label">Password</label>
            <div class="input-wrap">
              <span class="input-icon">🔑</span>
              <input type="password" name="password" class="form-control" id="adminPass" placeholder="••••••••" autocomplete="current-password" required />
              <button type="button" class="pw-toggle" onclick="togglePw()" title="Show/hide password">👁</button>
            </div>
          </div>
          <button type="submit" class="btn-login" id="loginBtn">Sign In to Admin Panel</button>
        </form>
        <div class="sso-divider">or</div>
        <button type="button" class="btn-sso" disabled aria-disabled="true" title="Microsoft SSO is not enabled yet">
          <span class="sso-mark" aria-hidden="true"><span></span><span></span><span></span><span></span></span>
          Login with Microsoft SSO - Coming Soon
        </button>
      </div>
      <div class="login-card-footer">
        <a href="../index.php">← Back to Public Booking Site</a>
      </div>
    </div>

    <div class="login-note">
      Access is logged. Unauthorised attempts are monitored.<br>
      © <?= date('Y') ?> Universiti Kuala Lumpur RCMP
    </div>

  </div>

  <script>
    function togglePw() {
      const f = document.getElementById('adminPass');
      f.type = f.type === 'password' ? 'text' : 'password';
    }

    function handleLogin(e) {
      e.preventDefault();
      const u = document.getElementById('adminUser').value.trim();
      const p = document.getElementById('adminPass').value;
      const err = document.getElementById('loginError');
      const errMsg = document.getElementById('errorMsg');
      const btn = document.getElementById('loginBtn');

      if (!/^\d{6}$/.test(u)) {
        errMsg.textContent = 'Staff ID must be exactly 6 digits.';
        err.classList.add('show');
        document.getElementById('adminUser').focus();
        return false;
      }

      // Demo check - replace with real PHP session auth
      if (u === '620798' && p === 'Rcmp@1234') {
        btn.textContent = 'Signing in…';
        btn.disabled = true;
        err.classList.remove('show');
        setTimeout(() => {
          window.location.href = 'index.php?page=dashboard';
        }, 800);
      } else {
        errMsg.textContent = 'Invalid Staff ID or password. Please try again.';
        err.classList.add('show');
        document.getElementById('adminPass').value = '';
        document.getElementById('adminPass').focus();
      }
      return false;
    }

    // Enter key
    document.addEventListener('keydown', e => {
      if (e.key === 'Enter') document.getElementById('loginForm').requestSubmit();
    });
  </script>

</body>

</html>