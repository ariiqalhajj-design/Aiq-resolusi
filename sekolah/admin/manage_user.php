<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Pastikan admin yang mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    die("<h1>Akses Ditolak</h1><p>Hanya admin yang dapat mengakses halaman ini.</p>");
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$msg = '';
$msg_type = '';

// Hapus User
if (isset($_GET['delete']) && isset($_GET['csrf_token'])) {
    // Validasi CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        $msg = "Token keamanan tidak valid.";
        $msg_type = 'error';
    } else {
        $id = (int) $_GET['delete'];
        
        // Pastikan user tidak menghapus dirinya sendiri
        if ($id !== $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id=? AND role='user'");
            if ($stmt->execute([$id])) {
                $msg = "User berhasil dihapus.";
                $msg_type = 'success';
            } else {
                $msg = "Gagal menghapus user.";
                $msg_type = 'error';
            }
        } else {
            $msg = "Tidak dapat menghapus akun sendiri.";
            $msg_type = 'error';
        }
    }
}

// Ambil semua user
$users = $pdo->query("SELECT * FROM users WHERE role='user' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Jika ingin edit
$editUser = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=? AND role='user'");
    $stmt->execute([$id]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$editUser) {
        $msg = "User tidak ditemukan.";
        $msg_type = 'error';
    }
}

// Proses edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    // Validasi CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $msg = "Token keamanan tidak valid.";
        $msg_type = 'error';
    } else {
        $id = (int) $_POST['id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        
        // Validasi input
        if (empty($username) || empty($email)) {
            $msg = "Username dan email harus diisi.";
            $msg_type = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = "Format email tidak valid.";
            $msg_type = 'error';
        } else {
            // Cek apakah username/email sudah digunakan oleh user lain
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND id != ? AND role = 'user'");
            $stmt->execute([$username, $email, $id]);
            
            if ($stmt->rowCount() > 0) {
                $msg = "Username atau email sudah digunakan.";
                $msg_type = 'error';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username=?, email=? WHERE id=? AND role='user'");
                try {
                    if ($stmt->execute([$username, $email, $id])) {
                        $msg = "User berhasil diperbarui.";
                        $msg_type = 'success';
                        // Redirect untuk menghilangkan parameter GET
                        header("Location: manage_user.php?msg=" . urlencode($msg) . "&type=$msg_type");
                        exit();
                    }
                } catch (Exception $e) {
                    $msg = "Error: " . $e->getMessage();
                    $msg_type = 'error';
                }
            }
        }
    }
}

