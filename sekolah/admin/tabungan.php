<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$msg = '';
$msg_type = ''; // success, error

// Ambil semua user untuk dropdown
try {
    $users = $pdo->query("SELECT id, username, nama FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $msg = "Error mengambil data user: " . $e->getMessage();
    $msg_type = 'error';
}

// Tambah Tabungan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $user_id = $_POST['user_id'] ?? null;
    $saldo = $_POST['saldo'] ?? 0;

    if ($user_id) {
        $stmt = $pdo->prepare("INSERT INTO tabungan (user_id, saldo, created_at) VALUES (?, ?, NOW())");
        try {
            $stmt->execute([$user_id, $saldo]);
            $msg = "Tabungan baru berhasil ditambahkan.";
            $msg_type = 'success';
        } catch (Exception $e) {
            $msg = "Error: " . $e->getMessage();
            $msg_type = 'error';
        }
    } else {
        $msg = "Pilih user terlebih dahulu.";
        $msg_type = 'error';
    }
}

// Hapus Tabungan
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tabungan WHERE id = ?");
    try {
        $stmt->execute([$id]);
        $msg = "Data tabungan berhasil dihapus.";
        $msg_type = 'success';
    } catch (Exception $e) {
        $msg = "Error: " . $e->getMessage();
        $msg_type = 'error';
    }
}

// Edit Tabungan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = (int) $_POST['id'];
    $user_id = $_POST['user_id'] ?? null;
    $saldo = $_POST['saldo'] ?? 0;

    if ($user_id) {
        $stmt = $pdo->prepare("UPDATE tabungan SET user_id=?, saldo=?, updated_at=NOW() WHERE id=?");
        try {
            $stmt->execute([$user_id, $saldo, $id]);
            $msg = "Data tabungan berhasil diperbarui.";
            $msg_type = 'success';
        } catch (Exception $e) {
            $msg = "Error: " . $e->getMessage();
            $msg_type = 'error';
        }
    } else {
        $msg = "Pilih user terlebih dahulu.";
        $msg_type = 'error';
    }
}

