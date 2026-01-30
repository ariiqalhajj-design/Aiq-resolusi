<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; // pastikan path benar

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}

$msg = '';
$success = '';

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($nama_lengkap) || empty($kelas)) {
        $msg = "Semua field harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Format email tidak valid.";
    } elseif ($password !== $confirm_password) {
        $msg = "Konfirmasi password tidak sesuai.";
    } elseif (strlen($password) < 6) {
        $msg = "Password minimal 6 karakter.";
    } else {
        // Cek apakah username atau email sudah terdaftar
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existing_user = $stmt->fetch();

        if ($existing_user) {
            $msg = "Username atau email sudah terdaftar.";
        } else {
            // Mulai transaction
            $pdo->beginTransaction();
            
            try {
                // Simpan user baru ke database
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
                
                if ($stmt->execute([$username, $email, $password])) {
                    $user_id = $pdo->lastInsertId();
                    
                    // Simpan data ke tabel tabungan
                    $tabunganStmt = $pdo->prepare("INSERT INTO tabungan (user_id, nama, kelas, saldo) VALUES (?, ?, ?, ?)");
                    
                    if ($tabunganStmt->execute([$user_id, $nama_lengkap, $kelas, 0.00])) {
                        // Commit transaction jika semua berhasil
                        $pdo->commit();
                        
                        $success = "Registrasi berhasil! Silakan login.";
                        header("refresh:2; url=login.php");
                    } else {
                        throw new Exception("Gagal menyimpan data tabungan.");
                    }
                } else {
                    throw new Exception("Gagal menyimpan data user.");
                }
            } catch (Exception $e) {
                // Rollback transaction jika ada error
                $pdo->rollBack();
                $msg = "Terjadi kesalahan. Silakan coba lagi. Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --accent-color: #1abc9c;
            --light-color: #f8f9fa;
            --dark-color: #34495e;
        }
        
        body {
            background: linear-gradient(-45deg, #2ecc71, #27ae60, #1abc9c, #16a085);
            background-size: 400% 400%;
            height: 100vh;
            display: flex;
            align-items: center;
            animation: gradientBG 15s ease infinite;
            overflow-x: hidden;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .register-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            animation: fadeIn 1s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .register-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(to bottom right, rgba(255,255,255,0.2), transparent);
            transform: rotate(-45deg);
            animation: shine 3s infinite linear;
        }
        
        @keyframes shine {
            0% { left: -100%; }
            20% { left: 100%; }
            100% { left: 100%; }
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .logo i {
            font-size: 36px;
            color: var(--primary-color);
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 20px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(46, 204, 113, 0.25);
            transform: translateY(-2px);
        }
        
        .input-group-text {
            background: transparent;
            border-radius: 8px 0 0 8px;
        }
        
        .password-toggle {
            cursor: pointer;
            background: transparent;
            border-left: 0;
            border-radius: 0 8px 8px 0;
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
        }
        
        .btn-login {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }
        
        .floating-icon {
            position: absolute;
            font-size: 24px;
            color: rgba(255, 255, 255, 0.7);
            animation: float 5s infinite ease-in-out;
        }
        
        .floating-icon:nth-child(1) {
            top: 10%;
            left: 15%;
            animation-delay: 0s;
        }
        
        .floating-icon:nth-child(2) {
            top: 20%;
            right: 15%;
            animation-delay: 1s;
        }
        
        .floating-icon:nth-child(3) {
            bottom: 20%;
            left: 10%;
            animation-delay: 2s;
        }
        
        .floating-icon:nth-child(4) {
            bottom: 15%;
            right: 20%;
            animation-delay: 3s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            animation: shake 0.5s linear;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #ddd;
        }
        
        .divider span {
            padding: 0 15px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .password-rules {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Floating Icons for Background -->
    <i class="floating-icon fas fa-leaf"></i>
    <i class="floating-icon fas fa-tree"></i>
    <i class="floating-icon fas fa-seedling"></i>
    <i class="floating-icon fas fa-recycle"></i>
    
    <div class="container">
        <div class="register-container">
            <div class="register-card card shadow-lg">
                <div class="card-header text-white py-4">
                    <div class="logo">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4 class="mb-0">Buat Akun Baru</h4>
                    <p class="mb-0 mt-2">Daftar untuk mengakses sistem</p>
                </div>
                <div class="card-body p-5">
                    <?php if($msg): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($msg) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" id="registerForm">
                        <div class="mb-4">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" name="username" id="username" required 
                                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                                       placeholder="Masukkan username">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" id="email" required 
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                       placeholder="Masukkan alamat email">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="nama_lengkap" class="form-label fw-semibold">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" name="nama_lengkap" id="nama_lengkap" required 
                                       value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : '' ?>"
                                       placeholder="Masukkan nama lengkap">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="kelas" class="form-label fw-semibold">Kelas</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                                <select class="form-control" name="kelas" id="kelas" required>
                                    <option value="">Pilih Kelas</option>
                                    <option value="10" <?= (isset($_POST['kelas']) && $_POST['kelas'] == '10') ? 'selected' : '' ?>>Kelas 10</option>
                                    <option value="11" <?= (isset($_POST['kelas']) && $_POST['kelas'] == '11') ? 'selected' : '' ?>>Kelas 11</option>
                                    <option value="12" <?= (isset($_POST['kelas']) && $_POST['kelas'] == '12') ? 'selected' : '' ?>>Kelas 12</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" name="password" id="password" required
                                       placeholder="Masukkan password">
                                <span class="input-group-text password-toggle" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                            <div class="password-rules mt-1">
                                <small>Password minimal 6 karakter</small>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required
                                       placeholder="Masukkan ulang password">
                                <span class="input-group-text password-toggle" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div id="passwordMatch" class="mt-1"></div>
                        </div>
                        
                        <div class="d-grid gap-2 mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Daftar
                            </button>
                        </div>
                        
                        <div class="divider">
                            <span>SUDAH PUNYA AKUN?</span>
                        </div>
                        
                        <div class="d-grid">
                            <a href="login.php" class="btn btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk ke Akun
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-white mb-0">&copy; 2023 Sistem Admin. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menampilkan/menyembunyikan password
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Menampilkan/menyembunyikan konfirmasi password
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                confirmPasswordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Validasi kekuatan password
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            // Reset strength bar
            strengthBar.style.width = '0%';
            strengthBar.className = 'password-strength';
            
            if (password.length > 0) {
                // Hitung kekuatan password (sederhana)
                let strength = 0;
                
                if (password.length >= 6) strength += 25;
                if (password.length >= 8) strength += 25;
                if (/[A-Z]/.test(password)) strength += 25;
                if (/[0-9]/.test(password)) strength += 25;
                
                // Update strength bar
                strengthBar.style.width = strength + '%';
                
                // Warna berdasarkan kekuatan
                if (strength < 50) {
                    strengthBar.classList.add('bg-danger');
                } else if (strength < 75) {
                    strengthBar.classList.add('bg-warning');
                } else {
                    strengthBar.classList.add('bg-success');
                }
            }
        });
        
        // Validasi kecocokan password
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    matchText.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> Password cocok</small>';
                } else {
                    matchText.innerHTML = '<small class="text-danger"><i class="fas fa-times-circle"></i> Password tidak cocok</small>';
                }
            } else {
                matchText.innerHTML = '';
            }
        });
        
        // Validasi form sebelum submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak sesuai!');
            }
        });
    </script>
</body>
</html>