<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Pastikan yang buka admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit;
}

$msg = '';
$msg_type = '';

// Handle form tambah kegiatan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $jenis_kegiatan = htmlspecialchars(trim($_POST['jenis_kegiatan']));
    $nama_kegiatan = htmlspecialchars(trim($_POST['nama_kegiatan']));
    $deskripsi = htmlspecialchars(trim($_POST['deskripsi']));
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');

    // Validasi input
    if (empty($user_id) || empty($jenis_kegiatan) || empty($nama_kegiatan) || empty($tanggal)) {
        $msg = "User, jenis kegiatan, nama kegiatan, dan tanggal harus diisi.";
        $msg_type = 'error';
    } else {
        $stmt = $pdo->prepare("INSERT INTO kegiatan (user_id, jenis_kegiatan, nama_kegiatan, deskripsi, tanggal) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$user_id, $jenis_kegiatan, $nama_kegiatan, $deskripsi, $tanggal]);
            $msg = "Kegiatan berhasil ditambahkan.";
            $msg_type = 'success';
            
            // Reset form
            $_POST = array();
        } catch (Exception $e) {
            $msg = "Error: " . $e->getMessage();
            $msg_type = 'error';
        }
    }
}

// Handle hapus kegiatan
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    // Hapus dari database
    $stmt = $pdo->prepare("DELETE FROM kegiatan WHERE id=?");
    if ($stmt->execute([$id])) {
        header("Location: kegiatan.php?msg=Kegiatan berhasil dihapus.&type=success");
        exit;
    } else {
        header("Location: kegiatan.php?msg=Gagal menghapus kegiatan.&type=error");
        exit;
    }
}

// Ambil semua user untuk dropdown
$users = $pdo->query("SELECT id, username FROM users WHERE role = 'user' ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua kegiatan dengan informasi user
$kegiatan = $pdo->query("
    SELECT k.*, u.username 
    FROM kegiatan k 
    LEFT JOIN users u ON k.user_id = u.id 
    ORDER BY k.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Kelola Kegiatan - Admin Panel</title>
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
            padding: 10px 20px;
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
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
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
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e8f5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
            background: white;
            transform: translateY(-2px);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        small {
            display: block;
            margin-top: 8px;
            color: #78909c;
            font-size: 13px;
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
            animation: fadeIn 0.5s ease;
        }
        
        tr:hover {
            background-color: #f1f8e9;
            transform: translateX(5px);
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .no-kegiatan {
            text-align: center;
            padding: 50px 30px;
            color: #78909c;
            animation: fadeIn 1s ease;
        }
        
        .no-kegiatan i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #c8e6c9;
            animation: pulse 2s infinite;
        }
        
        .no-kegiatan h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .no-kegiatan p {
            font-size: 16px;
        }
        
        .kategori-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .kategori-badge:hover {
            transform: scale(1.05);
        }
        
        .kategori-diniyyah {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            color: #2e7d32;
            box-shadow: 0 3px 10px rgba(46, 125, 50, 0.2);
        }
        
        .kategori-it {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1565c0;
            box-shadow: 0 3px 10px rgba(21, 101, 192, 0.2);
        }
        
        .kategori-inggris {
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            color: #ef6c00;
            box-shadow: 0 3px 10px rgba(239, 108, 0, 0.2);
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
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
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
            0% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            90% {
                opacity: 0.3;
            }
            100% {
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
            <h1><i class="fas fa-calendar-alt"></i> Kelola Kegiatan</h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </header>
    
    <div class="container">
        <?php if ($msg): ?>
            <div class="alert <?php echo $msg_type; ?>">
                <i class="fas <?php echo $msg_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2 class="card-title"><i class="fas fa-plus-circle"></i> Tambah Kegiatan Baru</h2>
            <form method="post" id="kegiatanForm">
                <div class="form-group">
                    <label for="user_id">Pilih User</label>
                    <select name="user_id" id="user_id" required>
                        <option value="">Pilih User</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= isset($_POST['user_id']) && $_POST['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="jenis_kegiatan">Jenis Kegiatan</label>
                    <select name="jenis_kegiatan" id="jenis_kegiatan" required>
                        <option value="">Pilih Jenis Kegiatan</option>
                        <option value="diniyyah" <?= isset($_POST['jenis_kegiatan']) && $_POST['jenis_kegiatan'] === 'diniyyah' ? 'selected' : '' ?>>Diniyyah</option>
                        <option value="it" <?= isset($_POST['jenis_kegiatan']) && $_POST['jenis_kegiatan'] === 'it' ? 'selected' : '' ?>>IT</option>
                        <option value="inggris" <?= isset($_POST['jenis_kegiatan']) && $_POST['jenis_kegiatan'] === 'inggris' ? 'selected' : '' ?>>Inggris</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="nama_kegiatan">Nama Kegiatan</label>
                    <input type="text" id="nama_kegiatan" name="nama_kegiatan" value="<?= isset($_POST['nama_kegiatan']) ? htmlspecialchars($_POST['nama_kegiatan']) : '' ?>" required placeholder="Masukkan nama kegiatan">
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Kegiatan</label>
                    <textarea id="deskripsi" name="deskripsi" placeholder="Masukkan deskripsi kegiatan"><?= isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?= isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d') ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Tambah Kegiatan</button>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title"><i class="fas fa-list"></i> Daftar Kegiatan</h2>
            
            <?php if (count($kegiatan) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Jenis</th>
                            <th>Nama Kegiatan</th>
                            <th>Deskripsi</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kegiatan as $index => $row): ?>
                        <tr style="animation-delay: <?= $index * 0.1 ?>s;">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td>
                                <span class="kategori-badge kategori-<?= $row['jenis_kegiatan'] ?>">
                                    <?= ucfirst($row['jenis_kegiatan']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
                            <td><?= strlen($row['deskripsi']) > 100 ? substr(htmlspecialchars($row['deskripsi']), 0, 100) . '...' : htmlspecialchars($row['deskripsi']) ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-danger btn-sm" href="kegiatan.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus kegiatan <?= htmlspecialchars($row['nama_kegiatan']) ?>?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-kegiatan">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Belum ada kegiatan</h3>
                    <p>Tidak ada kegiatan yang terdaftar dalam sistem.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
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

        // Add animation to table rows
        document.addEventListener('DOMContentLoaded', function() {
            createLeaves();
            
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
                row.classList.add('animate__animated', 'animate__fadeInUp');
            });

            // Add hover effect to form elements
            const formElements = document.querySelectorAll('input, select, textarea');
            formElements.forEach(el => {
                el.addEventListener('focus', () => {
                    el.parentElement.classList.add('focused');
                });
                el.addEventListener('blur', () => {
                    el.parentElement.classList.remove('focused');
                });
            });
        });
    </script>
</body>
</html>