// Ambil Data Tabungan
try {
    $data = $pdo->query("
        SELECT t.id, t.saldo, u.username, u.nama, u.kelas
        FROM tabungan t
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $msg = "Error mengambil data tabungan: " . $e->getMessage();
    $msg_type = 'error';
    $data = [];
}

// Jika ingin edit
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM tabungan WHERE id = ?");
    try {
        $stmt->execute([$id]);
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $msg = "Error mengambil data edit: " . $e->getMessage();
        $msg_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tabungan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --accent-color: #1e8449;
            --light-color: #f8f9fa;
            --dark-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #e74c3c;
            --leaf-light: #d5f4e6;
            --leaf-dark: #1a6351;
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e9 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
        }
        
        .nature-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(46, 204, 113, 0.15);
            border: 1px solid rgba(46, 204, 113, 0.18);
            overflow: hidden;
            margin-bottom: 25px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        
        .nature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .nature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(46, 204, 113, 0.25);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 20px;
            border-bottom: none;
        }
        
        .card-title {
            font-weight: 600;
            margin: 0;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        }
        
        .btn-outline-danger {
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
        }
        
        .table-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .table th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 500;
            padding: 12px 15px;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .table-hover tbody tr {
            transition: all 0.3s ease;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(46, 204, 113, 0.05);
            transform: scale(1.01);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #e1e5eb;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(46, 204, 113, 0.15);
            transform: translateY(-2px);
        }
        
        h1 {
            color: var(--dark-color);
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .saldo-badge {
            background: linear-gradient(135deg, var(--success-color), var(--primary-color));
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        /* Header dengan efek daun */
        .header-leaf {
            position: relative;
            padding: 15px 0;
        }
        
        .header-leaf::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
        }
        
        /* Animasi khusus */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease forwards;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        
        .staggered-animation > * {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.6s ease forwards;
        }
        
        .staggered-animation > *:nth-child(1) { animation-delay: 0.1s; }
        .staggered-animation > *:nth-child(2) { animation-delay: 0.2s; }
        .staggered-animation > *:nth-child(3) { animation-delay: 0.3s; }
        .staggered-animation > *:nth-child(4) { animation-delay: 0.4s; }
        .staggered-animation > *:nth-child(5) { animation-delay: 0.5s; }
        .staggered-animation > *:nth-child(n+6) { animation-delay: 0.6s; }
        
        /* Efek daun berjatuhan */
        .leaf {
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: var(--primary-color);
            opacity: 0.3;
            border-radius: 0 100% 0 100%;
            z-index: -1;
        }
        
        /* Responsif */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>
    <!-- Efek daun berjatuhan -->
    <div class="leaf" style="top: 5%; left: 5%; transform: rotate(45deg);"></div>
    <div class="leaf" style="top: 15%; right: 10%; transform: rotate(135deg);"></div>
    <div class="leaf" style="bottom: 20%; left: 15%; transform: rotate(225deg);"></div>
    <div class="leaf" style="bottom: 10%; right: 5%; transform: rotate(315deg);"></div>
    
    <div class="container animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-4 py-3 header-leaf">
            <div>
                <h1 class="h3 mb-1"><i class="fas fa-piggy-bank me-2"></i>Manajemen Tabungan</h1>
                <p class="text-muted mb-0">Kelola data tabungan siswa dengan mudah</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-<?= $msg_type == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show animate__animated animate__bounceIn" role="alert">
                <i class="fas fa-<?= $msg_type == 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row staggered-animation">
            <div class="col-lg-5 mb-4">
                <div class="nature-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-<?= $editData ? 'edit' : 'plus-circle' ?> me-2"></i>
                            <?= $editData ? 'Edit Tabungan' : 'Tambah Tabungan Baru' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="user_id" class="form-label fw-semibold">Pilih User</label>
                                <select class="form-select" name="user_id" id="user_id" required>
                                    <option value="">-- Pilih User --</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id']; ?>"
                                            <?= isset($editData['user_id']) && $editData['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['username'] . ' - ' . ($user['nama'] ?? 'N/A')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="saldo" class="form-label fw-semibold">Saldo (Rp)</label>
                                <input type="number" class="form-control" name="saldo" id="saldo" 
                                       value="<?= $editData['saldo'] ?? 0; ?>" min="0" step="1000" required>
                            </div>

                            <?php if ($editData): ?>
                                <input type="hidden" name="id" value="<?= $editData['id']; ?>">
                                <button type="submit" name="edit" class="btn btn-primary w-100 py-2">
                                    <i class="fas fa-save me-2"></i>Update Tabungan
                                </button>
                                <a href="tabungan.php" class="btn btn-outline-secondary w-100 mt-2 py-2">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                            <?php else: ?>
                                <button type="submit" name="add" class="btn btn-primary w-100 py-2 animate-pulse">
                                    <i class="fas fa-plus me-2"></i>Tambah Tabungan
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="nature-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>
                            Daftar Tabungan
                        </h5>
                        <span class="badge bg-light text-dark"><?= count($data) ?> Data</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($data) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Nama</th>
                                            <th>Kelas</th>
                                            <th>Saldo</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data as $row): ?>
                                        <tr class="animate__animated animate__fadeIn">
                                            <td><span class="fw-semibold">#<?= $row['id']; ?></span></td>
                                            <td><?= htmlspecialchars($row['username']); ?></td>
                                            <td><?= htmlspecialchars($row['nama'] ?? '-'); ?></td>
                                            <td><?= htmlspecialchars($row['kelas'] ?? '-'); ?></td>
                                            <td><span class="saldo-badge">Rp <?= number_format($row['saldo'], 0, ',', '.'); ?></span></td>
                                            <td>
                                                <div class="d-flex">
                                                    <a href="?edit=<?= $row['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Hapus data tabungan ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-piggy-bank"></i>
                                <h5 class="mt-2">Belum ada data tabungan</h5>
                                <p class="text-muted">Mulai dengan menambahkan data tabungan baru</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alert setelah 5 detik
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
        
        // Animasi saat elemen muncul di viewport
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.staggered-animation > *');
            
            animatedElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Efek hover pada kartu
            const cards = document.querySelectorAll('.nature-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.classList.add('animate__pulse');
                });
                card.addEventListener('mouseleave', () => {
                    card.classList.remove('animate__pulse');
                });
            });
            
            // Animasi daun berjatuhan
            function createLeaf() {
                const leaf = document.createElement('div');
                leaf.classList.add('leaf');
                leaf.style.left = Math.random() * 100 + '%';
                leaf.style.top = '-30px';
                leaf.style.transform = `rotate(${Math.random() * 360}deg)`;
                leaf.style.opacity = Math.random() * 0.5 + 0.1;
                leaf.style.width = Math.random() * 15 + 10 + 'px';
                leaf.style.height = Math.random() * 15 + 10 + 'px';
                
                document.body.appendChild(leaf);
                
                const animationDuration = Math.random() * 10 + 10;
                
                leaf.animate([
                    { top: '-30px', left: leaf.style.left },
                    { top: '100vh', left: parseFloat(leaf.style.left) + (Math.random() * 20 - 10) + '%' }
                ], {
                    duration: animationDuration * 1000,
                    easing: 'linear'
                });
                
                setTimeout(() => {
                    document.body.removeChild(leaf);
                }, animationDuration * 1000);
            }
            
            // Buat daun setiap 2 detik
            setInterval(createLeaf, 2000);
            
            // Buat beberapa daun awal
            for (let i = 0; i < 5; i++) {
                setTimeout(createLeaf, i * 500);
            }
        });
    </script>
</body>
</html>