// Tampilkan pesan dari redirect
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $msg = urldecode($_GET['msg']);
    $msg_type = $_GET['type'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1b5e20;
            --secondary: #2e7d32;
            --accent: #4caf50;
            --light: #e8f5e9;
            --dark: #1b5e20;
            --success: #388e3c;
            --warning: #f57c00;
            --info: #0288d1;
            --danger: #d32f2f;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            color: #2e7d32;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            color: white;
            padding: 25px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(-15deg);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        h1 {
            font-size: 28px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }
        
        .alert {
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            animation: slideInDown 0.5s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .alert.success {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            color: #2e7d32;
            border-left: 4px solid var(--success);
        }
        
        .alert.error {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: #c62828;
            border-left: 4px solid var(--danger);
        }
        
        .alert.info {
            background: linear-gradient(135deg, 'e3f2fd' 0%, 'bbdefb' 100%);
            color: #1565c0;
            border-left: 4px solid var(--info);
        }
        
        .alert i {
            margin-right: 12px;
            font-size: 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(46, 125, 50, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeInUp 0.6s ease;
            border: 1px solid #e8f5e9;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(46, 125, 50, 0.15);
        }
        
        .card-title {
            font-size: 22px;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e8f5e9;
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .card-title i {
            margin-right: 12px;
            color: var(--accent);
        }
        
        .card-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background: linear-gradient(to right, var(--primary), var(--accent));
            transition: width 0.3s ease;
        }
        
        .card:hover .card-title::after {
            width: 100px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--primary);
            font-size: 15px;
        }
        
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e8f5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
            background: white;
            transform: translateY(-2px);
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn {
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn i {
            margin-right: 10px;
            transition: transform 0.3s ease;
        }
        
        .btn:hover i {
            transform: scale(1.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 5px 15px rgba(27, 94, 32, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(27, 94, 32, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #78909c, #546e7a);
            color: white;
            box-shadow: 0 5px 15px rgba(120, 144, 156, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(120, 144, 156, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #c62828);
            color: white;
            box-shadow: 0 5px 15px rgba(211, 47, 47, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(211, 47, 47, 0.4);
        }
        
        .btn-sm {
            padding: 10px 15px;
            font-size: 14px;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        th, td {
            padding: 18px 20px;
            text-align: left;
            border-bottom: 1px solid #e8f5e9;
        }
        
        th {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        tr {
            transition: all 0.3s ease;
        }
        
        tr:hover {
            background-color: #f1f8e9;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .no-users {
            text-align: center;
            padding: 50px 30px;
            color: #78909c;
        }
        
        .no-users i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #c8e6c9;
        }
        
        .no-users h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .no-users p {
            font-size: 16px;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeInUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .card {
                padding: 20px;
            }
            
            th, td {
                padding: 12px 15px;
            }
        }
        
        /* Floating leaves background */
        .leaves-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            opacity: 0.1;
        }
        
        .leaf {
            position: absolute;
            background: var(--accent);
            border-radius: 50% 0 50% 0;
            animation: floatLeaf 20s infinite linear;
            opacity: 0.3;
        }
        
        @keyframes floatLeaf {
            from {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            90% {
                opacity: 0.3;
            }
            to {
                transform: translateY(-100vh) translateX(100vw) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Floating leaves background -->
    <div class="leaves-bg" id="leavesBg"></div>
    
    <header>
        <div class="header-content">
            <h1><i class="fas fa-users-cog"></i> Kelola User</h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </header>
    
    <div class="container">
        <?php if ($msg): ?>
            <div class="alert <?php echo $msg_type; ?>">
                <i class="fas <?php echo $msg_type === 'success' ? 'fa-check-circle' : ($msg_type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'); ?>"></i>
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>
        
        <!-- Form Edit User -->
        <?php if ($editUser): ?>
            <div class="card">
                <h2 class="card-title"><i class="fas fa-user-edit"></i> Edit User</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($editUser['username']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="edit_user" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                        <a href="manage_user.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2 class="card-title"><i class="fas fa-list"></i> Daftar User</h2>
            
            <?php if (count($users) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($user['created_at'] ?? '2023-01-01')) ?></td>
                            <td>
                                <div class="actions">
                                    <a href="manage_user.php?edit=<?= $user['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="manage_user.php?delete=<?= $user['id'] ?>&csrf_token=<?= urlencode($_SESSION['csrf_token']) ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus user <?= htmlspecialchars($user['username']) ?>?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-users">
                    <i class="fas fa-user-slash"></i>
                    <h3>Belum ada user terdaftar</h3>
                    <p>Tidak ada user yang terdaftar dalam sistem.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Konfirmasi sebelum menghapus
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('a.btn-danger');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Yakin ingin menghapus user ini?')) {
                        e.preventDefault();
                    }
                });
            });
            
            // Auto-hide pesan setelah 5 detik
            const alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            }

            // Create floating leaves background
            function createLeaves() {
                const leavesContainer = document.getElementById('leavesBg');
                const leavesCount = 15;
                
                for (let i = 0; i < leavesCount; i++) {
                    const leaf = document.createElement('div');
                    leaf.classList.add('leaf');
                    
                    // Random properties
                    const size = Math.random() * 25 + 15;
                    const posX = Math.random() * 100;
                    const posY = Math.random() * 100;
                    const delay = Math.random() * 15;
                    const duration = Math.random() * 10 + 20;
                    
                    leaf.style.width = `${size}px`;
                    leaf.style.height = `${size}px`;
                    leaf.style.left = `${posX}vw`;
                    leaf.style.top = `${posY}vh`;
                    leaf.style.animationDelay = `${delay}s`;
                    leaf.style.animationDuration = `${duration}s`;
                    
                    leavesContainer.appendChild(leaf);
                }
            }

            createLeaves();
        });
    </script>
</body>
</html>