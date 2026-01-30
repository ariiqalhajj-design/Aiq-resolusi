<?php
session_start();
require_once __DIR__ . '/../includes/db.php'; // pastikan path benar

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: /sekolah/admin/dashboard.php');
    } else {
        header('Location: /sekolah/public/dashboard_user.php');
    }
    exit;
}

$msg = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username_or_email) || empty($password)) {
        $msg = "Username/Email dan Password harus diisi.";
    } else {
        // Mencari user berdasarkan username atau email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username_or_email, $username_or_email]);
        $user = $stmt->fetch();

        if ($user) {
            // Password belum di-hash di database - PERBAIKI INI DI MASA DEPAN
            if ($password === $user['password']) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect berdasarkan role
                if ($_SESSION['role'] === 'admin') {
                    header('Location: /sekolah/admin/dashboard.php');
                    exit;
                } else {
                    header('Location: /sekolah/public/dashboard_user.php');
                    exit;
                }
            } else {
                $msg = "Username/Email atau Password salah.";
            }
        } else {
            $msg = "Username/Email atau Password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --success: #2ecc71;
            --info: #3498db;
            --warning: #f39c12;
            --light: #f8f9fa;
            --dark: #212529;
            --background: #f5fcf8;
            --card-bg: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --border-radius: 16px;
            --box-shadow: 0 10px 30px rgba(46, 204, 113, 0.15);
            --transition: all 0.3s ease;
        }

        .dark-mode {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --background: #121e17;
            --card-bg: #1a2a21;
            --text-primary: #ecf0f1;
            --text-secondary: #bdc3c7;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--background) 0%, #e8f5e9 100%);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .background-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            top: -250px;
            left: -250px;
            animation: float 15s infinite ease-in-out;
        }

        .shape-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, var(--info), var(--success));
            bottom: -200px;
            right: -200px;
            animation: float 18s infinite ease-in-out reverse;
        }

        .shape-3 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, var(--warning), var(--primary));
            top: 50%;
            left: 70%;
            animation: float 12s infinite ease-in-out;
        }

        .leaf {
            position: absolute;
            font-size: 24px;
            color: rgba(46, 204, 113, 0.2);
            animation: falling 15s infinite linear;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-20px, 20px) rotate(5deg); }
            50% { transform: translate(10px, -10px) rotate(-5deg); }
            75% { transform: translate(15px, 15px) rotate(3deg); }
        }

        @keyframes falling {
            0% {
                top: -10%;
                transform: translateX(0) rotate(0deg);
                opacity: 0.7;
            }
            100% {
                top: 110%;
                transform: translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            z-index: 1;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 40px 30px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            animation: cardEntrance 0.8s ease-out;
        }

        @keyframes cardEntrance {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
            animation: pulse 2s infinite ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-primary);
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            z-index: 2;
            transition: var(--transition);
        }

        .form-input {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: var(--transition);
            background: var(--card-bg);
            color: var(--text-primary);
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
            outline: none;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--text-secondary);
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            padding: 0 15px;
            font-size: 14px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .register-link a:hover {
            gap: 8px;
            color: var(--primary-dark);
        }

        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--card-bg);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            z-index: 10;
        }

        .theme-toggle:hover {
            transform: rotate(15deg);
        }

        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--card-bg);
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(150%);
            transition: transform 0.5s ease;
            z-index: 1000;
            border-left: 4px solid var(--primary);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-container {
                padding: 15px;
            }
            
            .login-card {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .shape {
                display: none;
            }
        }

        /* Animation for error message */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error-message {
            background: rgba(243, 156, 18, 0.1);
            border-left: 4px solid var(--warning);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s ease;
        }

        .error-icon {
            color: var(--warning);
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <!-- Adding animated leaves -->
        <div class="leaf" style="top: 10%; left: 5%;"><i class="fas fa-leaf"></i></div>
        <div class="leaf" style="top: 20%; left: 15%;"><i class="fas fa-leaf"></i></div>
        <div class="leaf" style="top: 5%; left: 25%;"><i class="fas fa-leaf"></i></div>
        <div class="leaf" style="top: 15%; left: 85%;"><i class="fas fa-leaf"></i></div>
        <div class="leaf" style="top: 25%; left: 75%;"><i class="fas fa-leaf"></i></div>
        <div class="leaf" style="top: 8%; left: 65%;"><i class="fas fa-leaf"></i></div>
    </div>

    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1>Masuk ke Sistem</h1>
                <p>Silakan masuk untuk mengakses dashboard</p>
            </div>

            <?php if($msg): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle error-icon"></i>
                    <span><?= htmlspecialchars($msg) ?></span>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="username_or_email" class="form-label">Username atau Email</label>
                    <div class="input-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-input" name="username_or_email" id="username_or_email" required 
                               value="<?= isset($_POST['username_or_email']) ? htmlspecialchars($_POST['username_or_email']) : '' ?>"
                               placeholder="Masukkan username atau email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-input" name="password" id="password" required
                               placeholder="Masukkan password">
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </button>
            </form>

            <div class="divider">
                <span>ATAU</span>
            </div>

            <div class="register-link">
                <a href="register.php">
                    <i class="fas fa-user-plus"></i> Buat Akun Baru
                </a>
            </div>
        </div>
    </div>

    <div class="notification" id="welcomeNotification">
        <div class="notification-icon">
            <i class="fas fa-info"></i>
        </div>
        <span>Selamat datang! Silakan masuk ke akun Anda.</span>
    </div>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                localStorage.setItem('theme', 'dark');
            } else {
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                localStorage.setItem('theme', 'light');
            }
        });
        
        // Load saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        // Show welcome notification
        setTimeout(() => {
            document.getElementById('welcomeNotification').classList.add('show');
        }, 1000);
        
        setTimeout(() => {
            document.getElementById('welcomeNotification').classList.remove('show');
        }, 5000);
        
        // Add focus effects to form inputs
        const formInputs = document.querySelectorAll('.form-input');
        formInputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.querySelector('.input-icon').style.color = 'var(--primary)';
                input.parentElement.querySelector('.input-icon').style.transform = 'translateY(-50%) scale(1.2)';
            });
            
            input.addEventListener('blur', () => {
                input.parentElement.querySelector('.input-icon').style.color = 'var(--text-secondary)';
                input.parentElement.querySelector('.input-icon').style.transform = 'translateY(-50%) scale(1)';
            });
        });

        // Create additional falling leaves
        function createLeaves() {
            const background = document.querySelector('.background-shapes');
            for (let i = 0; i < 10; i++) {
                const leaf = document.createElement('div');
                leaf.className = 'leaf';
                leaf.innerHTML = '<i class="fas fa-leaf"></i>';
                leaf.style.left = Math.random() * 100 + '%';
                leaf.style.animationDelay = Math.random() * 15 + 's';
                leaf.style.fontSize = (Math.random() * 10 + 16) + 'px';
                background.appendChild(leaf);
            }
        }

        createLeaves();
    </script>
</body>
</html>