<?php
require_once __DIR__ . '/config/init.php';
if (Session::isLoggedIn()) {
  redirect('user/dashboard.php');
}

require_once INCLUDES_PATH . 'User.php';
$error = '';

if (isPost()) {
  if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
    $error = 'Token keamanan tidak valid.';
  } else {
    if ($_POST['password'] !== $_POST['password_confirm']) {
      $error = 'Konfirmasi password tidak cocok';
    } elseif (strlen($_POST['password']) < 8) {
      $error = 'Password minimal 8 karakter';
    } else {
      $result = (new User())->register([
        'nik' => sanitize($_POST['nik'] ?? ''),
        'nama_lengkap' => sanitize($_POST['nama_lengkap'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'password' => $_POST['password'],
        'no_telepon' => sanitize($_POST['no_telepon'] ?? ''),
        'alamat' => sanitize($_POST['alamat'] ?? ''),
        'rt' => sanitize($_POST['rt'] ?? ''),
        'rw' => sanitize($_POST['rw'] ?? ''),
        'kelurahan' => sanitize($_POST['kelurahan'] ?? ''),
        'kecamatan' => sanitize($_POST['kecamatan'] ?? ''),
        'kategori' => sanitize($_POST['kategori'] ?? 'rumah_tangga')
      ]);
      if ($result['success']) {
        Session::flash('success', 'Registrasi berhasil! Silakan login.');
        redirect('login.php');
      }
      $error = $result['message'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar - SIPARES</title>
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

    .register-card {
      width: 100%;
      max-width: 550px;
      background: var(--white);
      border-radius: var(--radius-xl);
      padding: 2.5rem;
      box-shadow: var(--shadow-lg);
    }

    .register-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .register-logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 1rem;
    }

    .register-header h1 {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }

    .register-header p {
      color: var(--text-secondary);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    @media (max-width: 500px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }

    .form-section {
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border-color);
    }

    .form-section-title {
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--text-primary);
    }

    .register-footer {
      text-align: center;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--border-color);
    }

    .back-link {
      display: block;
      text-align: center;
      margin-top: 1rem;
      color: var(--text-secondary);
    }
  </style>
</head>

<body>
  <div class="register-card">
    <div class="register-header">
      <div class="register-logo">
        <i class="fas fa-recycle"></i> SIPARES
      </div>
      <h1>Buat Akun Baru</h1>
      <p>Daftar untuk mengakses layanan SIPARES</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo e($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <?php echo CSRF::input(); ?>

      <!-- Data Diri -->
      <div class="form-row">
        <div class="form-group">
          <label>NIK <span class="required">*</span></label>
          <input type="text" name="nik" maxlength="16" pattern="[0-9]{16}" placeholder="16 digit NIK"
            value="<?php echo e($_POST['nik'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label>No. Telepon</label>
          <input type="tel" name="no_telepon" placeholder="08xxxxxxxxxx"
            value="<?php echo e($_POST['no_telepon'] ?? ''); ?>">
        </div>
      </div>

      <div class="form-group">
        <label>Nama Lengkap <span class="required">*</span></label>
        <input type="text" name="nama_lengkap" placeholder="Sesuai KTP"
          value="<?php echo e($_POST['nama_lengkap'] ?? ''); ?>" required>
      </div>

      <div class="form-group">
        <label>Kategori Pelanggan <span class="required">*</span></label>
        <select name="kategori" required>
          <option value="rumah_tangga" <?php echo ($_POST['kategori'] ?? '') === 'rumah_tangga' ? 'selected' : ''; ?>>
            Rumah Tangga - Rp 25.000/bulan</option>
          <option value="usaha" <?php echo ($_POST['kategori'] ?? '') === 'usaha' ? 'selected' : ''; ?>>Usaha/Toko - Rp
            50.000/bulan</option>
          <option value="industri" <?php echo ($_POST['kategori'] ?? '') === 'industri' ? 'selected' : ''; ?>>Industri -
            Rp 150.000/bulan</option>
        </select>
      </div>

      <!-- Alamat -->
      <div class="form-section">
        <h3 class="form-section-title"><i class="fas fa-map-marker-alt"></i> Alamat</h3>

        <div class="form-group">
          <label>Alamat Lengkap <span class="required">*</span></label>
          <textarea name="alamat" rows="2" placeholder="Jalan, nomor rumah"
            required><?php echo e($_POST['alamat'] ?? ''); ?></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>RT/RW</label>
            <div style="display:flex;gap:0.5rem;">
              <input type="text" name="rt" maxlength="3" placeholder="RT" value="<?php echo e($_POST['rt'] ?? ''); ?>">
              <input type="text" name="rw" maxlength="3" placeholder="RW" value="<?php echo e($_POST['rw'] ?? ''); ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Kelurahan <span class="required">*</span></label>
            <input type="text" name="kelurahan" placeholder="Nama kelurahan"
              value="<?php echo e($_POST['kelurahan'] ?? ''); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label>Kecamatan <span class="required">*</span></label>
          <input type="text" name="kecamatan" placeholder="Nama kecamatan"
            value="<?php echo e($_POST['kecamatan'] ?? ''); ?>" required>
        </div>
      </div>

      <!-- Akun -->
      <div class="form-section">
        <h3 class="form-section-title"><i class="fas fa-lock"></i> Informasi Akun</h3>

        <div class="form-group">
          <label>Email <span class="required">*</span></label>
          <input type="email" name="email" placeholder="email@contoh.com"
            value="<?php echo e($_POST['email'] ?? ''); ?>" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Password <span class="required">*</span></label>
            <input type="password" name="password" placeholder="Min. 8 karakter" minlength="8" required>
          </div>
          <div class="form-group">
            <label>Konfirmasi Password <span class="required">*</span></label>
            <input type="password" name="password_confirm" placeholder="Ulangi password" required>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg mt-3">
        <i class="fas fa-user-plus"></i> Daftar Sekarang
      </button>
    </form>

    <div class="register-footer">
      <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
    </div>

    <a href="index.html" class="back-link">
      <i class="fas fa-arrow-left"></i> Kembali ke Beranda
    </a>
  </div>
</body>

</html>
