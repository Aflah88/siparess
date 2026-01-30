<?php
require_once __DIR__ . '/config/init.php';

if (Session::isLoggedIn()) {
  redirect('user/dashboard.php');
}
if (Session::isAdminLoggedIn()) {
  redirect('admin/dashboard.php');
}

$error = '';
if (isPost()) {
  if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
    $error = 'Token keamanan tidak valid.';
  } else {
    $loginType = $_POST['login_type'] ?? 'user';
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
      $error = 'Email dan password wajib diisi';
    } else {
      if ($loginType === 'admin') {
        require_once INCLUDES_PATH . 'Admin.php';
        $result = (new Admin())->login($email, $password);
        if ($result['success']) {
          redirect('admin/dashboard.php');
        }
      } else {
        require_once INCLUDES_PATH . 'User.php';
        $result = (new User())->login($email, $password);
        if ($result['success']) {
          redirect('user/dashboard.php');
        }
      }
      $error = $result['message'] ?? 'Login gagal';
    }
  }
}
$flash = Session::getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk - SIPARES</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, var(--primary-light) 0%, var(--white) 100%);
      padding: 2rem;
    }

    .login-card {
      width: 100%;
      max-width: 420px;
      background: var(--white);
      border-radius: var(--radius-xl);
      padding: 2.5rem;
      box-shadow: var(--shadow-lg);
    }

    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .login-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 1rem;
    }

    .login-header h1 {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }

    .login-header p {
      color: var(--text-secondary);
    }

    .login-tabs {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
    }

    .login-tab {
      flex: 1;
      padding: 0.75rem;
      text-align: center;
      border: 2px solid var(--border-color);
      border-radius: var(--radius-lg);
      background: none;
      cursor: pointer;
      font-weight: 500;
      color: var(--text-secondary);
      transition: var(--transition);
    }

    .login-tab.active {
      border-color: var(--primary);
      background: var(--primary-light);
      color: var(--primary);
    }

    .login-footer {
      text-align: center;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border-color);
    }

    .login-footer a {
      color: var(--primary);
      font-weight: 500;
    }

    .back-link {
      display: block;
      text-align: center;
      margin-top: 1rem;
      color: var(--text-secondary);
    }

    .back-link i {
      margin-right: 0.5rem;
    }
  </style>
</head>

<body>
  <div class="login-card">
    <div class="login-header">
      <div class="login-logo">
        <i class="fas fa-recycle"></i>
        SIPARES
      </div>
      <h1>Masuk ke Akun</h1>
      <p>Masukkan kredensial Anda untuk melanjutkan</p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?php echo e($flash['type']); ?>">
        <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'info-circle'; ?>"></i>
        <?php echo e($flash['message']); ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo e($error); ?>
      </div>
    <?php endif; ?>

    <div class="login-tabs">
      <button type="button" class="login-tab active" onclick="setLoginType('user')">
        <i class="fas fa-user"></i> Pengguna
      </button>
      <button type="button" class="login-tab" onclick="setLoginType('admin')">
        <i class="fas fa-shield-alt"></i> Admin
      </button>
    </div>

    <form method="POST">
      <?php echo CSRF::input(); ?>
      <input type="hidden" name="login_type" id="loginType" value="user">

      <div class="form-group">
        <label for="email">Email</label>
        <input type="text" id="email" name="email" placeholder="Masukkan email Anda"
          value="<?php echo e($_POST['email'] ?? ''); ?>" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-input">
          <input type="password" id="password" name="password" placeholder="Masukkan password" required>
          <button type="button" class="password-toggle" onclick="togglePassword()">
            <i class="fas fa-eye" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg">
        <i class="fas fa-sign-in-alt"></i> Masuk
      </button>
    </form>

    <div class="login-footer">
      <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
    </div>

    <a href="index.html" class="back-link">
      <i class="fas fa-arrow-left"></i> Kembali ke Beranda
    </a>
  </div>

  <script>
    function setLoginType(type) {
      document.getElementById('loginType').value = type;
      document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
      event.target.closest('.login-tab').classList.add('active');
    }

    function togglePassword() {
      const input = document.getElementById('password');
      const icon = document.getElementById('toggleIcon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    }
  </script>
</body>

</html>
